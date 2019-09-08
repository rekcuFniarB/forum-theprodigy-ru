#!/usr/bin/env python2.7
from __future__ import unicode_literals
import simplejson as json
import sys, os
from youtube_dl import YoutubeDL as Yt
from youtube_dl.utils import MaxDownloadsReached
from time import sleep
import subprocess
import MySQLdb
import re
import urllib
from hashlib import md5
from HTMLParser import HTMLParser

html = HTMLParser()

txt_launch_error = '''No path given.

Example:
    %s /tmp/config.json
    \n''' % sys.argv[0]

url_regex = re.compile(r'https?://[^\s\[\]<>"\']+')

#files = []
recent_dl = None

def err(msg):
    try:
        sys.stderr.write('%s\n' % msg)
    except UnicodeEncodeError:
        sys.stderr.write('%s\n' % msg.encode('ascii', 'replace'))
    except UnicodeDecodeError:
        sys.stderr.write('%s\n' % msg.decode('utf-8'))

def filetitle(filename, striphash = False):
        title = os.path.basename(filename).split('.')
        title.pop()
        # Filename without extension
        title = '.'.join(title)
        if striphash:
            ## Filename came with a hash, remove hash
            title = ' '.join(title.split(' ')[1:])
        else:
            ## else replace hash placeholder
            title = title.replace('[__HASH__] ', '')
        return title

## put placeholders in SQL query string, e.g.:
## "SELECT post FROM messages WHERE id IN (%s)" becomes
## "SELECT post FROM messages WHERE id IN (%s, %s, %s)"
def preformatSQL(SQL, data):
    placeholders = []
    prepared_data = []
    for val in data:
        datatype = type(val)
        if datatype is not list and datatype is not int and datatype is not float and datatype is not str and datatype is not unicode:
            raise ValueError('Wrond data type supplied.')
        
        if datatype is list:
            ## '%s,%s,%s,...'
            placeholders.append(','.join(['%s'] * len(val)))
            prepared_data.extend(val)
        else:
            placeholders.append('%s')
            prepared_data.append(val)
    
    return (SQL % tuple(placeholders), tuple(prepared_data))

def yt_hook(f):
    global config, recent_dl
    recent_dl = None
    if f['status'] == 'finished':
        title = filetitle(f['filename'], striphash=True)
        ext = f['filename'].split('.')[-1].lower()
        if ext == 'webm':
            # Convert webm to ogg
            err('__DEBUG__: Converting webm to ogg...')
            ogg = f['filename'].replace('.webm', '.ogg')
            subprocess.call(['ffmpeg', '-i', f['filename'], '-c', 'copy', '-vn', '-loglevel', 'error', '-y', ogg])
            os.remove(f['filename'])
            f['filename'] = ogg
        elif ext == 'wav' or ext == 'flac':
            # convert wav or flac to ogg
            err('__DEBUG__: Converting wav/flac to ogg...')
            ogg = f['filename'].replace('.wav', '.ogg').replace('.flac', '.ogg')
            subprocess.call(['ffmpeg', '-i', f['filename'], '-c', 'libvorbis', '-vn', '-loglevel', 'error', '-y', ogg])
            os.remove(f['filename'])
            f['filename'] = ogg
        elif ext != 'ogg' and ext != 'mp3' and ext != 'opus' and ext != 'm4a' and ext != 'aac' and ext != 'ac3' and ext != 'mka':
            err('__DEBUG__: Removing not supported format `%s`' % ext)
            os.remove(f['filename'])
            recent_dl = None
            return False
        size = os.path.getsize(f['filename'])
        item = (f['filename'], title, size)
        if 'max_size' in config and type(config['max_size']) == int:
            # Remove file larger than max_size.
            if size <= config['max_size']:
                recent_dl = item
            else:
                err('Removing %s due to size limit...' % f['filename'])
                os.remove(f['filename'])
                recent_dl = None
                return False
        else:
            # Just save if no 'max_size' config.
            recent_dl = item

ytopt = {
    ## download formats priority
    'format': 'm4a/aac/140/mp3/vorbis/171/wav/bestaudio/best',
    'extractaudio': True,
    ## output file template
    'outtmpl': '[__HASH__] %(title)s - %(id)s.%(ext)s',
    ## limit max file downloads per link
    'max_downloads': 1,
    ## using aria2c for downloads to speed up download process (using multithreaded download)
    'external_downloader': 'aria2c',
    ## don't download playlists
    'noplaylist': True,
    ## don't change file creation time
    'updatetime': False,
    #'nooverwrites': True,
    'external_downloader_args': [
        #'--console-log-level',
        #'error',
        '-q',
        '-x',
        '5',
        '-k',
        '7M',
        '-U',
        'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:59.0) Gecko/20100101 Firefox/59.0'],
    ## run function on file downloaded
    'progress_hooks': [yt_hook],
    ## ignore errors
    'ignoreerrors': True
    }

def main():
    db = MySQLdb.connect(
        host = config['mysqlhost'], port = config['mysqlport'],
        db = config['mysqldb'], charset = config['mysqlcharset'],
        user = config['mysqluser'], passwd = config['mysqlpasswd'])
    cursor = db.cursor()
    
    ## Expected format: [('body', member_id, "member name"), ...]
    result = []
    
    for datasource in config['sources']:
        ## get raw SQL query string
        SQL = SQLs[datasource['SQL']]
        ## format SQL string
        prepared = preformatSQL(SQL, datasource['data'])
        cursor.execute(prepared[0], prepared[1])
        result.extend(cursor.fetchall())
    
    db.close()
    
    if len(result) == 0:
        err('No tracks found.')
        quit(0)
    
    err('__DEBUG__: found %s posts.' % len(result))
    
    urls = [] # [('URL', 'MemberName'), ...]
    _urls = [] # ['URL', 'URL', ...]
    
    for post in result:
        upost = html.unescape(post[0])
        matched_urls = url_regex.findall(upost)
        for matched_url in matched_urls:
            if not matched_url in _urls:
                if 'bandcamp.com' in matched_url:
                    if not '/track/' in matched_url:
                        ## skip everything from bandcamp except single track
                        continue
                if 'playlist' in matched_url or 'album' in matched_url:
                    ## skip playlists and albums
                    continue
                urls.append((matched_url, post[2]))
                _urls.append(matched_url)
    
    
    if len(urls) == 0:
        err('[INFO] No url found.')
        quit(0)
    else:
        err('[INFO] found %s urls.' % len(urls))
    
    files = []
    
    ## list of downloaded files titles
    ## used to bypass files with with same titles
    titles = []
    
    cached_files = os.listdir(config['cache'])
    cache_hashes = []
    ## retrieve cached files hashes from filenames
    for file in cached_files:
        cache_hashes.append(file[:10])
    
    ## run youtube_dl for every url
    for url in urls:
        err('[INFO] Processing URL %s' % url[0])
        urlhash = md5(url[0].encode('utf-8')).hexdigest()[:10]
        
        ## bypass already downloaded urls
        if urlhash not in cache_hashes:
            ytopt['outtmpl'] = ytopt['outtmpl'].replace('[__HASH__]', urlhash)
            with Yt(ytopt) as ytd:
                # we have to pass single url instead of list of urls
                # otherwise 'max_downloads' option will not work
                try:
                    ytd.download((url[0],))
                    if recent_dl != None:
                        #file_to_store = recent_dl[0].replace('[__HASH__]', urlhash)
                        #filename_to_store = os.path.basename(file_to_store)
                        filename_to_store = os.path.basename(recent_dl[0])
                        _recent_dl = (filename_to_store, recent_dl[1], recent_dl[2], url[1])
                        if _recent_dl in files:
                            err('[SKIP] file "%s" already downloaded recently' % recent_dl[1])
                        elif recent_dl[1] in titles:
                            err('[SKIP] title "%s" already exists' % recent_dl[1])
                        else:
                            ## add hash value to filename
                            #os.rename(recent_dl[0], file_to_store)
                            ## store tuple (filename, title, size, post_author)
                            files.append(_recent_dl)
                            cache_hashes.append(urlhash)
                            titles.append(recent_dl[1])
                except MaxDownloadsReached:
                    err('__DEBUG__: MaxDownloadsReached.')
                except KeyboardInterrupt:
                    err('\nTerminated.')
                    quit(0)
                except:
                    err('__DEBUG__: Unknown YtDl Error with %s' % url[0])
        else:
            ## url found in cache, bypass downloading
            ## find filename from list by hash 
            matched_file = [cf for cf in cached_files if urlhash in cf][0]
            matched_file = matched_file.decode('utf-8')
            err('URL hash `%s` already in cache, skipping download and reusing.' % urlhash)
            title = filetitle(matched_file, striphash = True)
            if title in titles:
                err('[SKIP] title "%s" already exists' % title)
            else:
                matched_file_path = os.path.join(config['cache'], matched_file)
                file_size = os.path.getsize(matched_file_path)
                files.append((matched_file, title, file_size, url[1]))
                titles.append(title)
        
        err('')


    if len(files) == 0:
        sys.stderr.write('Empty files list, exiting.\n')
        quit(1)

    # Sorting files by size
    files.sort(key=lambda tup: tup[2])
    
    ## write m3u playlist
    playlist_filename = '%s.m3u' % config['name']
    playlist_filename_tmp = '%s.tmp.m3u' % config['name']
    playlist_filename_prev = '%s.prev.m3u' % config['name']
    playlist_file = os.path.join(config['cache'], playlist_filename)
    playlist_file_tmp = os.path.join(config['cache'], playlist_filename_tmp)
    playlist_file_prev = os.path.join(config['cache'], playlist_filename_prev)

    with open(playlist_file_tmp, 'w') as f:
        f.write('#EXTM3U\n\n')
        for track in files:
            ## track title format: title (post_author)
            playlist_item_title = '#EXTINF:-1,%s (by %s)\n' % (track[1], track[3])
            ## track URL
            playlist_item = os.path.join(config['sitedir'], 
                urllib.quote(track[0].encode('utf-8')))
            f.write(playlist_item_title.encode('utf-8'))
            f.write(playlist_item)
            f.write('\n\n')

    err('Moving <%s> to <%s>' % (playlist_file, playlist_file_prev))
    try:
        os.rename(playlist_file, playlist_file_prev)
    except:
        err('Renaming failed.')
    err('Moving <%s> to <%s>' % (playlist_file_tmp, playlist_file))
    try:
        os.rename(playlist_file_tmp, playlist_file)
    except:
        err('Renaming failed.')
    
    ## Store playlist also in json format
    playlist_filename = '%s.playlist.json' % config['name']
    playlist_filename_tmp = '%s.playlist.tmp.json' % config['name']
    playlist_filename_prev = '%s.playlist.prev.json' % config['name']
    playlist_file = os.path.join(config['cache'], playlist_filename)
    playlist_file_tmp = os.path.join(config['cache'], playlist_filename_tmp)
    playlist_file_prev = os.path.join(config['cache'], playlist_filename_prev)
    
    with open(playlist_file_tmp, 'w') as f:
        json.dump(files, f, indent=4)
    
    err('Moving <%s> to <%s>' % (playlist_file, playlist_file_prev))
    try:
        os.rename(playlist_file, playlist_file_prev)
    except:
        err('Renaming failed.')
    err('Moving <%s> to <%s>' % (playlist_file_tmp, playlist_file))
    try:
        os.rename(playlist_file_tmp, playlist_file)
    except:
        err('Renaming failed.')

def quit(exitcode):
    if os.path.exists(still_running_flag):
        os.remove(still_running_flag)
    if exitcode >= 0:
        sys.exit(exitcode)

if __name__ == '__main__':
    if len(sys.argv) < 2:
        sys.stderr.write(txt_launch_error)
        quit(0)

    config_file = os.path.abspath(sys.argv[1])
    conf_dir = os.path.dirname(config_file)
    sql_conf_file = os.path.join(conf_dir, 'sql.json')
    
    ## load SQL strings
    with open(sql_conf_file, 'r') as f:
        SQLs = json.load(f)
    
    with open(config_file, 'r') as f:
        config = json.load(f)
        still_running_flag = os.path.join(config['cache'], '.running')
        ytopt['outtmpl'] = os.path.join(config['cache'], ytopt['outtmpl'])
        try:
            if not os.path.exists(still_running_flag):
                os.mknod(still_running_flag, 0644)
            main()
            quit(0)
        except KeyboardInterrupt:
            quit(0)
        except:
            quit(-1)
            raise

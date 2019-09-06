#!/usr/bin/env python2.7
from __future__ import unicode_literals

import simplejson as json
import sys, os
from time import sleep, time
import subprocess

txt_launch_error = '''No path given.

Example:
    %s /tmp/config.json
    \n''' % sys.argv[0]


def main():
    ## defaults:
    if 'jingle_period' in config and type(config['jingle_period']) == int and config['jingle_period'] > 0:
        jingle_period = config['jingle_period']
    else:
        jingle_period = 30000000
    
    ## Prepare jingles list ##
    if 'jingles' in config:
        if type(config['jingles']) == list and len(config['jingles']) > 0:
            jingles = config['jingles']
        elif type(config['jingles']) == str and os.path.isfile(config['jingles']):
            # Its a path to a json file, load it
            with open(config['jingles'], 'r') as f:
                jingles = json.load(f)
        else:
            jingles = []
    else:
        jingles = []
    
    if len(jingles) > 0:
        add_j = True
        if len(jingles) > 1:
            jn = 1
        else:
            jn = 0
    else:
        add_j = False
    ## end of prepare jingles ##
    
    dirname = os.path.dirname(config_file)
    cachedir = '%s/cache' % dirname
    cachelist_file = '%s/%s.playlist.json' % (cachedir, config['name'])
    with open(cachelist_file, 'r') as f:
        cachelist = json.load(f)
    
    #files = []
    #for item in cachelist
    
    ## Generate liquidsoap playlist
    lq_playlist_file = '%s/%s.playlist' % (dirname, config['name'])
    with open(lq_playlist_file, 'w') as f:
        if add_j:
            # Add a jingle in the beginning
            jingle = u'request.push ' + jingles[0] + '\n\n'
            f.write(jingle.encode('utf-8'))
        size_count = 0
        for item in cachelist:
            size_count += item[2]
            filename = '%s/%s' % (cachedir, os.path.basename(item[0]))
            playlist_item = u'request.push annotate:artist="%s",title="%s (by %s)":%s\n\n' % (config['title'], item[1], item[3], filename)
            f.write(playlist_item.encode('utf-8'))
            if add_j and size_count > jingle_period:
                jingle = u'request.push ' + jingles[jn] + '\n\n'
                f.write(jingle.encode('utf-8'))
                size_count = 0
                jn += 1
                if jn >= len(jingles):
                    jn = 0
        if add_j:
            # Add last jingle to the end of playlist
            jingle = u'request.push ' + jingles[len(jingles)-1] + '\n\n'
            f.write(jingle.encode('utf-8'))

    ## add playlist to liquidsoap queue
    subprocess.call(['/usr/local/bin/autodj-play', 'list', lq_playlist_file])

if __name__ == '__main__':
    if len(sys.argv) < 2:
        sys.stderr.write(txt_launch_error)
        sys.exit(0)

    config_file = os.path.abspath(sys.argv[1])
    with open(config_file, 'r') as f:
        config = json.load(f)
    try:
        main()
    except KeyboardInterrupt:
        sys.exit(0)

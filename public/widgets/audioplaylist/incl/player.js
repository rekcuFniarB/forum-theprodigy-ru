/**
 * Convert seconds to time hh:mm:ss format
 */
Number.prototype.toHHMMSS = function () {
    var sec_num = parseInt(this, 10);
    var hours   = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);
    if (hours   < 10) {hours   = "0"+hours;}
    if (minutes < 10) {minutes = "0"+minutes;}
    if (seconds < 10) {seconds = "0"+seconds;}
    var time    = hours+':'+minutes+':'+seconds;
    return time;
}

/**
 * Self playlist player
 */

var Player = function (uiSelector, options) {
    this.options = {
        thumb: true,
        skin: 'default'
    };
    this.types = {
        ogg:  'audio/ogg; codecs=vorbis',
        mp3:  'audio/mpeg',
        m4a:  'audio/mp4',
        opus: 'audio/ogg; codecs=opus',
        aacp: 'audio/aacp',
        aac:  'audio/aac'
    };
    
    // empty wav file
    this.blank = 'data:audio/wav;base64,UklGRkYAAABXQVZFZm10IBAAAAABAAIARKwAABCxAgAEABAATElTVBoAAABJTkZPSVNGVA4AAABMYXZmNTYuMTYuMTAyAGRhdGEAAAAA';
    
    // what player should do. Values: play|pause|etc
    this.do = null;
    
    this.currentPlaylist = null;
    this.currentPlaylistName = '';
    this.currentPlaylistId = 0;
    this.currentTrack = 0;
    
    // list of playlists
    this.playlists = [];
        
    // events handlers
    this.on = new function() {
        var _this = this;
        // paused event handler
        this.paused = function() {
            parent.ui.btnPause.hide();
            parent.ui.btnPlay.show();
            parent.do = 'pause';
        }; // on pause
        
        // play event handler
        this.playing = function() {
            parent.ui.btnPause.show();
            parent.ui.btnPlay.hide();
        }; // on playing
        
        // player ready event handler
        this.ready = function() {
            var todo = parent.do;
            var audio = parent.audio[0];
            if (todo == 'play') {
                audio.play();
            }
            else if (todo == 'pause') {
                audio.pause();
            }
        }; // on player ready
        
        // playback ended event handler
        this.ended = function() {
            // stop if last item
            if (parent.currentTrack < (parent.currentPlaylist.length - 1)) {
                parent.playNext();
            }
        }; // on playback ended
        
        // source error handler
        this.sourceError = function () {
            _this.ended();
        };
        
        // playback duration change event
        this.durationChange = function() {
            parent.ui.progress.time.text(parent.audio[0].duration.toHHMMSS());
        }; // on duration change
        
        this.redrawBar = function(width, time) {
            parent.ui.progress.bar.css('width', width);
            parent.ui.progress.time.html(time);
        };
        
        this.timeRound = 0;
        
        // playback time update event handler
        this.timeUpdate = function() {
            var self = this;
            var audio = parent.audio[0];
            if (audio.duration != NaN && audio.duration > 0) {
                var durRound = Math.round(audio.currentTime * 10);
                // don't redraw too often (~10Hz)
                if (durRound != this.timeRound) {
                    var width = ((audio.currentTime * 100) / audio.duration) + '%';
                    var time = audio.currentTime.toHHMMSS() + '&nbsp;/&nbsp;' + audio.duration.toHHMMSS();
                    if (typeof window.requestAnimationFrame !== 'undefined') {
                        window.requestAnimationFrame(function() {
                            self.redrawBar(width, time);
                        });
                    } else {
                        this.redrawBar(width, time);
                    }
                    this.timeRound = durRound;
                }
            }
        }; // on time update
        
        // progress bar touch
        this.touch = function(e) {
            var audio = parent.audio[0];
            if (audio.duration != NaN){
                var target = $(e.target);
                var offset = target.offset().left;
                var width = target.width();
                /** width --- duration /
                 *   cpos --- time    / time = (cpos*duration)/width
                 *                   /
                 * (e.pageX - offset.left) is relative click event position
                 */
                var newTime = (audio.duration * (e.pageX - offset)) / width;
                audio.currentTime = newTime;
            }
        }; // on touch
    }; // event handlers
    
    this.utils = new function() {
        this.URL = function(url, delimeter) {
            if (typeof delimeter == 'undefined') {
                var delimeter = '&';
            }
            var href = document.createElement('a');
            href.href = url;
            var t_args = href.search.substring(1).split(delimeter);
            var args = {};
            for (var i=0; i<t_args.length; i++) {
                arg = t_args[i].split('=');
                // normalize bool values
                if (arg[1] == 'false') arg[1] = false;
                if (arg[1] == 'true') arg[1] = true;
                args[arg[0]] = arg[1];
            }
            
            href.args = args;
            return href;
        }; // utils.URL()
    }; // utils object
    
    this.play = function(src) {
        this.init();
        
        var audio = this.audio[0];
        var audioSrc = this.audio.source[0];
        
        if (!audio.paused) this.pause();
        
        if (typeof src === 'undefined') {
            if( audioSrc.src == '' || audioSrc.src == this.blank) {
                if (this.currentPlaylist == null) {
                    this.title('Select a track.');
                    return false;
                } else {
                    // Play probably first track if none selected
                    return this.playPlaylistItem(this.currentTrack);
                }
            } // if audio src is empty
            else {
                return audio.play();
            }
        }
        
        this.on.redrawBar('0%', ''); // reset progressbar
        this.do = 'play';
        audioSrc.src = src;
        audio.load();
    }; // play()
    
    this.pause = function() {
        this.do = 'pause';
        this.audio[0].pause();
    }; // pause()
    
    /**
     * Set title of current playing track
     * @param text string title
     */
    this.title = function(text) {
        this.ui.title.text(text);
        document.title = text;
    }; // set title
    
    /**
     * List of playlists click event handler
     * Load selected playlist
     */
    this.playlistsClick = function(e) {
        //var target = $(e.currentTarget);
        var target = $(e.target);
        if (target.hasClass('playlists-item-name')){
            // list of playlists item was clicked
            var id = target.data('playlist-id');
            var src = target.data('src');
            var name = target.text();
            var type = target.data('type');
            var loaded = target.data('loaded');
            var pl_cont = target.next();
            //this.do = 'play'; // should autostart playing 
            if (typeof this.playlists[id]['playlist'] === 'undefined') {
                // load remote playlist if not loaded
                this.loadPlaylist(src, name, id, type);
            }
            if (target.parent().hasClass('current-playlist')) {
                // current playlist was clicked, just toggle it
                target.parent().toggleClass('current-playlist');
            } else {
                // non current playlist was clicked, hide current, display selected
                $(this.ui.selector + ' li.current-playlist').toggleClass('current-playlist');
                target.parent().toggleClass('current-playlist');
            }
        }
        else if (target.parent().hasClass('playlist-item-track')) {
            // track item was clicked, play selected track
            var targetTrack = target.parent();
            var targetTrackId = targetTrack.data('track-id');
            this.currentPlaylistId = targetTrack.data('playlist-id');
            this.currentPlaylist = this.playlists[this.currentPlaylistId].playlist;
            this.playPlaylistItem(targetTrackId);
        }
        //this.ui.playlistName.text(name);
        //this.playPlaylistItem(id);
    }; // playlistsClick()
    
    
    
    this.playPlaylistItem = function (id) {
        var item = this.currentPlaylist[id];
        this.currentTrack = id;
        this.title(item.title);
        this.play(item.src);
        this.setThumbnail(item.thumbnail);
        // mark current track in playlist
        $(this.ui.selector + ' .playlist-item-current').toggleClass('playlist-item-current');
        $(this.ui.selector + ' li.playlists-item-id-' + this.currentPlaylistId + ' .playlist-item-track-' + id).toggleClass('playlist-item-current');
    }; // playPlaylistItem()
    
    this.playNext = function() {
        if (this.currentPlaylist == null) {
            this.title('Select a track.');
            return false;
        }
        var next = 0;
        if (this.currentTrack >= (this.currentPlaylist.length - 1)) {
            next = 0
        } else {
            next = this.currentTrack + 1;
        }
        this.playPlaylistItem(next);
        this.scrollPlaylist();
    }; // playNext()

    this.playPrev = function() {
        if (this.currentPlaylist == null) {
            this.title('Select a track.');
            return false;
        }
        var prev = 0;
        if (this.currentTrack == 0) {
            prev = this.currentPlaylist.length -1;
        } else {
            prev = this.currentTrack - 1;
        }
        this.playPlaylistItem(prev);
        this.scrollPlaylist();
    }; // playNext()
    
    // scroll to current track
    this.scrollPlaylist = function () {
        var trackTop = $(this.ui.selector + ' .playlist-item-current').position().top;
        var playlistScrollTop = this.ui.playlists.scrollTop();
        var height = this.ui.playlists.height();
        if (trackTop < 0 || trackTop > height - 30)
            this.ui.playlists.scrollTop(trackTop + playlistScrollTop - 30);
    };
    
    this.setThumbnail = function(src) {
        if (this.options.thumb) {
            this.ui.thumbnail.empty();
            if (typeof src === 'undefined') {
                return false;
            } else {
                var thumb = $('<img/>', {
                    src: src
                }).appendTo(this.ui.thumbnail);
            }
        }
    }; // setThumbnail()
    
    this.initial = true;
    
    this.init = function () {
        if (!this.initial) return true; // already done
        this.initial = false;
        
        //  creating audio element
        this.audio = $('<audio/>', {
            preload: 'none'
        });
        
        // start playback when player is ready to play
        this.audio[0].addEventListener('loadedmetadata', this.on.ready, false);
        // other events bindings
        this.audio[0].addEventListener('pause', this.on.paused, false);
        this.audio[0].addEventListener('playing', this.on.playing, false);
        this.audio[0].addEventListener('ended', this.on.ended, false);
        this.audio[0].addEventListener('error', this.on.ended, false);
        this.audio[0].addEventListener('durationchange', this.on.durationChange, false);
        this.audio[0].addEventListener('timeupdate', function(){_this.on.timeUpdate();}, false);
        
        this.audio.source = $('<source/>', {src: this.blank}).appendTo(this.audio);
        this.audio.source[0].addEventListener('error', this.on.sourceError, false);
        this.audio.appendTo(this.ui.container);
        
        this.title('Select a track to start playback...');
    }; // init()
    
    this.parsePlaylist = function(data) {
        var parsed = [];
        for (i=0; i<data.length; i++){
            var item = {};
            item.src = encodeURIComponent(data[i][0]);
            var proto = item.src.split(':')[0];
            if (proto != 'http' || proto != 'https') {
                if (typeof this.options.urlPrefix !== 'undefined') {
                    item.src = this.options.urlPrefix + item.src;
                }
            }
            var complexTitle = data[i][1].split(' - ');
            if (complexTitle.length > 1) {
                item.id = complexTitle[complexTitle.length -1];
                item.title = data[i][1].replace(' - ' + item.id, '');
                item.thumbnail = 'https://img.youtube.com/vi/' + item.id + '/mqdefault.jpg';
            } else {
                item.title = data[i][1];
                item.thumbnail = 'https://img.youtube.com/vi/NO_THUMBNAIL/mqdefault.jpg';
            }
            item.author = data[i][3];
            parsed.push(item);
        }
        return parsed;
    }; //parsePlaylist()
    
    /**
     * create html playlist UI
     */
    this.buildPlaylist = function(data, name, id) {
        var pl_ul = $('<ul/>');
        for (i=0; i<data.length; i++) {
            var pli = $('<li/>', {
                class: 'playlist-item playlist-item-track playlist-item-track-' + i
            }).data({
                'track-id': i,
                'playlist-id': id
            });
            var title = $('<div/>', {text: data[i].title}).appendTo(pli);
            if (typeof data[i].author !== 'undefined' && data[i].author != '') {
                var author = $('<div/>', {text: '(by ' + data[i].author + ')'}).appendTo(pli);
            }
            pli.appendTo(pl_ul);
        }
        var target = $(this.ui.selector + ' li.playlists-item-id-'+id+'>div.playlists-item-playlist');
        target.empty().append(pl_ul);
        this.playlists[id]['playlist'] = data;
        if (this.currentPlaylist == null) {
            this.currentPlaylist = this.playlists[id]['playlist'];
            this.currentPlaylistId = id;
        }
    }; // buildPlaylist()
    
    /**
     * Load playlist. If string passed load remote playlist.
     * if object is passed just using it as playlist
     * @param list playlist object or url to load remote playlist
     * @param name playlist name
     * @param id playlist id
     * @param type optional playlis format type
     */
    this.loadPlaylist = function(list, name, id, type) {
        if (typeof type === 'undefined') var type = 1;
        if (typeof list === 'string') {
            $.ajax({
                url: list,
                timeout: 5000,
                dataType: 'json',
                cache: false,
                //context: this,
                success: function(media){
                    if (type == 2) {
                        _this.buildPlaylist(_this.parsePlaylist(media), name, id);
                    } else {
                        _this.buildPlaylist(media, name, id);
                    }
                    //if (_this.do == 'play') {
                    //    // start playback
                    //    _this.playPlaylistItem(0);
                    //}
                }
            }); // ajax
        } // if list is string (url)
        else {
            if (type == 2) {
                this.buildPlaylist(this.parsePlaylist(list), name, id);
            } else {
                this.buildPlaylist(list, name, id);
            }
            //if (this.do == 'play') {
            //    // start playback
            //    this.playPlaylistItem(0);
            //}
        }
    }; // loadPlaylist()
    
    this.initPlaylist = function(playlist) {
        if (!this.options.thumb) {
            // disable thumbnail
            this.ui.thumbnail.parent('td').hide();
        }
        if (typeof playlist === 'object') {
            this._initPlaylist(playlist);
        }
        else if (typeof playlist === 'string') {
            // it's probably an url, load remote playlist
            $.ajax({
                url: playlist,
                dataType: 'json',
                timeout: 5000,
                cache: false,
                success: function(media) {
                    _this._initPlaylist(media);
                } // ajax success
            }); // ajax
        } else {
            this.title('Wrong playlist format type supplied.');
        }
    }; // initPlaylist()
    
    /**
     * Display list of available playlist
     */
    this._initPlaylist = function(playlist) {
        this.playlists = playlist;
        var plul = $(this.ui.playlists).children('ul');
        if (plul.length == 0) {
            var plul = $('<ul/>');
            for (var i=0; i<this.playlists.length; i++) {
                var pltype = 1;
                if (typeof this.playlists[i]['type'] !== 'undefined') {
                    pltype = this.playlists[i]['type'];
                }
                var pli = $('<li/>', {
                    class: 'playlists-item playlists-item-id-' + i,
                }).
                appendTo(plul);
                $('<div/>', {
                        class: 'playlist-item playlists-item-name'
                }).
                    data({
                        'playlist-id': i,
                        'src': this.playlists[i]['src'],
                        'type': pltype,
                        'loaded': false
                    }).
                    text(this.playlists[i]['name']).
                    prepend('<div class="list-bullet"><div class="list-bullet-collapsed">&#9657;</div><div class="list-bullet-active">&#9663;</div></div>').
                    appendTo(pli);
                var pli_pl = $('<div/>', {class: 'playlists-item-playlist'}).
                    appendTo(pli);
            }
            plul.appendTo(this.ui.playlists);
            if (this.playlists.length == 1) {
                // We have 1 playlist only, uncollapse it
                this.loadPlaylist(this.playlists[0].src, this.playlists[0].name, 0, this.playlists[0].type);
                $(this.ui.selector + ' li.playlists-item-id-0').toggleClass('current-playlist');
            }
        } // if no playlists
        //this.ui.playlists.show();
    }; // showPlaylists()
    
    /**
     * change options on the fly
     * @param opts dict: options
     */
    this.setOpts = function(opts) {
        $.extend(this.options, opts);
    };
    
    /**
     * Init
     */
    if (typeof options !== 'undefined') $.extend(this.options, options);
    
    var _this = this;
    var parent = this;
    this.ui = {};
    this.ui.selector = uiSelector;
    this.ui.container = $(uiSelector);
    this.ui.control = $(uiSelector + ' .player-control');
    this.ui.btnPlay = $(uiSelector + ' .player-control > .player-btn.play');
    this.ui.btnPause = $(uiSelector + ' .player-control > .player-btn.pause').hide();
    this.ui.btnPrev = $(uiSelector + ' .player-control > .player-btn.prev');
    this.ui.btnNext = $(uiSelector + ' .player-control > .player-btn.next');
    //this.ui.playlist = $(uiSelector + ' .player-playlist');
    this.ui.title = $(uiSelector + ' .player-playback-title');
    this.ui.thumbnail = $(uiSelector + ' div.player-thumbnail');
    this.ui.progress = {
        touch: $(uiSelector + ' div.player-playback-progress > div.player-playback-touch'),
        time: $(uiSelector + ' div.player-playback-progress > div.player-playback-time'),
        bar: $(uiSelector + ' div.player-playback-progress > div.player-playback-progressbar'),
    }
    this.ui.playlists = $(uiSelector + ' .player-playlists');
    //this.ui.playlistsBtn = $(uiSelector + ' .player-btn.player-playlists-toggle').hide();
    //this.ui.playlistBtn = $(uiSelector + ' .player-btn.player-playlist-toggle').hide();
    //this.ui.playlistName = $(uiSelector + ' .player-playlist-name');
    
    if (!this.options.thumb) {
        // disable thumbnail
        this.ui.thumbnail.parent('td').hide();
    }
    
    /**
     * Bindings
     */
    this.ui.btnPlay.on('click', function() {
        _this.play();
    });
    this.ui.btnPause.on('click', function() {
        _this.pause();
    });
    this.ui.btnPrev.on('click', function() {
        _this.playPrev();
    });
    this.ui.btnNext.on('click', function() {
        _this.playNext();
    });
    //this.ui.playlist.on('click', 'li', function(e) {
    //    _this.playlistClick(e);
    //});
    this.ui.playlists.on('click', 'li', function(e) {
        _this.playlistsClick(e);
    });
    this.ui.progress.touch.on('click', function(e) {
        _this.on.touch(e);
    });
    //this.ui.playlistName.on('click', function() {
    //    _this.ui.playlists.hide();
    //    _this.ui.playlist.show();
    //});
//     this.ui.playlistsBtn.on('click', function() {
//         _this.showPlaylists();
//     });
}

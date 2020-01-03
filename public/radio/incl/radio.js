/*
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

var Radio = function(s){
    this.pl = s;
    this.timer = '';
    this.media = '';
    this.stream_date = '';
    this.types = {
        ogg:  'audio/ogg; codecs=vorbis',
        mp3:  'audio/mpeg',
        m4a:  'audio/mp4',
        opus: 'audio/ogg; codecs=opus',
        aacp: 'audio/aacp',
        aac:  'audio/aac'
    };
    this.blank = 'data:audio/wav;base64,UklGRkYAAABXQVZFZm10IBAAAAABAAIARKwAABCxAgAEABAATElTVBoAAABJTkZPSVNGVA4AAABMYXZmNTYuMTYuMTAyAGRhdGEAAAAA';
    this.preferredStream = 'main';
    this.streamNumber = 0;
    
    this.checkCanPlay = function(supplied){
        if (typeof supplied === 'string') {
            supplied = supplied.split(',');
            this.streamNumber = 0;
        }
        var canPlayMaybe, canPlayProbably, canPlay;
        for (var i=this.streamNumber; i<supplied.length; i++){
            if (this.player.canPlayType(this.types[supplied[i]]) == 'probably'){
                canPlayProbably = supplied[i];
                //this.streamNumber = i;
                break;
            } else if (this.player.canPlayType(this.types[supplied[i]]) == 'maybe'){
                canPlayMaybe = supplied[i];
                //this.streamNumber = i;
                break;
            }
        }
        if (canPlayProbably)
            canPlay = canPlayProbably;
        else if (canPlayMaybe)
            canPlay = canPlayMaybe;
        else canPlay = supplied[supplied.length-1];
        return canPlay;
    };
    
    this.play = function(media){
        var player = this.player;
        if (typeof media === 'undefined'){
        // Get current location and play it
            var media = document.location.href.split("#")[1];
        }
        if (typeof media === 'undefined' || media == ''){
            this.playRadio();
        } else {
            if (player.paused && player.currentTime > 0 && !player.ended && player.readyState > 2) {
                player.play();
            } else {
                this.playRecord(media);
            }
        }
    }; // End of play()
    
    this.playRecord = function(file){
        //this.player.pause();
        var title = decodeURIComponent(file);
        var t = this;
        clearInterval(this.timer);
        var item = $('#Playlist a[href="#'+file+'"]');
        var supplied = item.attr('fmt').split(',');
        file = "Recordings/" + file;
        var sources = [];
        for (var i=0; i<supplied.length; i++) {
            sources.push(file + '.' + supplied[i])
        }
        this.pause();
        this.player.isRadio = false;
        this.playSrc(sources);
        $("#play-radio-button").show();
        this.player.ui.progressBar.show();
        $(".radio-elements").hide();
        this.timer = setInterval(function(){t.player.timeUpdate();}, 1000);
        this.highlightPlaying(item);
        this.player.ui.title.text(title);
        document.title = title;
    }; // End of play_record()
    
    this.playRadio = function(){
        var t = this;
        this.player.stop();
        if (this.preferredStream == 'main') {
            var sources = this.media.mainStreams.slice();
            for (i=0; i<sources.length; i++) {
                sources[i] = this.media.icecast + this.media.media[sources[i]];
            }
        } else {
            var sources = this.media.icecast + this.media.media[this.preferredStream];
        }
        this.player.isRadio = true;
        this.playSrc(sources);
        $('.playing').toggleClass('playing');
        $("#play-radio-button").hide();
        this.player.ui.progressBar.hide();
        $(".radio-elements").show();
        clearInterval(this.timer);
        this.updateMetadata();
        this.timer = setInterval(function(){t.updateMetadata(); t.streamNumber = 0;}, 20000);
    }; // End of playRadio()
    
    this.src = function(src) {
        if (typeof src === 'undefined') {
            return this.player.src;
        }
        //this.player.source.src = this.blank;
        this.player.setSrc(src);
    };
    
    this.playSrc = function(src) {
        this.src(src);
        this.player.load();
        //this.player.play();
        this.player.addEventListener('loadedmetadata', function tmpfn(e) {
            var player = e.target;
            player.removeEventListener('loadedmetadata', tmpfn, false);
            player.play();
        }, false);
    };
    
    this.pause = function() {
        this.player.pause();
    };
    this.stop = function() {
        this.pause();
        this.src(this.blank);
        this.player.load();
    };
    
    this.playerErrorHandler = function() {
        //var mainStreamsCount = this.media.mainStreams.length - 1;
        //if (this.player.isRadio) {
            //if (this.streamNumber < mainStreamsCount) {
                //this.streamNumber++;
                //this.playRadio(true);
            //} else {
                //this.streamNumber = 0;
                //this.player.pause();
            //}
        //} else {
            //if (!this.player.paused)
            //this.player.pause();
            //this.player.ui.title.text('Не удалось воспроизвести.');
        //}
    };
    
    this.qualityChangeHandler = function(e) {
        this.preferredStream = $(e.target).val();
        //this.streamNumber = 0;
        this.playRadio();
    }; // qualityChangeHandler()
    
    this.highlightPlaying = function(item){
        $('.playing').toggleClass('playing');
        if (typeof item === 'undefined'){
            var file = document.location.href.split("#")[1];
            var item = $('# Playlist a[href="#'+file+'"]');
        }
        item.parent().toggleClass('playing').parents('ul').show();
    }; // End of highlightPlaying()
    
    this.refreshMedia = function(){
        var self = this;
        $.ajax({
            url: "Config/Radio-playlist.json",
            timeout: 5000,
            dataType: 'json',
            cache: false,
            success: function(media){
                self.media = media;
            }
        });
    }; // End of refreshMedia()
    
    this.skip = function(){
        var self = this;
        $.ajax({
            url: "http://toxicity.myftp.org/radio/api/skip/",
            dataType: 'json',
            timeout: 5000,
            success: function(status){
                if(!status["OK"]){
                    self.player.ui.title.text("Невозможно пропустить.");
                } else {
                    self.player.ui.title.text("Пропускаем...");
                }
            }, // End of success function
            error: function(){
                self.player.ui.title.text("Невозможно пропустить.");
            }
        }); // End of $.ajax()
    }; // End of skip()
  
    this.updateMetadata = function(){
        var self = this;
        var radioURL, radioFmt;
        if (document.location.protocol == 'https:'){
            radioURL = '/index.php?action=api&query=radioinfo';
            radioFmt = 'json';
        } else {
            radioURL = self.media.icecast + "status-jsonp.xsl";
            radioFmt = 'jsonp';
        }
        $.ajax({
            url: radioURL,
            dataType: radioFmt,
            jsonpCallback: 'callback',
            timeout: 5000,
            success: function(data){
                if (!$.isEmptyObject(data)){ // response not empty
                    var streamDate = '';
                    var sources = data.icestats.source;
                    var live = autodj = actualStream = null;
                    var availableStreams = {};
                    // Prepare metadata
                    for (i=0; i<sources.length; i++){
                        if (typeof sources[i].stream_start !== 'undefined'){
                            sources[i].artist = (typeof sources[i].artist === 'undefined') ? 'untitled' : sources[i].artist;
                            sources[i].title = (typeof sources[i].title === 'undefined') ? 'untitled' : sources[i].title;
                            sources[i].server_name = (typeof sources[i].server_name === 'undefined') ? '' : ' - ' + sources[i].server_name;
                            sources[i].nowPlaying = sources[i].artist + ' - ' + sources[i].title + sources[i].server_name;
                            sources[i].listenurl = (typeof sources[i].listenurl === 'undefined') ? '' : sources[i].listenurl.replace(self.media.icecast, '');
                            streamDate += sources[i].stream_start + ' ';
                            availableStreams[sources[i].listenurl] = {
                                nowPlaying:  sources[i].nowPlaying,
                                serverName:  sources[i].server_name,
                                streamStart: sources[i].stream_start
                            }
                            
                            if (self.media.descr[sources[i].listenurl] == 'live'){
                                live = i;
                            } else if (self.media.descr[sources[i].listenurl] == 'autodj'){
                                autodj = i;
                            }
                        } // if stream is online
                    } // End of prepare metadata loop
                    
                    // Quality selector update
                    self.player.ui.qualSelector.children('option').each(function(){
                        var option = $(this);
                        var name = option.val();
                        if (name != 'main'){
                            if (typeof availableStreams[self.media.media[name]] === 'undefined'){
                                option.prop('disabled', true);
                                if (option.prop('selected')){
                                    option.prop('selected', false);
                                }
                            } else {
                                if (option.prop('disabled')){
                                    option.prop('disabled', false);
                                }
                            }
                        } // !main option
                    }); // quality selector update
                    
                    if (live !== null){
                        actualStream = live;
                    } else if (autodj !== null){
                        actualStream = autodj;
                    }
                    if (actualStream !== null){
                        self.media.media.title = sources[actualStream].nowPlaying;
                        //streamDate = sources[actualStream].stream_start;
                    } else {
                        self.media.media.title = 'Радио сейчас не в эфире.';
                    }
                    self.player.ui.title.text(self.media.media.title);
                    document.title = self.media.media.title;
                    
                    // Firefox bug workaround https://bugzilla.mozilla.org/show_bug.cgi?id=777642
                    //if (navigator.userAgent.indexOf('Firefox') != -1){
                        // Reset radio if there is changes in streams (e.g. new streamer conection e.t.c.)
                        if (self.stream_date != '' && self.stream_date != streamDate){
                            if (!self.player.paused && self.player.isRadio){
                                self.streamNumber = 0;
                                self.stop();
                                self.player.isRadio = false;
                                self.playRadio();
                            }
                        }
                    //} // if Firefox
                    self.stream_date = streamDate;
                } // Response not empty
                else {
                    // Got empty response
                    self.player.ui.title.text("Радио сейчас не в эфире.");
                }
            }, // Ajax success
            error: function(data){self.player.ui.title.text("Радио сейчас не в эфире.");}
        }); // End of ajax
    }; // End of update_metadata()
    
    this.init = function(){
        var self = this;
        // Load Playlists
        $.ajax({
            url: "Config/Playlist.html",
            dataType: "html",
            cache: false,
            success: function(playlist){
                $('#Playlist>ul').html(playlist);
        
                // Collapse list:
                $('#Playlist li>ul').hide();
                //self.highlightPlaying();

                // Open/close lists:
                $('#Playlist li>div').on('click', function(){
                    $(this).next('ul').slideToggle();});
  
                // Add download links and "playing" icon
                $('#Playlist li>a').wrap('<div class="track"></div>')
                   .each(function(){
                       var title = $(this).attr('href').substr(1);
                       var dl = $(this).attr('dl');
                       var html = ' <a href="Recordings/' + title + dl +'" target="_blank" class="dl" title="скачать">↧</a>';
                       $(this).after(html);
                    });
                $('#Playlist div.track').append('<div class="play-icon">▷</div>');

                // Display dl link on hover
                $('#Playlist li>div.track').hover(function(){$(this).children('a.dl').show();}, function(){$(this).children('a.dl').hide();});
                
                // Load Radio config
                $.ajax({
                    url: "Config/Radio-playlist.json",
                    timeout: 5000,
                    dataType: 'json',
                    cache: false,
                    //context: this,
                    success: function(media){
                        self.media = media;
                        if (typeof media.additionalStreams !== 'undefined') {
                            var select = $('<select/>').addClass('radio-qual radio-elements');
                            select.append('<option value="main" selected>Основной</option>');
                            for (var i=0; i<media.additionalStreams.length; i++) {
                                $('<option/>')
                                    .attr('value', media.additionalStreams[i])
                                    .text(media.descr[media.additionalStreams[i]])
                                    .appendTo(select);
                            }
                            select.appendTo(self.player.ui.find('div.radio-qual'))
                                .on('change', function(e){self.qualityChangeHandler(e)});
                            self.player.ui.qualSelector = select;
                        } // if additionalStreams exists
                        // Start playback
                        self.play();
                    } // Radio config load success
                }); // Radio config load ajax
            } // ajax success
        }); // End of ajax playlist load
        
        /*
         * Bindings
         */
        // Change player source on hashchange
        $(window).on('hashchange', function(){self.play();});
        // Click child <a> when <li> clicked
        $('#Playlist > ul').on("click", "li", function(event){
            var H = $(event.target).children("a").eq(0).attr("href");
            if (H){
                //self.play(H.split("#")[1]);
                document.location.href = H;
            }
        });
        // Stick player on scroll
        //$(window).scroll(this.player.stick);
        // Android workaround
         //$('#play-radio-button>a').on('click', function(){
             //self.playRadio(true);
         //});
    }; // End of init()
    
    var t = this; 
    this.player = document.createElement('audio');
    this.player.sources = [];
    for (var i=0; i<3; i++) {
        this.player.sources[i] = document.createElement('source');
        this.player.appendChild(this.player.sources[i]);
    }
    this.player.parent = this;
    this.player.isRadio = false;
    this.player.controls = false;
    this.player.volume = 1.0;
    this.player.preload = 'metadata';
    this.player.ui = $(s);
    this.player.ui.btnPause = this.player.ui.find('button.button-pause');
    this.player.ui.btnPlay = this.player.ui.find('button.button-play');
    this.player.ui.progressBar = this.player.ui.find('.progress');
    this.player.ui.progressBar.indicator = this.player.ui.find('.progress-indicator');
    this.player.ui.progressBar.time = this.player.ui.find('.progress-time');
    this.player.ui.progressBar.touch = this.player.ui.find('.progress-touch');
    this.player.ui.title = this.player.ui.find('div.title');
    
    if (typeof this.player.stop === 'undefined') {
        this.player.stop = function() {
            if (!t.player.paused) {
                t.player.pause();
            }
            t.player.setSrc(t.blank);
        };
    }
    
    this.player.setSrc = function(src) {
        var player = t.player;
        if (typeof src !== 'object') {
            src = [src];
        }
        // add blank sources
        if (src.length < player.sources.length) {
            for (var i = src.length; i < player.sources.length; i++) {
                src.push(player.parent.blank);
            }
        }
        // set sources
        for (i=0; i<player.sources.length; i++) {
            player.sources[i].src = src[i];
        }
    };

    this.player.onpause = function(e){
        var player = e.target;
        player.ui.btnPause.hide();
        player.ui.btnPlay.show();
        if (player.isRadio){
            // Don't let the radio be paused, it must be stopped instead.
            player.setSrc(player.parent.blank);
            player.load();
        }
    };
    this.player.onplay = function(e){
        var player = e.target;
        player.ui.btnPause.show();
        player.ui.btnPlay.hide();
    };
    
    //this.player.onerror = function(){
        //t.playerErrorHandler();
    //}
    //this.player.source.addEventListener('error', function(){t.playerErrorHandler();}, true);
    
    // Seek event handler
    this.player.ui.progressBar.touch.on('click', function(e){
        if (t.player.duration != NaN){
            var offset = $(this).offset().left;
            var width = $(this).width();
            /* width --- duration  /
             *   cpos --- time    / time = (cpos*duration)/width
             *                   /
             * (e.pageX - offset.left) is relative click event position
             */
            var newTime = (t.player.duration * (e.pageX - offset)) / width;
            t.player.currentTime = newTime;
        }
    });
    
    this.player.ondurationchange = function(){
        t.player.ui.progressBar.time.text(t.player.duration.toHHMMSS())
    };
    
    this.player.timeUpdate = function(){
        if (t.player.duration != NaN){
            t.player.ui.progressBar.indicator.css('width', ((t.player.currentTime*100)/t.player.duration) + '%');
            t.player.ui.progressBar.time.html(t.player.currentTime.toHHMMSS() + '&nbsp;/&nbsp;' + t.player.duration.toHHMMSS());
        }
    };
    this.player.ui.btnPlay.on('click', function(){
        var player = t.player;
        player.parent.play();
    });
    this.player.ui.btnPause.on('click', function(){
        if (!t.player.paused)
            t.player.pause();
    });
    
    this.player.ui[0].appendChild(this.player);
    
    // Stick player on scroll
    this.player.offset = this.player.ui.offset().top;
    this.player.stick = function(){
        var windowTop = $(window).scrollTop();
        //var playerOffset = t.player.ui.offset().top;
        if (windowTop > t.player.offset){
            t.player.ui.addClass('sticky');
        } else {
            t.player.ui.removeClass('sticky');
        }
    }; // End of stick function
    
}; // End of Radio object

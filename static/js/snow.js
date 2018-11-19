var Snow = new function() {
    this.chegamode = false;
    this.dxmode = false;
    this.snowFlakes = {};
    
    this.fall = function (flakes,fmaxr) {
        var step = 0.6;
        for ( i=0; i<flakes.length; i++ )
        flakes[i][1] += step*flakes[i][2];
    }; // fall()

    this.checkFlakesPosition = function (flakes,fmaxr,canvash) {
        for ( i=0; i<flakes.length; i++ )
            if ( flakes[i][1] > canvash+fmaxr ) {
                flakes[i][1] = -fmaxr;
                flakes[i][2] = Math.random()*fmaxr;
            }
    }; // checkFlakesPosition()

    this.wind = function () {
            
    };

    this.draw = function(flakes,canvasw,canvash,ctx) {
        ctx.clearRect(0,0,canvasw,canvash);
        for ( i=0; i<flakes.length; i++ ) {
            ctx.fillStyle = "rgba(255, 255, 255, " + .9 * ( canvash-flakes[i][1] )/canvash + ")";
            if (this.flakeMode == "2" || this.chegamode) {
                ctx.beginPath();
                ctx.drawImage(this.snowFlakes['cheginka'][0], flakes[i][0], 
                    flakes[i][1], 4*flakes[i][2], 4*flakes[i][2]);
                ctx.closePath();
            } else if (this.dxmode) {
                ctx.beginPath();
                ctx.drawImage(this.snowFlakes['dxinka'][0], flakes[i][0],
                    flakes[i][1], 4*flakes[i][2], 5.5*flakes[i][2]);
                ctx.closePath();
            } else if (this.flakeMode == "1" && this.snowflakeID != 'snowflake0') {
                ctx.beginPath();
                ctx.drawImage(this.snowFlakes[this.snowflakeID][0], flakes[i][0], 
                    flakes[i][1], 2*flakes[i][2], 2*flakes[i][2]);
                ctx.closePath();
            } else {
                ctx.beginPath();
                ctx.arc(flakes[i][0], flakes[i][1], flakes[i][2], 0, Math.PI*2, true); 
                ctx.closePath();
            }
            ctx.fill();
        }
    }; // draw()

    this.animation = function(flakes,fcount,fmaxr,canvasw,canvash,ctx) {
        var self = this;
        if (this.windowIsActive) {
            this.draw(flakes,canvasw,canvash,ctx);
            this.fall(flakes,fmaxr);
            this.wind();
            this.checkFlakesPosition(flakes,fmaxr,canvash);
        }
        if (typeof window.requestAnimationFrame === 'function') {
            window.requestAnimationFrame(function(){self.animation(flakes,fcount,fmaxr,canvasw,canvash,ctx);});
        }
    };
    
    this.getRandom = function(min, max) {
        min = Math.ceil(min);
        max = Math.floor(max);
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }; // getRandom()

    this.init = function() {
        var self = this;
        this.snowflakeID = "snowflake" + this.getRandom(0, 4);

        this.flakeMode = getCookie('flakemode');
        if (this.flakeMode == '') {
            setCookie('flakemode', '1', 31);
            this.flakeMode = '1';
        }
        
        this.windowIsActive = false;
        window.onfocus = function() {
            self.windowIsActive = true;
        };
        window.onblur = function() {
            self.windowIsActive = false;
        };
        window.onmousemove = function() {
            self.windowIsActive = true;
        };
    
        var currentLocation = Forum.Utils.URL(document.location.href, ';');
        if (currentLocation.args.board == '16') {
            if (currentLocation.args.threadid == '42267') {
                this.snowFlakes['dxinka'] = $('<img>').
                attr({
                    src: "YaBBImages/dxinka.png",
                    name: "dxinka",
                    id: "dxinka"}).
                css({
                    width: "40px",
                    height: "54px",
                    display: "none"}).
                appendTo('body');
                this.dxmode = true;
            } else {
                this.chegamode = true;
            }
        }
        
        this.snowFlakes['snowflake1'] = $('<img>').
            attr({
                src: "YaBBImages/snowflake1.png",
                name: "snowflake1",
                id: "snowflake1"}).
            css({
                width: "17px",
                height: "20px",
                display: "none"}).
            appendTo('body');
        
        this.snowFlakes['snowflake2'] = $('<img>').
            attr({
                src: "YaBBImages/snowflake2.png",
                name: "snowflake2",
                id: "snowflake2"}).
            css({
                width: "20px",
                height: "19px",
                display: "none"}).
            appendTo('body');
        
        this.snowFlakes['snowflake3'] = $('<img>').
            attr({
                src: "YaBBImages/snowflake3.png",
                name: "snowflake3",
                id: "snowflake3"}).
            css({
                width: "17px",
                height: "17px",
                display: "none"}).
            appendTo('body');
    
        this.snowFlakes['snowflake4'] = $('<img>').
            attr({
                src: "YaBBImages/snowflake4.png",
                name: "snowflake4",
                id: "snowflake4"}).
            css({
                width: "22px",
                height: "18px",
                display: "none"}).
            appendTo('body');
    
        this.snowFlakes['cheginka'] = $('<img>').
            attr({
                src: "YaBBImages/cheginka.png",
                name: "chegaflake",
                id: "chegaflake"}).
            css({
                width: "44px",
                height: "46px",
                display: "none"}).
            appendTo('body');

        this.canvas = $('<canvas/>').
            attr('id', 'canvas').
            css({
                display: 'block',
                position: 'fixed',
                'box-sizing': 'border-box',
                left: 0,
                top: 0,
                'z-index': -1
            }).
            appendTo('body');
        
        if (getCookie('disableSnowflakes2011') == '' && this.canvas[0].getContext('2d')) {
            this.canvas[0].width = window.innerWidth-20;
            this.canvas[0].height = 600;
            var ctx = this.canvas[0].getContext('2d');
    
            var fcount  = 120;
            var flakes  = new Array();
            var fmaxr   = 10;
            var canvasw = window.innerWidth-20;
            var canvash = 600;
    
            // init flakes
            for ( i=0; i<fcount; i++ ) {
                flakes[flakes.length] = new Array();
                flakes[flakes.length-1][0] = 10+Math.random()*(canvasw-2*fmaxr); // x
                flakes[flakes.length-1][1] = -fmaxr; // y
                flakes[flakes.length-1][2] = 0.2*fmaxr + Math.random()*(0.8*fmaxr); // r
                flakes[flakes.length-1][3] = 0; // type
            }
    
            if (typeof window.requestAnimationFrame === 'function') {
                window.requestAnimationFrame(function(){
                    self.animation(flakes, fcount, fmaxr, canvasw, canvash, ctx);
                });
            } else {
                // Fallback to setInterval if no requestAnimationFrame support
                setInterval(function(){
                    self.animation(flakes, fcount, fmaxr, canvasw, canvash, ctx)},50);
            }
        }
    }; // init()
} // Snow object

$(function() { // document.ready:
    Snow.init();
}); // document ready

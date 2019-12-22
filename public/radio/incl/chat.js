function Chat(server, roomName, msgWindow){
  var self = this;
  this.lastTimestamp = null;
  this.msgWindow = msgWindow;
  this.server = server; // "wss://domain:port"
  this.roomName = roomName;
  this.room = undefined;
  this.active = false;
  
  this.fallbackPoll = function(){
    $.ajax('/index.php', {
      type: 'GET',
      async: true,
      data: {room: this.roomName, action: 'chatroompoll', since: this.lastTimestamp},
      success: function(messages){
        self.printMsgs(messages);
      }
    });
  }; // End of fallbackPoll()
  
  // Print messages list
  this.printMsgs = function(messages) {
    for (i=0; i<messages.length; i++){
      self.printMsg(messages[i]);
      self.lastTimestamp = messages[i].date;
    }
  };
  
  // Timers IDs
  this._reconnectTimeOut = null;
  this._pingTimeout = null;
  
  // periodically check connection
  this.ping = function() {
    clearTimeout(self._reconnectTimeOut);
    clearTimeout(self._pingTimeout);
    // Reconnect if no pong (ping response)
    // Every message or ping delays reconnect time, so this shouldn't happen
    // if connecton is Ok. Reconnect if no pong in 10 minutes.
    self._reconnectTimeOut = setTimeout(self.init, 600000);
    // Ping server every 7 minutes
    self._pingTimeout = setTimeout(function(){
        console.log('[DEBUG] Sending ping...');
        self.room.send('__PING__');
    }, 420000);
  }; // ping()
  
  this.printMsg = function(message){
    var date = new Date((parseInt(message.date)) * 1000);
    var dateTimeStamp = date.toLocaleString();
    var timeStamp = date.toLocaleTimeString();
    var htmlTimestamp = '<span class="chat-timestamp" title="' + dateTimeStamp + '">[' + timeStamp + ']</span> ';
    var userLink = '';
    if (message.memberName == 'skype'){
      userLink ='<img src="/YaBBImages/Skype-icon-x17.png" title="' + dateTimeStamp + '">';
    } else {
      var userLink = '<a href="/?action=viewprofile;user=' + message.memberName + '" target="_blank" title="' + dateTimeStamp + '">' + message.realName + '</a>';
    }
    if (message.body == '__JOIN__') {
        var msg = htmlTimestamp + userLink + ' зашёл в комнату.' + '<br>'
    }
    else if (message.body == '__EXIT__') {
        var msg = htmlTimestamp + userLink + ' вышел из комнаты.' + '<br>'
    } else {
        var msg = htmlTimestamp + userLink + ': ' + message.body + '<br>'
    }
    this.msgWindow.append(msg);
    // Autoscroll
    this.scroll();
  }; // End of printMsg()
  
  this.scroll = function(){
    var sh = this.msgWindow.prop("scrollHeight") - this.msgWindow.height();
    if (sh - this.msgWindow.scrollTop() < 50)
    //this.msgWindow.animate({scrollTop: sh}, 'fast');
    this.msgWindow.scrollTop(sh);
  }; // End of scroll()
  
  this.checkResult = function(result, callback){
    switch(result){
        case '__ERROR__':
           callback('Произошла ошибка');
            break;
        case '__GUEST__':
            callback('<a href="/?action=login" target="_blank">Авторизуйтесь на форуме</a>.');
            break;
        case '__EMPTY__':
            callback('Пустое сообщение.');
            break;
        case '__OK__':
            // OK
            break;
        default:
            callback(result);
    }
  }; // End of checkResult()
  
  this.close = function() {
    clearTimeout(self._reconnectTimeOut);
    clearTimeout(self._pingTimeout);
    self.room.close();
  };
  
  this.init = function(){
    if (typeof WebSocket !== 'undefined'){
      if (typeof self.room === 'object' && self.room.readyState != self.room.CLOSED)
          self.close();
      console.log('[DEBUG] WS init...');
      self.room = new WebSocket(self.server + '/chat/' + self.roomName + '/');
      self.room.onopen = function(){
        self.scroll();
        self.active = true;
        self.ping();
      };
      self.room.onmessage = function(event){
        console.log(event.data);
        if (event.data != '__PONG__') {
          var data = JSON.parse(event.data);
          self.printMsg(data);
        }
        self.ping();
      };
    }
    else {
      console.log('[DEBUG] Fallback poll mode.');
      //alert('К сожалению, ваша версия браузера не поддерживается.');
      //$('button#chat-switch-on, button#chat-switch-off, #chat-window').toggle('slow');
      self.room = setInterval(function(){
        self.fallbackPoll();
      }, 5000);
      self.active = true;
    }
    
    // Initial message
    self.msgWindow.append('<div class="chat-notify">Соединяемся...</div>');
    
    // Send join notify
    $.ajax('/index.php', {
        type: 'POST',
        async: true,
        data: {
            requesttype: 'ajax',
            room: self.roomName,
            action: 'chatroomsend',
            message: '__JOIN__'
        }
    });
    
    //Get last messages befor our joining
    $.ajax('/index.php', {
        type: 'GET',
        async: true,
        data: {
            requesttype: 'ajax',
            room: self.roomName,
            action: 'lastmessages',
        },
        success: function(messages) {
            self.printMsgs(messages);
        }
    });
    
    $('#chatform').on('submit', function(e){
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax('/index.php', {
          type: 'POST',
          async: true,
          data: formData,
          success: function (result){
            $('#chatform>div>input[name="message"]').val('');
            self.checkResult(result, function(r){
              self.msgWindow.append('<div class="chat-notify">⚠ '+r+'</div>');
              self.scroll();
            });
          },
          error: function(){alert('Произошла обишка.\nПовторите попытку позже.');}
        }); // End of ajax post
    }); // End of "on submit"
  }; // End of init();
  
  this.destroy = function(){
    $('#chatform').off('submit');
    if (typeof self.room === 'object' && self.room.readyState != self.room.CLOSED)
      self.close();
    if (typeof self.room === 'number')
      clearInterval(self.room);
    $('#chatmessages').html('');
    self.lastTimestamp = null;
    // Send exit notify
    if (self.active) {
        $.ajax('/index.php', {
            type: 'POST',
            async: true,
            data: {
                requesttype: 'ajax',
                room: self.roomName,
                action: 'chatroomsend',
                message: '__EXIT__'
            }
        });
    }
    self.active = false;
  }; // End of destroy

  //// Chat bindings
  // On/Off switch
  $('button.chat-switchers').on('click', function(e){
    if($(e.target).hasClass('chat-on-off')) {
      $('button#chat-switch-on, button#chat-switch-off').toggle();
      $('#chat-window').toggle('slow', function(){
        if ($('#chat-window').is(':visible')) {
          self.init();
          $('button.chat-btn-detach').show();
        }
        else {
          self.destroy();
          $('button.chat-btn-shrink, button.chat-btn-detach').hide();
          $('#chat').removeClass('chat-detach');
          $('#logo').show();
        }
      });
    }
    else if ($(e.target).hasClass('chat-btn-detach')) {
        $('#chat').addClass('chat-detach');
        $('button.chat-btn-shrink').show();
        $('button.chat-btn-detach').hide();
        $('#logo').hide();
    }
    else if ($(e.target).hasClass('chat-btn-shrink')) {
        $('#chat').removeClass('chat-detach');
        $('button.chat-btn-shrink').hide();
        $('button.chat-btn-detach').show();
        $('#logo').show();
    }
  });
  $(window).unload(function(){
    self.destroy();
  });
} // End of Chat object

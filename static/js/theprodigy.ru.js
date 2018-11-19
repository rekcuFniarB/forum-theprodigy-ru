// Check the existance of the console object.
if (!window.console) console = {};
console.log = console.log || function(){};
console.warn = console.warn || function(){};
console.error = console.error || console.log;
console.info = console.info || function(){};

// Extend jQuery with functions to get request parameters
$.extend({
  getUrlVars: function(){
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split(';');
    for(var i = 0; i < hashes.length; i++)
    {
      hash = hashes[i].split('=');
      vars.push(hash[0]);
      vars[hash[0]] = hash[1];
    }
    return vars;
  },
  getUrlVar: function(name){
    return $.getUrlVars()[name];
  }
});

(function () {
  // Polyfil for IE9+
  if ( typeof window.CustomEvent === "function" ) return false;

  function CustomEvent ( event, params ) {
    params = params || { bubbles: false, cancelable: false, detail: undefined };
    var evt = document.createEvent( 'CustomEvent' );
    evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail );
    return evt;
   }

  CustomEvent.prototype = window.Event.prototype;

  window.CustomEvent = CustomEvent;
  
  // String.endsWith polyfil
  if (!String.prototype.endsWith) {
    String.prototype.endsWith = function(search, this_len) {
      if (this_len === undefined || this_len > this.length) {
        this_len = this.length;
      }
      return this.substring(this_len - search.length, this_len) === search;
    };
  } // end of endsWith polyfil
})();

var Forum = new Object();
Forum.isActiveWindow = true;

Forum.ready = false;
Forum.readyEvent = new CustomEvent('forumready');

/**
 * Set isActiveWindow to <code>false</code> when the user goes to another window or tab.
 */
$(window).blur(function() {
	Forum.isActiveWindow = false;
});

/**
 * Set isActiveWindow to <code>true</code> and make asynchronous data update when the user returns back to the page.
 */
$(window).focus(function() {
	Forum.isActiveWindow = true;
});

/**
 * Global definition of mouse-up event.
 * Release the captured popup windows.
 */
$(document).mouseup(function() {
	Forum.Utils.PopupWindow.captured = null;
});

/**
 * Global definition of key-down event.
 * Close the opened popup window on ESC key press.
 */
$(document).keydown(function(event) {
	// if ESC key is pressed
	if (event.keyCode == 27)
		Forum.Utils.PopupWindow.close();
});

/**
 * Global definition of the mouse-move event.
 * If there is a cpatured popup window, then move it with the mouse.
 */
$(document).mousemove(function(event)
{
	if (Forum.Utils.PopupWindow.captured != null)
	{
		var popup = $(Forum.Utils.PopupWindow.captured);
		
		// get the overlay size
		var overlayWidth = $('#overlay').width();
		var overlayHeight = $('#overlay').height();
		
		// calculate a new move
		var deltaX = event.pageX - Forum.Utils.PopupWindow.capturedPosition.left;
		var deltaY = event.pageY - Forum.Utils.PopupWindow.capturedPosition.top;
		
		// calculate a new position
		var newX = popup.offset().left + deltaX;
		var newY = popup.offset().top + deltaY;		
		
		// don't allow to move the popup window out of the overlay bounds
		if (newX < 0)
			newX = 0;
		else if (newX > overlayWidth - popup.width())
			newX = overlayWidth - popup.width();
		if (newY < 0)
			newY = 0;
		else if (newY > overlayHeight - popup.height())
			newY = overlayHeight - popup.height();
		
		// move the popup window to the new position
		var newPosition = new Forum.Utils.Position(newX, newY);
		popup.offset(newPosition);
		
		// update the captured position
		Forum.Utils.PopupWindow.capturedPosition.setLeft(event.pageX);
		Forum.Utils.PopupWindow.capturedPosition.setTop(event.pageY);
	}
});

/**
 * This function should be used for each definition of dynamic classes.
 * It accepts an object defining new class methods and attributes.
 * Note: a class constructor-function should be called "initialize".
 * @param {classBody} an object serving as prototype for the new class.
 * @returns a constructor function with the prototype property set to classBody.
 */
Forum.Class = function(classBody, extendedClass)
{
	// create an empty class constructor
	var classPrototype = function() {};
	
	// if class body is not an object,
	// then we can not create the class prototype. Abort.
	if (typeof(classBody) != 'object')
		return null;
	
	// create a class constructor
	if (typeof(classBody.initialize) == 'function')
		classPrototype = classBody.initialize;
	else if (typeof(extendedClass) == 'function')
		classPrototype = function() {extendedClass.apply(this, arguments);};	
		
	// inherit parents attributes and methods.
	if (extendedClass != null && typeof(extendedClass.prototype) == 'object')
		for (var attribute in extendedClass.prototype)
			if (classBody[attribute] === undefined)
				classBody[attribute] = extendedClass.prototype[attribute];

	// Create prototype of the class.
	classBody.initialize = undefined;
	classPrototype.prototype = classBody;

	return classPrototype;
};

var latestMessagesAndCommentsLevel = 1;

function getXmlHttp()
{
  var xmlhttp;
  try {
    xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
  } catch (e) {
    try {
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    } catch (E) {
      xmlhttp = false;
    }
  }
  if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
    xmlhttp = new XMLHttpRequest();
  }
  return xmlhttp;
}

function comment(msgId, boardId, threadId, startNum, sesc)
{
    var xmlhttp = getXmlHttp();
    var boardStr = '&board='+(boardId > -1 ? boardId : "");
    var quoteStr = (threadId != '' && startNum != '' && sesc != '') ? '&threadid='+threadId+'&start='+startNum+'&sesc='+sesc : '';
    var commentText= document.getElementById('commentBox'+msgId).value;
    commentText= encodeURIComponent(commentText);  // Эти симвлы раньше не отправлялись в запросе (BrainFucker)
    var params = "postid="+msgId+boardStr+"&comment="+ commentText +quoteStr;
    xmlhttp.open("POST", "/index.php?action=commentpost;requesttype=ajax", true);
    
    //Send the proper header information along with the request
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.setRequestHeader("Content-length", params.length);
    xmlhttp.setRequestHeader("Connection", "close");
    
    xmlhttp.onreadystatechange = function() {
      if (xmlhttp.readyState != 4) return;
      
      if (xmlhttp.status == 200) {
        clearTimeout(varTimeout);
        if (xmlhttp.responseText == 'EMPTY_COMMENT')
          alert("Введи комментарий сначала!");
        else if (xmlhttp.responseText == 'TOO_MANY_COMMENTS')
          alert("Превышен лимит в 10000 знаков для комментариев");
	else if (xmlhttp.responseText == 'DUPLICATE_COMMENT')
          alert("Комментарий уже добавлен");
	else if (xmlhttp.responseText == 'TOO_MUCH_COMMENTS')
          alert("Не пиши много комментариев подряд, лучше ответь сообщением.");
        else if (xmlhttp.responseText == 'MESSAGE_CLOSED_FOR_COMMENTS')
        {
          alert("Сообщение закрыто для комментариев");
          document.getElementById('lock'+msgId).src = "/YaBBImages/lock_closed.png";
          document.getElementById('commentBox'+msgId).style.display = 'none';
          document.getElementById('commentBtn'+msgId).style.display = 'none';
        } else {
			document.getElementById('commentBox'+msgId).value = "";
			var commentsContainer = document.getElementById('comments' + msgId);
                        commentsContainer.innerHTML = xmlhttp.responseText;
			if (! commentsContainer.classList.contains('comments-collapsed')) {
                            document.getElementById('commentForm' + msgId).style.display = 'none';
                        }
			// Notify user not to post too much comments
			if ($('#comments'+msgId+'>div.comment>div.comment-content').last().text().length > 1000)
			  alert("Пожалуйста, не пиши много комментариев подряд, лучше ответь сообщением.");
			
			// Update the user's message comments subscription (by default each commenting user is subscribed to the new message comments)
			var subscriptionLink = ($('#commentForm' + msgId).prev().children('a[class^="msgComments"]').length > 0) ?
									$('#commentForm' + msgId).prev().children('a[class^="msgComments"]')
									: null;
			Forum.Utils.MessageCommentsForm.updateSubscription(msgId, subscriptionLink);
        }
      }
    }
    
    xmlhttp.send(params);
    var varTimeout = setTimeout( function(){ xmlhttp.abort(); alert("Не удалось добавить комментарий. Попробуй ещё раз."); }, 10000);
}

function deletePostComment(msgId, lineNr, boardId, threadId, startNum, sesc)
{
  
  var commentAuthor = $('#comment' + msgId + '-' + lineNr + '>div>a').first().data('userid').toString();
  var confirmed = false;
  var mdfrzn = '';
  var selfDel = false;
  if (Forum.sessioninfo.userid == commentAuthor){
      confirmed = confirm("Удалить этот комментарий?");
      selfDel = true;
  }
  else {
      mdfrzn = prompt('Укажите причину удаления', '');
      if (mdfrzn != '' && mdfrzn != null && typeof(mdfrzn) !== 'undefined')
          confirmed = true;
  }
  
  //if (confirm("Удалить этот комментарий?"))
  if (confirmed)
  {
    var xmlhttp = getXmlHttp();
    var boardStr = '&board='+(boardId > -1 ? boardId : "");
    if (!selfDel)
        var rznStr = '&mdfrzn=' + encodeURIComponent(mdfrzn);
    else rznStr = '';
    var quoteStr = (threadId != -1 && startNum != -1 && sesc != -1) ? '&threadid='+threadId+'&start='+startNum+'&sesc='+sesc : '';
  
    var loadingIcon = document.getElementById('loading' + msgId + "-" + lineNr);
    loadingIcon.style.display = 'inline';
    xmlhttp.open("GET", "/index.php?action=deletepostcomment"+boardStr+"&requesttype=ajax&postid="+msgId+"&commentNr="+lineNr+rznStr+quoteStr, true);
  
    xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState != 4) return;
    
    if (xmlhttp.status == 200) {
		clearTimeout(varTimeout);
		document.getElementById('comments' + msgId).innerHTML = xmlhttp.responseText;
		loadingIcon.style.display = 'none';
    }
  }
  
    xmlhttp.send(null);
    var varTimeout = setTimeout( function(){ xmlhttp.abort(); document.getElementById('loading' + msgId + "-" + lineNr).style.display = 'none'; alert("Не удалось удалить комментарий. Попробуй ещё раз."); }, 10000);
  }
}

function lockUnlock(msgId, boardId)
{
  var xmlhttp = getXmlHttp();
  var lockElement = null;
  var lockAction = false;
  var boardStr = '&board='+(boardId > -1 ? boardId : "");
  var commentBoxLengthField = document.getElementById('commentBoxLength' + msgId);

  if (document.getElementById('lock'+msgId).src.indexOf('lock_closed.png') > 0) {
    lockElement = document.getElementById('lock'+msgId);
    lockAction = false; 
  } else if (document.getElementById('lock'+msgId).src.indexOf('lock_open.png') > 0) {
    lockElement = document.getElementById('lock'+msgId);
    lockAction = true;
  } else {
    alert("Ошибка при выполнении запроса!");
    return;
  }
    
  document.getElementById('lockloading' + msgId).style.display = 'inline-block';
  document.getElementById('lock' + msgId).style.display = 'none';
  xmlhttp.open("GET", "/index.php?action=lockpostcomments"+boardStr+"&requesttype=ajax&postid="+msgId+"&lock="+(lockAction?1:0), true);
  
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState != 4) return;
    
    if (xmlhttp.status == 200) {
      clearTimeout(varTimeout);
      document.getElementById('lockloading' + msgId).style.display = 'none';
      document.getElementById('lock' + msgId).style.display = 'inline-block';
      
      if (lockAction) {
        lockElement.src = "/YaBBImages/lock_closed.png";
        document.getElementById('commentBox'+msgId).style.display = 'none';
        document.getElementById('commentBtn'+msgId).style.display = 'none';
        commentBoxLengthField.style.display = 'none';
      } else {
        lockElement.src = "/YaBBImages/lock_open.png";
        document.getElementById('commentBox'+msgId).style.display = 'inline';
        document.getElementById('commentBox'+msgId).focus();
        document.getElementById('commentBtn'+msgId).style.display = 'inline';
        commentBoxLengthField.style.display = 'inline';
      }
    }
  }
  
  xmlhttp.send(null);
  var varTimeout = setTimeout( function(){
      xmlhttp.abort();
      document.getElementById('lockloading' + msgId).style.display = 'none';
      document.getElementById('lock' + msgId).style.display = 'inline-block';
      alert("Не удалось выполнить операцию. Попробуй ещё раз.");
  }, 10000);
}

function recentTopicComments(boardId, threadId, startNum, sesc)
{
  var xmlhttp = getXmlHttp();
  var boardStr = '&board='+(boardId > -1 ? boardId : "");
  var quoteStr = (startNum != -1 && sesc != -1) ? '&start='+startNum+'&sesc='+sesc : '';
  
  var numLastComments = document.getElementById('numLastComments').value;
  numLastComments = numLastComments < 10 ? 10 : numLastComments > 99 ? 10 : numLastComments;
  
  document.getElementById('recentcommentstable').innerHTML = "Идёт загрузка...";
  document.getElementById('recentcommentstable').style.display = 'block';
  document.getElementById('recentcommentstable').style.width = '100%';

  xmlhttp.open("GET", "/index.php?action=recenttopiccomments"+boardStr+quoteStr+"&requesttype=ajax&threadid="+threadId+"&num="+numLastComments, true);
  
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState != 4) return;
    
    if (xmlhttp.status == 200) {
      clearTimeout(varTimeout);
      
      if (xmlhttp.responseText == "NO_RECENT_COMMENTS_FOUND") {
        document.getElementById('recentcommentstable').style.display = 'none';
        alert("Комментариев в теме нет");
      } else {
        document.getElementById('numLastComments').value = numLastComments;
        document.getElementById('recentcommentstable').innerHTML = xmlhttp.responseText;
      }
    }
  }
  
  xmlhttp.send(null);
  var varTimeout = setTimeout( function(){ xmlhttp.abort(); document.getElementById('recentcommentstable').style.display = 'none'; alert("Не удалось выполнить операцию. Попробуй ещё раз."); }, 10000); 
}

function myRecentTopicComments(boardId, threadId, startNum, sesc)
{
  var xmlhttp = getXmlHttp();
  var boardStr = '&board='+(boardId > -1 ? boardId : "");
  var quoteStr = (startNum != -1 && sesc != -1) ? '&start='+startNum+'&sesc='+sesc : '';
  
  var numMsgsMyComments = document.getElementById('numMsgsMyComments').value;
  numMsgsMyComments = numMsgsMyComments < 1 ? 3 : numMsgsMyComments > 99 ? 3 : numMsgsMyComments;
  
  document.getElementById('recentcommentstable').innerHTML = "Идёт загрузка...";
  document.getElementById('recentcommentstable').style.display = 'block';
  document.getElementById('recentcommentstable').style.width = '100%';

  xmlhttp.open("GET", "/index.php?action=myrecenttopiccomments"+boardStr+quoteStr+"&requesttype=ajax&threadid="+threadId+"&numMsgs="+numMsgsMyComments, true);
  
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState != 4) return;
    
    if (xmlhttp.status == 200) {
      clearTimeout(varTimeout);
      
      if (xmlhttp.responseText == "NO_MY_RECENT_COMMENTS_FOUND") {
        document.getElementById('recentcommentstable').style.display = 'none';
        alert("Твоих комментариев в теме нет");
      } else {
        document.getElementById('numMsgsMyComments').value = numMsgsMyComments;
        document.getElementById('recentcommentstable').innerHTML = xmlhttp.responseText;
      }
    }
  }
  
  xmlhttp.send(null);
  var varTimeout = setTimeout( function(){ xmlhttp.abort(); document.getElementById('recentcommentstable').style.display = 'none'; alert("Не удалось выполнить операцию. Попробуй ещё раз."); }, 10000); 
}

function editChatWall(boardId)
{
  var xmlhttp = getXmlHttp();
  xmlhttp.open("GET", "/index.php?action=editchatwall;requesttype=ajax;board="+boardId+";ubbc=0", true);
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState != 4) return;
    
    if (xmlhttp.status == 200) {
      clearTimeout(varTimeout);
      var array = xmlhttp.responseText.split("#;#");
      document.getElementById("chatWall").innerHTML = '<font size="1"><center><textarea id="chatWallArea" name="chatWallArea" style="width: 90%; height: 300px; font-size: xx-small;" onkeypress="return (this.value.length < 10000);">'+array[0]+'</textarea><br /><i>(максимальная длина 10 000 знаков)</i></center><div style="margin: 10px 0px 0px 0px"><a href="javascript: saveChatWall('+boardId+', \'\'); void(0);"><img src="/YaBBImages/save.png" alt="сохранить" width="14" height="14" border="0" /></a> <a href="javascript: updateChatWall('+boardId+'); void(0);"><img src="/YaBBImages/cancel.png" alt="отменить" width="14" height="14" border="0" /></a>'+(array[1] != ""?'<div style="float: right; font-style: italic;">Последний автор: '+array[1]+'</div>':'')+'</div></font>';
    }
  }
  
  xmlhttp.send(null);
  var varTimeout = setTimeout( function(){ xmlhttp.abort(); alert("Не удалось выполнить операцию. Попробуй ещё раз."); }, 10000);
}

function saveChatWall(boardId, guestname)
{
  var xmlhttp = getXmlHttp();
  var guestnamestr = (guestname != '') ? ";guestname="+guestname : "";
  if (document.getElementById('chatWallArea') != null){
    chatwalltext=document.getElementById('chatWallArea').value;
    chatwalltext= encodeURIComponent(chatwalltext);
    var params = 'chatwalltext='+chatwalltext;
    }
  else params= null;
  
  
  xmlhttp.open("POST", "/index.php?action=savechatwall;requesttype=ajax;board="+boardId+guestnamestr, true);
  
  //Send the proper header information along with the request
  xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xmlhttp.setRequestHeader("Content-length", params!=null?params.length:0);
  xmlhttp.setRequestHeader("Connection", "close");
  
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState != 4) return;
    
    if (xmlhttp.status == 200) {
      clearTimeout(varTimeout);
      
      if (xmlhttp.responseText == "NO_NAME") {
        var eguestname = prompt("Укажи своё имя", "");
        saveChatWall(boardId, eguestname);
      } else if (xmlhttp.responseText == "DUPLICATE_NAME") {
        var dguestname = prompt("Такое имя уже зарегистрировано на форуме. Введи другое.", "");
        saveChatWall(boardId, dguestname);
      } else
        updateChatWall(boardId);
    }
  }
  
  xmlhttp.send(params);
  var varTimeout = setTimeout( function(){ xmlhttp.abort(); alert("Не удалось выполнить операцию. Попробуй ещё раз."); }, 10000);
}

function updateChatWall(boardId)
{
  var xmlhttp = getXmlHttp();
  if (document.getElementById('loading') != null)
    document.getElementById('loading').style.display = 'inline';
  xmlhttp.open("GET", "/index.php?action=updatechatwall;requesttype=ajax;board="+boardId+";ubbc=1", true);
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState != 4) return;
    
    if (xmlhttp.status == 200) {
      clearTimeout(varTimeout);
      if (document.getElementById('loading') != null)
        document.getElementById('loading').style.display = 'none';
      var array = xmlhttp.responseText.split("#;#");
      document.getElementById('chatWall').innerHTML = '<font size="1"><div id="chatWallText" style="margin: 0px 0px 10px 0px;">'+array[0]+'</div><div><a href="javascript: editChatWall('+boardId+'); void(0);"><img src="/YaBBImages/edit.png" alt="редактировать" width="14" height="14" border="0" /></a> <a href="javascript: updateChatWall('+boardId+'); void(0);"><img src="/YaBBImages/reload.png" alt="перезагрузить" width="14" height="14" border="0" /></a><img src="/YaBBImages/loading.gif" id="loading" alt="loading" style="display: none;" />'+(array[1] != ""?'<div style="float: right; font-style: italic;">Последний автор: '+array[1]+'</div>':'')+'</div></font>'; 
    }
  }
  
  xmlhttp.send(null);
  var varTimeout = setTimeout( function(){ xmlhttp.abort(); alert("Не удалось выполнить операцию. Попробуй ещё раз."); }, 10000);
}

function backupChatWall(boardId)
{
  var xmlhttp = getXmlHttp();
  xmlhttp.open("GET", "/index.php?action=backupchatwall;requesttype=ajax;board="+boardId, true);
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState != 4) return;
    
    if (xmlhttp.status == 200) {
      clearTimeout(varTimeout);
      if (xmlhttp.responseText == "NOT_PERMITTED")
        alert("Тебе не разрешено пользоваться этой функцией");
      else if (xmlhttp.responseText == "TRUE")
        alert("Резервная копия создана.");
      else
        alert("Произошла ошибка. Попробуй ещё раз.");
    }
  }
  
  xmlhttp.send(null);
  var varTimeout = setTimeout( function(){ xmlhttp.abort(); alert("Не удалось выполнить операцию. Попробуй ещё раз."); }, 10000);
}

function restoreChatWall(boardId)
{
  var xmlhttp = getXmlHttp();
  xmlhttp.open("GET", "/index.php?action=restorechatwall;requesttype=ajax;board="+boardId, true);
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState != 4) return;
    
    if (xmlhttp.status == 200) {
      clearTimeout(varTimeout);
      if (xmlhttp.responseText == "NOT_PERMITTED")
        alert("Тебе не разрешено пользоваться этой функцией");
      else if (xmlhttp.responseText == "FALSE")
        alert("Произошла ошибка. Попробуй ещё раз.");
      else
        updateChatWall(boardId);
    }
  }
  
  xmlhttp.send(null);
  var varTimeout = setTimeout( function(){ xmlhttp.abort(); alert("Не удалось выполнить операцию. Попробуй ещё раз."); }, 10000);
}

function quickReplyQuote(e, cgi, threadId, mid, title, start, sesc)
{
  var seltext = '';
  if (window.getSelection) seltext = window.getSelection();
  else if (document.getSelection) seltext = document.getSelection();
  else if (document.selection) seltext = document.selection.createRange().text;

  if (document.getElementById("QUICKREPLYAREA") == null || document.getElementById("QUICKREPLYAREA").value == null || !(e.ctrlKey || (seltext && seltext != '')))
    return true;
    
  var quickReplyArea = document.getElementById('QUICKREPLYAREA');

  if (seltext && seltext != '') {
    quickReplyArea.value += (quickReplyArea.value.length > 0)?'\n\n[quote]'+seltext+'[/quote]':'[quote]'+seltext+'[/quote]';
    quickReplyArea.focus();
    return false;
  }

  var xmlhttp = getXmlHttp();
  xmlhttp.open("GET", cgi+';action=post;threadid='+threadId+';quickreplyquote='+mid+';title='+title+';start='+start+';sesc='+sesc, true);
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState != 4) return true;
    
    if (xmlhttp.status == 200) {
      clearTimeout(varTimeout);

      quickReplyArea.value += (quickReplyArea.value.length > 0)?'\n\n'+xmlhttp.responseText:xmlhttp.responseText;
      quickReplyArea.focus();
      return false;
    }
  }
  
  xmlhttp.send(null);
  var varTimeout = setTimeout( function(){ xmlhttp.abort(); alert('Не удалось выполнить операцию. Попробуй ещё раз.'); }, 10000);
  return false;
}

function quickQuoteComment(e, comment){
  if (!e.ctrlKey) return true;
  
  qNick=$(comment).prev().prev().text(); // Get author of comment
  qMessage=$(comment).parent().children('span').text(); // Get comment text
  
  $('#QUICKREPLYAREA').val($('#QUICKREPLYAREA').val() + '[quote][b]' + qNick + ':[/b] ' + qMessage +'[/quote]\n\n'); // Add quoted comment to quick reply area
  
  return false;
}

function showCatMenu(cat)
{
  for (var i in cat.childNodes)
    if (cat.childNodes[i].className == "catMenu")
      cat.childNodes[i].style.display="block";
}

function hideCatMenu(cat)
{
      cat.style.display="none";
}

function editBillboard(catId)
{
  var xmlhttp = getXmlHttp();
  xmlhttp.open("GET", "/index.php?action=editbillboard;requesttype=ajax;cat="+catId+";ubbc=0", true);
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState != 4) return;
    
    if (xmlhttp.status == 200) {
      clearTimeout(varTimeout);
      var array = xmlhttp.responseText.split("#;#");
      var browser=navigator.appName;
      if (browser=="Microsoft Internet Explorer") {
        document.getElementById('billboard'+catId).parentNode.style.display = 'block';
        document.getElementById('billboard'+catId).style.display = 'block';
      } else {
        document.getElementById('billboard'+catId).parentNode.style.display = 'table-row';
        document.getElementById('billboard'+catId).style.display = 'table-cell';
      }
      document.getElementById('billboard'+catId).innerHTML = '<font size="1"><center><textarea id="billboard'+catId+'Area" name="billboardArea" style="width: 90%; height: 300px; font-size: xx-small;" onkeypress="return (this.value.length < 10000);">'+array[0]+'</textarea></center><div style="margin: 10px 0px 0px 0px"><a href="#" onclick="saveBillboard('+catId+'); return false;"><img src="/YaBBImages/save.png" alt="сохранить" width="14" height="14" border="0" /></a> <a href="#" onclick="updateBillboard('+catId+'); return false;"><img src="/YaBBImages/cancel.png" alt="отменить" width="14" height="14" border="0" /></a>'+(array[1] != ""?'<div style="float: right; font-style: italic;">Последний автор: '+array[1]+'</div>':'')+'</div></font>';
    }
  }
  
  xmlhttp.send(null);
  var varTimeout = setTimeout( function(){ xmlhttp.abort(); alert("Не удалось выполнить операцию. Попробуй ещё раз."); }, 10000);
}

function saveBillboard(catId)
{
  var xmlhttp = getXmlHttp();
  var params = document.getElementById('billboard'+catId+'Area') != null ?'billboardtext='+document.getElementById('billboard'+catId+'Area').value : null;
  xmlhttp.open("POST", "/index.php?action=savebillboard;requesttype=ajax;cat="+catId, true);
  
  //Send the proper header information along with the request
  xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xmlhttp.setRequestHeader("Content-length", params!=null?params.length:0);
  xmlhttp.setRequestHeader("Connection", "close");
  
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState != 4) return;
    
    if (xmlhttp.status == 200) {
      clearTimeout(varTimeout);
      
      if (xmlhttp.responseText == "ACCESS_RESTRICTED")
        alert("Тебе запрещён доступ к этой функции");
      else
        updateBillboard(catId);
    }
  }
  
  xmlhttp.send(params);
  var varTimeout = setTimeout( function(){ xmlhttp.abort(); alert("Не удалось выполнить операцию. Попробуй ещё раз."); }, 10000);
}

function updateBillboard(catId)
{
  var xmlhttp = getXmlHttp();
  xmlhttp.open("GET", "/index.php?action=updatebillboard;requesttype=ajax;cat="+catId+";ubbc=1", true);
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState != 4) return;
    
    if (xmlhttp.status == 200) {
      clearTimeout(varTimeout);
 
      var array = xmlhttp.responseText.split("#;#");
      if (array[0]=="") {
        document.getElementById('billboard'+catId).parentNode.style.display = 'none';
        document.getElementById('billboard'+catId).style.display = 'none';
      }
      document.getElementById('billboard'+catId).innerHTML = '<font size="1"><div id="billboard'+catId+'Text">'+array[0]+'</div></font>'; 
    }
  }
  
  xmlhttp.send(null);
  var varTimeout = setTimeout( function(){ xmlhttp.abort(); alert("Не удалось выполнить операцию. Попробуй ещё раз."); }, 10000);
}

function backupBillboard(catId)
{
  var xmlhttp = getXmlHttp();
  xmlhttp.open("GET", "/index.php?action=backupbillboard;requesttype=ajax;cat="+catId, true);
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState != 4) return;
    
    if (xmlhttp.status == 200) {
      clearTimeout(varTimeout);
      if (xmlhttp.responseText == "NOT_PERMITTED")
        alert("Тебе не разрешено пользоваться этой функцией");
      else if (xmlhttp.responseText == "TRUE")
        alert("Резервная копия создана.");
      else
        alert("Произошла ошибка. Попробуй ещё раз.");
    }
  }
  
  xmlhttp.send(null);
  var varTimeout = setTimeout( function(){ xmlhttp.abort(); alert("Не удалось выполнить операцию. Попробуй ещё раз."); }, 10000);
}

function restoreBillboard(catId)
{
  var xmlhttp = getXmlHttp();
  xmlhttp.open("GET", "/index.php?action=restorebillboard;requesttype=ajax;cat="+catId, true);
  xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState != 4) return;
    
    if (xmlhttp.status == 200) {
      clearTimeout(varTimeout);
      if (xmlhttp.responseText == "NOT_PERMITTED")
        alert("Тебе не разрешено пользоваться этой функцией");
      else if (xmlhttp.responseText == "FALSE")
        alert("Произошла ошибка. Попробуй ещё раз.");
      else
        updateBillboard(catId);
    }
  }
  
  xmlhttp.send(null);
  var varTimeout = setTimeout( function(){ xmlhttp.abort(); alert("Не удалось выполнить операцию. Попробуй ещё раз."); }, 10000);
}

function fetchTopicBillboard(topic_id, ubbcFormat)
{
	var xmlhttp = getXmlHttp();
	xmlhttp.open("GET", "/index.php?action=fetchtopicbillboard;requesttype=ajax;threadid="+topic_id+";ubbc="+(ubbcFormat?1:0), true);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState != 4) return;
    
		if (xmlhttp.status == 200) {
			clearTimeout(varTimeout);
      
			if (xmlhttp.responseText == "ACCESS_RESTRICTED")
				alert("Тебе запрещён доступ к этой функции");
			else {
				// update billboard text
				if (ubbcFormat)
					showTopicBillboardForm(topic_id, xmlhttp.responseText);
				else {
					var xmlDoc = getXMLDOMFromXmlString(xmlhttp.responseText),
						billboardText = xmlDoc.documentElement.childNodes.item(0).firstChild != null ? xmlDoc.documentElement.childNodes.item(0).firstChild.nodeValue : '',
						billboardAuthorLogin = xmlDoc.documentElement.childNodes.item(1).firstChild != null ? xmlDoc.documentElement.childNodes.item(1).firstChild.nodeValue : '',
						billboardAuthorName = xmlDoc.documentElement.childNodes.item(2).firstChild != null ? xmlDoc.documentElement.childNodes.item(2).firstChild.nodeValue : null;
						billboardAuthorGender = xmlDoc.documentElement.childNodes.item(3).firstChild != null ? xmlDoc.documentElement.childNodes.item(3).firstChild.nodeValue : null;
						
					if (billboardText == '')
						document.getElementById('topicBillboardArea').style.display = 'none';
					
					if (billboardAuthorLogin != '') {
						billboardText += '<p><font size="1"><i>Редактировал'+(billboardAuthorGender=="Female"?"а":"")+' афишу ';
						if (billboardAuthorName != null)
							billboardText += '<a href="index.php?action=viewprofile;user='+billboardAuthorLogin+'"><b>'+billboardAuthorName+'</b></a>';
						else
							billboardText += '<b>'+billboardAuthorLogin+'</b>';
						billboardText += '</i></font></p>';
					}
						
					document.getElementById('topicBillboard').innerHTML = billboardText;
				}
			}
		}
	}
  
	xmlhttp.send();
	var varTimeout = setTimeout( function(){ xmlhttp.abort(); alert("Не удалось выполнить операцию. Попробуй ещё раз."); }, 10000);
}

function editTopicBillboard(topic_id)
{
	var topicBillboard = document.getElementById('topicBillboard'),
		topicBillboardArea = document.getElementById('topicBillboardArea'),
		billboardText;
	
	if (topicBillboardArea.style.display != 'none' && document.getElementById('topicBillboardTextArea') != null) {
		fetchTopicBillboard(topic_id, false);
		return;
	}

	fetchTopicBillboard(topic_id, true);
}

function saveTopicBillboard(topic_id)
{
	var xmlhttp = getXmlHttp();
	var billboardText = document.getElementById('topicBillboardTextArea').value;
	billboardText = encodeURIComponent(billboardText);
	var params = document.getElementById('topicBillboardTextArea') != null ? 'billboardtext='+billboardText : null;
	if (params == null)
	{
		alert("Ошибка! Не удалось получить текст афиши. Перезагрузите страницу, пожалуйста.");
		return;
	}
	xmlhttp.open("POST", "/index.php?action=savetopicbillboard;requesttype=ajax;threadid="+topic_id, true);
  
	//Send the proper header information along with the request
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Content-length", params!=null?params.length:0);
	xmlhttp.setRequestHeader("Connection", "close");
  
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState != 4) return;
    
		if (xmlhttp.status == 200) {
			clearTimeout(varTimeout);
      
			if (xmlhttp.responseText == "ACCESS_RESTRICTED")
				alert("Тебе запрещён доступ к этой функции");
			else if (xmlhttp.responseText == "ERROR")
				alert("Не удалось сохранить новую афишу. Попробуй ещё раз.");
			else
				// update billboard text
				fetchTopicBillboard(topic_id, false);
		}
	}
  
	xmlhttp.send(params);
	var varTimeout = setTimeout( function(){ xmlhttp.abort(); alert("Не удалось выполнить операцию. Попробуй ещё раз."); }, 10000);
}

function getXMLDOMFromXmlString(value)
{
	var xmlDoc;
	if (window.DOMParser)
	{
	  parser=new DOMParser();
	  xmlDoc=parser.parseFromString(value,"text/xml");
	}
	else // Internet Explorer
	{
	  xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
	  xmlDoc.async="false";
	  xmlDoc.loadXML(value); 
	}
	return xmlDoc;
}

function showTopicBillboardForm(topic_id, value)
{
	var topicBillboard = document.getElementById('topicBillboard'),
		topicBillboardArea = document.getElementById('topicBillboardArea'),
		billboardField = document.createElement('textarea'),
		form = document.createElement("form"),
		// parse returned xml document and return XML DOM object
		xmlDoc = getXMLDOMFromXmlString(value),
		billboardText = '';

	if (xmlDoc.documentElement.childNodes.item(0).firstChild != null)
		billboardText = xmlDoc.documentElement.childNodes.item(0).firstChild.nodeValue;
    
	form.setAttribute("method", "POST");	
		
	billboardField.setAttribute('id', 'topicBillboardTextArea');
	billboardField.setAttribute('name', 'topicBillboardTextArea');
	billboardField.style.width = "100%";
	billboardField.style.height = "300px";
	billboardField.appendChild( document.createTextNode(billboardText) );
	
	form.appendChild(billboardField);
	
	var saveBtn = document.createElement('img');
	saveBtn.setAttribute('src', '/YaBBImages/save.png');
	saveBtn.setAttribute('width', '14');
	saveBtn.setAttribute('height', '14');
	saveBtn.setAttribute('border', '0');
	saveBtn.setAttribute('alt', 'сохранить');
	saveBtn.setAttribute('title', 'сохранить');
	saveBtn.onclick = function() {saveTopicBillboard(topic_id);};
	saveBtn.onmouseover = function() {saveBtn.style.cursor='pointer';};
	saveBtn.onmouseout = function() {saveBtn.style.cursor='default';};
	
	var cancelBtn = document.createElement('img');
	cancelBtn.setAttribute('src', '/YaBBImages/cancel.png');
	cancelBtn.setAttribute('width', '14');
	cancelBtn.setAttribute('height', '14');
	cancelBtn.setAttribute('border', '0');
	cancelBtn.setAttribute('alt', 'отменить');
	cancelBtn.setAttribute('title', 'отменить');
	cancelBtn.style.marginLeft = '20px';
	cancelBtn.onclick = function() {editTopicBillboard(topic_id);};
	cancelBtn.onmouseover = function() {cancelBtn.style.cursor='pointer';};
	cancelBtn.onmouseout = function() {cancelBtn.style.cursor='default';};

	topicBillboard.innerHTML = '';
	topicBillboard.appendChild(form);
	topicBillboard.appendChild(saveBtn);
	topicBillboard.appendChild(cancelBtn);
	
	if (topicBillboardArea.style.display == 'none')
		topicBillboardArea.style.display = 'table-row';
}

var oChangeRatingDir = {'down': 'down', 'up': 'up'};

function raiseTopicRating(topicID)
{
	changeTopicRating(topicID, oChangeRatingDir.up);
}

function lowerTopicRating(topicID)
{
	changeTopicRating(topicID, oChangeRatingDir.down);
}

function changeTopicRating(topicID, changeRatingDir)
{
	var xmlhttp = getXmlHttp();
	xmlhttp.open("GET", "/index.php?action=changetopicrating;requesttype=ajax;threadid="+topicID+";dir="+changeRatingDir, true);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState != 4) return;
    
		if (xmlhttp.status == 200) {
			clearTimeout(varTimeout);
			
			if (xmlhttp.responseText == '' || xmlhttp.responseText == 'ERROR')
			{
				alert("Произошла ошибка! Пороверь подключение к интернет и попробуй ещё раз.");
				return;
			}
				
			var xmlDoc = getXMLDOMFromXmlString(xmlhttp.responseText);
			if (xmlDoc == null)
				return;
			
			var positiveRating = xmlDoc.documentElement.childNodes.item(0).firstChild != null ? xmlDoc.documentElement.childNodes.item(0).firstChild.nodeValue : '',
				negativeRating = xmlDoc.documentElement.childNodes.item(1).firstChild != null ? xmlDoc.documentElement.childNodes.item(1).firstChild.nodeValue : '';
			
			document.getElementById('positiveTopicRating'+topicID).innerHTML = positiveRating;
			document.getElementById('negativeTopicRating'+topicID).innerHTML = negativeRating;
		}
	}
  
	xmlhttp.send();
	var varTimeout = setTimeout( function(){ xmlhttp.abort(); }, 30000);
}

function hideLastMsgsNCmnts()
{
	setCookie("hideLastMsgsNCmnts", "1", 90);
	$("#profileTable").css("width", "600px");
	if (Forum.isMobileMode) {
            $('#profileTable>tbody>tr>td:first-child').show();
        }
	document.getElementById('followersColTitle').style.display='none';
	document.getElementById('followersCol').style.display='none';
	document.getElementById('showMsgsNCmntsBtn').style.display='inline';
}

function showLastMsgsNCmnts(username)
{
	var displayMode = 'table-cell';
	setCookie("hideLastMsgsNCmnts", "0", -1);
	$("#profileTable").css("width", "100%");
	if (Forum.isMobileMode) {
            $('#profileTable>tbody>tr>td:first-child').hide();
            displayMode = 'block';
        }
        document.getElementById('followersColTitle').style.display = displayMode;
	document.getElementById('followersCol').innerHTML = '';
	document.getElementById('followersCol').style.display = displayMode;
	showUserLatestMessagesAndComments(username);
	document.getElementById('showMsgsNCmntsBtn').style.display='none';
	latestMessagesAndCommentsLevel = 1;
}

function highlightComment(obj)
{
	obj.style.cursor = 'pointer';
	obj.className='windowbg2';
}

function unhighlightComment(obj)
{
	obj.className='windowbg';
}

function showUserLatestMessagesAndComments($username)
{
    if (Forum.ready) {
	var xmlhttp = getXmlHttp(),
	    timerStart = new Date();
	jQuery("input", $("#followersColTitle")).attr("checked", "checked");	// set messages and comments filters off
	document.getElementById('followersCol').innerHTML = '<div align="center"><img src="/YaBBImages/loading2.gif" width="48" height="48" style="margin: 50px;" alt="Загрузка" /><br /><span style="font-family: Verdana, Courier New, sans-serif; size: 16pt; letter-spacing: 3px;">идёт загрузка...</span></div>';
	xmlhttp.open("GET", "/index.php?action=latestmessagesandcomments;requesttype=ajax;user="+$username, true);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState != 4) return;
    
		if (xmlhttp.status == 200) {
			clearTimeout(varTimeout);
			
			if (xmlhttp.responseText == '' || xmlhttp.responseText == 'ERROR')
			{
				document.getElementById('followersCol').innerHTML="Произошла ошибка! Пороверь подключение к интернет и попробуй ещё раз. Если ошибка повторяется, обратись, пожалуйста, к админу <b>dig7er</b>.";
				return;
			}
			
			document.getElementById('followersCol').innerHTML=xmlhttp.responseText;
			var showMessages = ($("#followersColTitle").find("input:eq(0):checked").length > 0),
				showComments = ($("#followersColTitle").find("input:eq(1):checked").length > 0);
			if ((showMessages && $("#followersCol > table.message:hidden").length > 0) || (showComments && $("#followersCol > table.comment:hidden").length > 0))
				document.getElementById('followersCol').innerHTML+='<div align="center" style="margin: 20px;"><input type="button" value="Показать предыдущие" onclick="showNextMessagesAndComments()" /></div>';
			//alert(new Date() - timerStart);
		}
	}
  
	xmlhttp.send();
	var varTimeout = setTimeout( function(){ document.getElementById('followersCol').innerHTML='Поиск последних сообщений и комментариев занимает слишком много времени. Вероятно, сервер перегружен и стоит попробоавть позже.'; }, 30000);
    } else {
        // forum not ready, execute on ready
        document.addEventListener('forumready', function(e){
            showUserLatestMessagesAndComments($username);
        }, false);
    }
}

function showNextMessagesAndComments()
{
	var showMessages = ($("#followersColTitle").find("input:eq(0):checked").length > 0),
		showComments = ($("#followersColTitle").find("input:eq(1):checked").length > 0),
		n,
		i,
		tables;
	if (showMessages && showComments)
		tables = $("#followersCol > table:hidden").toArray();
	else if (showMessages)
		tables = $("#followersCol > table.message:hidden").toArray();
	else if (showComments)
		tables = $("#followersCol > table.comment:hidden").toArray();
	
	if (tables == null)
		return;

	for (i=0; i < 15 && i < tables.length; i++)
			$(tables[i]).show("slide", { direction: 'up' }, 500);
	
	// show or delete the more button
	if (tables.length - 15 <= 0)
		$("#followersCol > div:last").remove();
		
	latestMessagesAndCommentsLevel++;
}

function toggleLatestMessages(el)
{
	if (el.checked) {
		var tables = $(".message").slice(0, latestMessagesAndCommentsLevel*15).toArray();
		$(tables).show("slide", { direction: "up" }, 500);
	} else
		$(".message:visible").hide("slide", { direction: "up" }, 500);
}

function toggleLatestComments(el)
{
	if (el.checked) {
		var tables = $(".comment").slice(0, latestMessagesAndCommentsLevel*15).toArray();
		$(tables).show("slide", { direction: "up" }, 500);
	} else
		$(".comment:visible").hide("slide", { direction: "up" }, 500);
}

function followRequest(userID)
{
	// make ajax query
	$.ajax({
		method: "get",
		url: "index.php",
		data: "action=followrequest;requesttype=ajax;uid="+userID,
		success: function(response) {
			if (response == "" || response == "ERROR")
				alert("Произошла ощибка! Пожалуйста, проверь подключение и попробуй ещё раз.");
			else
				$("#followButton").parent().html(response);
		}
	});
}

function setCookie(c_name,value,expiredays)
{
	var exdate=new Date();
	exdate.setDate(exdate.getDate()+expiredays);
	document.cookie=c_name+ "=" +escape(value)+
	((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}

function showHideRecentCommentsTable()
{
  if (document.getElementById('recentcommentstable').style.display=='none')
  {
    document.getElementById('recentcommentstable').style.display='block';
    document.getElementById('recentCommentsTableInfo').style.display='none';
    setCookie('recentCommentsTable','show',7)
  } else {
    document.getElementById('recentcommentstable').style.display='none';
    document.getElementById('recentCommentsTableInfo').style.display='block';
    setCookie('recentCommentsTable','hide',7)
  }
}

/**
 * A static class with the helpful forum functions.
 */
Forum.Utils = {
	/**
	 * Plays an mp3 file on the background.
	 * @param {fileURL} URL of the mp3 file.
	 */
	playMP3OnBackground: function(fileURL)
	{
		$('#instantMessages').flash(
			{ src: '/includes/singlemp3player.swf?autoStart=true', height: 1, width: 1, wmode: 'transparent' },
			{ version: 7 },
			function(htmlOptions) {
				htmlOptions.flashvars.file = fileURL;
				$(this).after($.fn.flash.transform(htmlOptions));						
			}
		);
	},
	
	/**
	 * Creates a dark page overlay in a separate div width id 'overlay'.
	 */
	createPageOverlay: function()
	{
		if ($('#overlay').length == 0)
			$("<div/>").attr('id','overlay').
				css('background-color', '#000000').
				css('position','absolute').
				css('top', 0).
				css('left', 0).
				css('margin', 0).
				css('padding', 0).
				appendTo("body").
				css('z-index', 1000).
				width('100%').
				height($(document).height()).
				fadeTo('slow', 0.67).
                                on('click', function (){
                                    Forum.Utils.PopupWindow.close();
                                });
	},
	
	/**
	 * Removes a page overlay.
	 */
	removePageOverlay: function()
	{
		if ($('#overlay').length > 0)
			$('#overlay').off('click').remove();
	}
};

/**
 * Position is a dynamic class used to define position of any object on the screen.
 */
Forum.Utils.Position = Forum.Class({
	top: null,
	left: null,
	initialize: function(left, top)
	{
		this.top = top;
		this.left = left;
	},
	setLeft: function(left)
	{
		this.left = left;
	},
	setTop: function(top)
	{
		this.top = top;
	}
});

/**
 * A dynamic class for creating a popup window.
 */
Forum.Utils.PopupWindow = Forum.Class({		
	self: this,
	instance: null,
	
	/**
	 * Constructor for PopupWindow.
	 * @param {topPosition} sets the top position of the popup window in pixels.
	 */
	initialize: function(topPosition)
	{
		if (topPosition == null)
			topPosition = 50;
		var width = parseInt($(document).width()*0.9);
		var leftPosition = parseInt(($(document).width() - width)/2);
		
		this.instance = $('<div/>').
							addClass(Forum.Utils.PopupWindow.CSS_CLASS_NAME + " windowbg2").
							css('position', 'absolute').
							css('top', topPosition).
							css("border", Forum.Utils.PopupWindow.BORDER_STYLE).
							css("padding", Forum.Utils.PopupWindow.PADDING_STYLE).
							css("z-index", "1001").
							width(width).
							css('left', leftPosition).
                                                        css('margin', 0).
                                                        css('box-sizing', 'border-box').
							mousedown(Forum.Utils.PopupWindow.capture).
							append($('<div/>').
										css('text-align', 'right').
										append($('<img/>').
											attr('src', Forum.Utils.PopupWindow.IMAGES.CLOSE).
											click(function() {Forum.Utils.PopupWindow.close(this)}).
											css('cursor', 'pointer').
											css('margin', 3).
											attr('alt', "Закрыть").
											attr('title', "Закрыть")
										)
							).
							append($('<div/>').
										attr('id', 'messageContent').
										//mousedown(function(event) { return false; }).
										css('padding', Forum.Utils.PopupWindow.PADDING_STYLE)
							);
	},
	
	/**
	 * Sets the given content to the popup window.
	 * @param {html} an HTML string.
	 */
	setContent: function(html)
	{
		$('#messageContent').html(html);
                this.align();
	},
		
	/**
	 * Displays the popup window.
	 */
	open: function() {
		if (Forum.Utils.PopupWindow.numOpenedPopupWindows == 0)
		{
			Forum.Utils.createPageOverlay();
			this.instance.appendTo($('body'));
			$(this.instance).focus();
			Forum.Utils.PopupWindow.numOpenedPopupWindows++;
		} else
			Forum.Utils.PopupWindow.queue.push(this);
	},
	
	align: function() {
	    var popupWidth = $(this.instance).width();
	    var popupHeight = $(this.instance).outerHeight();
	    var winWidth = $(window).width();
	    var winHeight = $(window).innerHeight();
	    var winScroll = $(window).scrollTop();
            var calcHeight = winScroll + popupHeight;
            var documentHeight = $(document).height();
	    if (popupHeight < winHeight) {
	        // Center vertically
	        $(this.instance).css({
	                'top': winScroll + (winHeight - popupHeight)/2
	        });
	    }
	    else if (popupHeight > winHeight) {
	        // Popup window height is larger than window height
                $(this.instance).css('top', winScroll + 10);
	    }
	    if (calcHeight > documentHeight) {
                // fix overlay for very big popup height
                $('#overlay').css('height', calcHeight);
            }
	}
});

/**
 * A jQuery div-object with currently selected popup window.
 */
Forum.Utils.PopupWindow.captured = null;

/**
 * Global mouse position when the mouse is over the captured popup window.
 */
Forum.Utils.PopupWindow.capturedPosition = null;

/**
 * Mouse-down event handler for popup windows.
 * It captures the selected popup window and saves the mouse position.
 * @param {event} event object with mouse position information.
 * @returns false in order to disable the selection of text in the underlying layers.
 */
Forum.Utils.PopupWindow.capture = function(event)
{
	Forum.Utils.PopupWindow.captured = this;
	Forum.Utils.PopupWindow.capturedPosition = new Forum.Utils.Position(event.pageX, event.pageY);
	//return false;
}

/**
 * A static final string in PopupWindow class used to set the window border style.
 */
Forum.Utils.PopupWindow.BORDER_STYLE = "1px solid black";

/**
 * A static final string in PopupWindow class used to set the window padding.
 */
Forum.Utils.PopupWindow.PADDING_STYLE = "3px";

/**
 * A static final map where all the window images are saved.
 */
Forum.Utils.PopupWindow.IMAGES = {
	CLOSE: "/YaBBImages/closepopup.gif"
};

/**
 * A static PopupWindow function. Shows a popup window with a forum message.
 * @param {id} id of the message to show.
 * @param {posTop} top position for a popup window.
 */
Forum.Utils.PopupWindow.showMessage = function(id, posTop)
{
	var popupWindow = new Forum.Utils.PopupWindow(posTop);
	var html = Forum.Data.getMessageHTML(id, popupWindow);
	popupWindow.open();
	popupWindow.setContent(html);
};

/**
 * A static function close() for the PopupWindow class.
 * It removes the popup window from the screen.
 * @param {closeImgObj} a popup window close image DOM reference. The image should be a direct child of the popup window div.
 */
Forum.Utils.PopupWindow.close = function(closeImgObj) {
	var popupWindow = (closeImgObj != null) ?
						$(closeImgObj).parent().parent("."+Forum.Utils.PopupWindow.CSS_CLASS_NAME) :
						$("."+Forum.Utils.PopupWindow.CSS_CLASS_NAME+":first");

	Forum.Utils.PopupWindow.numOpenedPopupWindows -= popupWindow.length;
	popupWindow.remove();
	
	if (Forum.Utils.PopupWindow.numOpenedPopupWindows == 0)
		Forum.Utils.removePageOverlay();
		
	if (Forum.Utils.PopupWindow.queue.length > 0)
		Forum.Utils.PopupWindow.queue.shift().open();
};

/**
 * A final static string defining the css class name of the popup window divs.
 */
Forum.Utils.PopupWindow.CSS_CLASS_NAME = 'popupWindows';

/**
 * A static array keeping a queue of popup windows. Only one popup window can be shown at a time.
 */
Forum.Utils.PopupWindow.queue = new Array();

/**
 * A static counter keeping the number of opened popup windows.
 */
Forum.Utils.PopupWindow.numOpenedPopupWindows = 0;

/**
 * A static function showImage() function displays popup window
 * containing full version of selected picture or embeds videoplayer.
 * @param {event} selected picture
 */
Forum.Utils.showImage = function(event) {
    event.stopPropagation();
    var image = $(event.currentTarget);
    var showPopup = true;
    var imgTypes = ['jpg', 'jpeg', 'jpg:large', 'jpg:small', 'png', 'gif', 'svg', 'svgz', 'webp', 'bmp'];
    if (image.is('img')) {
        if (image.width() > 100 || image.height() > 100) {
            var fullImg = $('<img>')
                .css({'max-width': '100%', 'max-height': 'none'})
                .on('load', function(){
                    imgPopup.align();
                });
            var imgSource = image.parent('a').attr('href');
            var doFixSrc = true;
            var parentIsURL = false;
            // If the picture is inside an URL
            if (typeof imgSource === 'string') {
                imgSource = Forum.Utils.URL(imgSource);
                parentIsURL = true;
                if ($.inArray(imgSource.ext, imgTypes) !== -1) {
                    // Set popup image src
                    fullImg.attr('src', imgSource.href);
                    doFixSrc = false;
                }
            }
            
            if (doFixSrc) {
                var src = Forum.Utils.URL(image.attr('src'));
                if (src.hostname.indexOf('radikal.ru') >= 0) {
                    // Replacing thumbnail with full version
                    fullImg.attr('src', src.href.replace('x.jpg', '.jpg').replace('t.jpg', '.jpg'));
                }
                else if (src.hostname.indexOf('funkyimg.com') >= 0) {
                    // Replacing thumbnail with full version
                    if (src.pathname.indexOf('/t2/') >= 0) {
                        fullImg.attr('src', src.href.replace('/t2/', '/u2/'));
                    }
                    else if (src.pathname.indexOf('/p/') >= 0) {
                        fullImg.attr('src', src.href.replace('/p/', '/i/'));
                    }
                    else {
                        fullImg.attr('src', src.href);
                    }
                }
                else if (src.hostname.indexOf('fastpic.ru') >= 0) {
                    // Replacing thumbnail with full version
                    var full_ver_href = src.href.replace('/thumb/', '/big/').replace('.jpeg', '.jpg').concat('?noht=1');
                    fullImg.attr('src', full_ver_href);
                }
                else if (src.hostname.indexOf('photosp.ru') >= 0) {
                    // Replacing thumbnail with full version
                    fullImg.attr('src', src.href.replace('.th.', '.'));
                }
                else {
                    fullImg.attr('src', src.href);
                }
            }
            
            var newSrc = fullImg.attr('src');

            var content = $('<div/>').append(fullImg);
            content.append('<br><a href="' + newSrc + '" target="_blank">Прямая ссылка</a>');
            if (parentIsURL) {
                content.append(', <a href="' + imgSource.href + '" target="_blank">источник</a>');
            }
        } else {
            showPopup = false;
        }
    } // if target is image
    else if (image.is('a')) {
        var src = Forum.Utils.URL(image.attr('href'));
        var lsrc = Forum.Utils.URL(image.attr('href').toLowerCase());
        var content;
        if (lsrc.hostname == 'youtu.be' || lsrc.hostname == 'm.youtube.com' || lsrc.hostname == 'www.youtube.com') {
            var YtEmbedSrc = '//www.youtube.com/embed/';
            var YtParams = image.attr('yt-params') || '';
            if (lsrc.hostname == 'youtu.be') {
                var src = Forum.Utils.URL(image.attr('href') + YtParams);
                var yID = src.pathname.substring(1);
            }
            else if (lsrc.hostname.indexOf('.youtube.com') != -1)
                var yID = src.args.v || '';
            else
                yID = '';
            var YtPL = src.args.list || '';
            
            if (YtPL == '' && yID == '') {
                // It's neither video, nor playlist
                return;
            }
            
            if (YtPL == '') {
                YtEmbedSrc += yID + '?autoplay=1&rel=0';
            } else {
                // It's a playlist
                YtEmbedSrc += '?listType=playlist&list=' + YtPL + '&autoplay=1&rel=0';
            }
            if (typeof src.args.t !== 'undefined') {
                // we have start time parameter
                YtEmbedSrc += '&start=' + src.args.t;
            }
            var parent = image.parent('div');
            if (parent.hasClass('youtube-embed')) {
                // It's a Youtube thumbnail, replace it with player
                showPopup = false;
                event.preventDefault();
                var YtEmbed = '<div class="yt-dummy"></div><iframe src="' + YtEmbedSrc + '" allowfullscreen><a href="' + src.href + '" target="_blank">' + src.href + '</a></iframe>';
                image.off('mouseover.yt-title touchstart.yt-title');
                parent.children('div.yt-title').hide();
                parent.html(YtEmbed);
                return;
            } else {
                content = $('<div/>').append('<iframe src="' + YtEmbedSrc + '" style="width: 720px; height: 405px; max-width: 100%;" allowfullscreen><a href="' + src.href + '" target="_blank">' + src.href + '</a></iframe>');
            }
        } // if youtube
        else if (lsrc.hostname == 'vimeo.com' || lsrc.hostname == 'player.vimeo.com') {
            var videoID = parseInt(src.basename);
            if (!isNaN(videoID)) {
                showPopup = true;
                var vimSrc = 'https://player.vimeo.com/video/' + videoID + '?color=ffffff';
                content = $('<div/>').append('<iframe src="' + vimSrc + '" style="width: 720px; height: 405px; max-width: 100%;" allowfullscreen><a href="' + src.href + '" target="_blank">' + src.href + '</a></iframe>');
            } else {
                showPopup = false;
            }
        } // if vimeo.com
        else if (lsrc.hostname == 'www.facebook.com') {
            var _path = src.pathname.split('/');
            if (_path.length > 3 && _path[2] == 'videos') {
                showPopup = true;
                var videoID = _path[3];
                var videoSrc = 'https://www.facebook.com/video/embed?video_id=' + videoID;
                content = $('<div/>').append('<iframe src="' + videoSrc + '" style="width: 720px; height: 405px; max-width: 100%;" allowfullscreen><a href="' + src.href + '" target="_blank">' + src.href + '</a></iframe>');
            } else {
                showPopup = false;
            }
        } // facebook.com
        else if (lsrc.hostname == 'soundcloud.com') {
            showPopup = false;
            var parent = image.parent('div');
            if (parent.hasClass('soundcloud-embed')) {
                // It's a embedded player
                event.preventDefault();
                var scMeta = parent.data('soundcloud');
                if (typeof(scMeta) === 'undefined') {
                    Forum.Utils.getSoundcloudMeta(event, src.href, true);
                    return false;
                } else {
                    parent.html(scMeta.html);
                    return;
                }
            } else {
                // It's a link
                var scTrackRegex = /^https?:\/\/soundcloud\.com\/[^\]\/\s]+\/[^\]\/\s]+$/;
                var scAlbumRegex = /^https?:\/\/soundcloud\.com\/[^\]\/\s]+\/sets\/[^\]\/\s]+$/;
                // check if link is a track or an album
                if(scTrackRegex.test(src.href) || scAlbumRegex.test(src.href)) {
                    event.preventDefault();
                    Forum.Utils.getSoundcloudMeta(event, src.href, true);
                }
                return;
            }
        } // if SoundCloud
        else if (imgTypes.indexOf(src.ext) > -1) {
            // target link is an image, show in popup
            showPopup = true;
            var fullImg = $('<img>')
                .css({'max-width': '100%', 'max-height': 'none'})
                .on('load', function(){
                    imgPopup.align();
                })
                .attr('src', src.href);
            content = $('<div/>').append(fullImg);
            content.append('<br><a href="' + src.href + '" target="_blank">Прямая ссылка</a>');
        } // if target link is an image
        else {
            showPopup = false;
        }
    } // if target is a link
    else {
        showPopup = false;
    }
    
    if (showPopup){
        event.preventDefault();
        var imgPopup = new Forum.Utils.PopupWindow($(document).scrollTop() + 10);
        imgPopup.open();
        content.css({'text-align': 'center', 'cursor': 'default'}).off('click');
        imgPopup.setContent(content);
    }
}; // Forum.Utils.showImage()

/**
 * retrieve soundcloud meta info
 */
Forum.Utils.getSoundcloudMeta = function(event, url, shouldEmbed) {
    var target = $(event.target).parent('div.soundcloud-embed');
    if (typeof(url) === 'undefined') {
        var url = target.children('a.soundcloud-embed-lnk').attr('href');
    }
    var maxheight = 166;
    if (url.indexOf('/sets/') > 0) {
        maxheight = 450;
    }
    var scMeta = target.data('soundcloud');
    if (typeof(scMeta) === 'undefined') {
        $.ajax({
            url: 'https://soundcloud.com/oembed',
            data: {
                format: 'json',
                url: url,
                show_comments: false,
                auto_play: true,
                visual: false,
                maxheight: maxheight
            },
            dataType: 'json',
            timeout: 7000,
            success: function(data) {
                data.html = data.html.replace('visual=true', 'vusual=false').replace('show_teaser=true', 'show_teaser=false');
                if (target.is('div')) {
                    target.data('soundcloud', data);
                    target.find('div.soundcloud-title').text(data.title);
                    if (typeof(shouldEmbed) !== 'undefined') {
                        target.children('a.soundcloud-embed-lnk').click();
                    }
                } else {
                    // it's a link, not a player. Show popup with a player
                    var content = $('<div/>').append(data.html);
                    var scPopup = new Forum.Utils.PopupWindow($(document).scrollTop() + 10);
                    scPopup.open();
                    content.css({'text-align': 'center', 'cursor': 'default'}).off('click');
                    scPopup.setContent(content);
                }
            }, // ajax succes
            error: function(data) {
                var errmsg = 'Это аудио не доступно.';
                if (target.is('div')) {
                    // embedded player was clicked
                    target.find('div.soundcloud-title').text(errmsg);
                } else {
                    // A link in comments was clicked, show in popup
                    var content = $('<div/>').text(errmsg).
                        css({'text-align': 'center', 'cursor': 'default'});
                    var scPopup = new Forum.Utils.PopupWindow($(document).scrollTop() + 10);
                    scPopup.open();
                    scPopup.setContent(content);
                }
            } // ajax error
        }); // ajax
    } // if no SC meta
}; // getSoundcloudMeta()

/**
 * Youtube thumbnail mouseover handler
 * displaying video title.
 */
Forum.Utils.showYtTitle = function(event) {
    var YtLnk = $(event.target);
    var YtContainer = YtLnk.parent('div.youtube-embed');
    if (YtContainer.hasClass('youtube-playlist')) {
        return;
    }
    var title = YtContainer.children('div.yt-title');
    if (title.length == 0) {
        // Get title from the server if not exists
        var YtSrc = Forum.Utils.URL(YtLnk.attr('href'));
        var yID = YtSrc.pathname.substring(1);
        $.ajax({
            url: 'index.php',
            data: {
                action: 'api',
                requesttype: 'ajax',
                query: 'get_youtube_video_data',
                v: yID
            },
            dataType: 'json',
            timeout: 5000,
            success: function (data) {
                if (typeof data.title == 'string'){
                    var tBlock = '<div class="yt-title">' + data.title + '<div>';
                    YtContainer.append(tBlock);
                    var title = YtContainer.children('div.yt-title');
                    title.fadeIn('slow');
                    YtContainer.on('mouseout', function(){title.fadeOut('slow')});
                }
            } // ajax success
        }); // ajax
    } else {
        // Title already exists, just show it
        title.fadeIn('slow');
    }
}; // Forum.Utils.showYtTitle()

/**
 * Click event handler replacing placeholder with iframe
 */
Forum.Utils.mediaEmbed = function(event){
    event.preventDefault();
    var target = $(event.target);
    var container = target.parent('div.media-embed').empty();
    var frame = $('<iframe>')
        .attr({
            src: target.attr('href').replace('http://', '//'),
            allow: 'fullscreen'
        })
        .appendTo(container);
}; // Forum.Utils.mediaEmbed()

/**
 * Static class with utilities for working with forum spoilers.
 * Spoilers are created by tags [hidden][/hidden]
 */
Forum.Utils.Spoiler = {
	IMAGES: {
		EXPAND: 'expand.jpg',
		COLLAPSE: 'collapse.jpg'
	},

	showHide: function(spoiler)
	{
		var textArea = spoiler.parentNode.getElementsByTagName('div')[1];
		if (textArea.style.display=='block')
			textArea.style.display='none';
		  
		else if (textArea.style.display=='none')
			textArea.style.display='block';
		
		this.changeImg(spoiler);
	},
	  
	changeImg: function(spoiler)
	{
		var img = spoiler.getElementsByTagName('a')[0].firstChild;
		var parts = img.src.split("/");
		var pic = "";
		parts[parts.length-1] = (parts[parts.length-1] == this.IMAGES.EXPAND) ? this.IMAGES.COLLAPSE : this.IMAGES.EXPAND;
		for (var j=0; j<parts.length; j++)
			pic += (j==0) ? parts[j] : "/" + parts[j];
		img.src = pic;
	}
};

/**
 * Static class for message comments form.
 */
Forum.Utils.MessageCommentsForm = {
	/**
	 * Shows or hides the text box and a submit button for a new message comment.
	 */
	showHide: function(msgID)
	{
		if (!$('#commentBoxLength'+msgID).length){
		    $('div.commentBox > #commentBox'+msgID).parent().after('<div><input type="text"   id="commentBoxLength'+msgID+'" maxlength="3" value="256" readonly></div>');
		}
		var lockBtn = $('img#lock'+msgID);
		if (lockBtn.length > 0 && lockBtn.attr('src').indexOf('lock_closed.png') > 0) {
                    $('#commentBoxLength'+msgID).hide();
                }
		$('#commentForm'+msgID+',#comments'+msgID+'.comments-collapsed').toggle();
		if ($('#commentForm'+msgID+':visible').length > 0){
			$('#commentBox'+msgID).focus();
		        // Insert nicks in input field on clicks
			$('#comments'+msgID+' .comment-content > a[href*="viewprofile;user="]').bind("click", function(e){
			  var CommentBox = $('#commentBox'+msgID);
			  e.preventDefault();
			  var commentBoxVal = CommentBox.val();
                          var Nick = '[b]' + $(this).text() + '[/b]';
			  if (commentBoxVal.slice(-1) != ' ' && commentBoxVal != '') {
                              Nick = ' ' + Nick;
                          }
                          var MaxLength = parseInt(CommentBox.attr('maxlength'));
			  if ((MaxLength - commentBoxVal.length) > Nick.length)
			    replaceText(Nick, document.getElementById('commentBox'+msgID));
			    CommentBox.trigger("change");
			});
		} else {
		  $('#comments'+msgID+' .comment-content > a[href*="viewprofile;user="]').unbind("click");
		}
	},
	
	/**
	 * Adds a subscription to the new message comments to the member.
	 * @param {messageID} ID of the message
	 * @param {subscriptionTagReference} an HTML A tag DOM-object
	 * @returns false to not to execute a synchronous hyperlink call.
	 */
	subscribe: function(messageID, subscriptionTagReference)
	{
		$("body").css('cursor', 'wait');
		$.ajax({
			url: 'index.php',
			data:	{
					action: "subscribemessagecomments",
					messageID: messageID,
					requesttype: 'ajax'
					},
			dataType: "html",
			success: function(data) {
				$(subscriptionTagReference).replaceWith(data);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.error("ERROR ("+textStatus+"): " + errorThrown);
			},
			complete: function() {
				$("body").css('cursor', 'default');
			}
		});
		return false;
	},
	
	/**
	 * Deletes a member's subscription to the new message comments.
	 * @param {messageID} ID of the message
	 * @param {subscriptionTagReference} an HTML A tag DOM-object
	 * @returns false to not to execute a synchronous hyperlink call.
	 */
	unsubscribe: function(messageID, subscriptionTagReference)
	{
		$("body").css('cursor', 'wait');
		$.ajax({
			url: 'index.php',
			data:	{
					action: "unsubscribemessagecomments",
					messageID: messageID,
					requesttype: 'ajax'
					},
			dataType: "html",
			success: function(data) {
				$(subscriptionTagReference).replaceWith(data);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.error("ERROR ("+textStatus+"): " + errorThrown);
			},
			complete: function() {
				$("body").css('cursor', 'default');
			}
		});
		return false;
	},
	
	/**
	 * Checks the user's message comments subscription asynchronously and replaces the subscription link.
	 * @param {messageID} ID of the message.
	 * @param {subscriptionLink} DOM object of the subscription link.
	 */
	updateSubscription: function(messageID, subscriptionLink)
	{
		if (messageID == null || subscriptionLink == null)
			return;

		$.ajax({
			url: 'index.php',
			data:	{
					action: "updatecommentssubscription",
					messageID: messageID,
					requesttype: 'ajax'
					},
			dataType: "html",
			success: function(data) {
				$(subscriptionLink).replaceWith(data);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.error("ERROR ("+textStatus+"): " + errorThrown);
			}
		});
	},
	
	/**
	 * Modify comment
	 */
	modify: function(postID, commentNr, commentID, boardID, e){
	    e.preventDefault();
	    var commentBlock = '#comment' + postID + '-' + commentNr;
	    var preservedComment = $(commentBlock).html();
            var commentAuthor = $(commentBlock + '>div>a').first().data('userid').toString();
            var reasonField = '';
            if (Forum.sessioninfo.userid != commentAuthor){
                reasonField = '<input type="text" name="mdfrzn" required maxlength="128" size="50" placeholder="Причина редактирования">';
            }
	    function checkResult(result, callback){
	        switch(result){
		    case 'ERROR':
			alert('Произошла ошибка.');
			break;
		    case 'ACCESS_DENIED':
		        alert('Этот комментарий изменять нельзя.');
			break;
		    case 'EMPTY_COMMENT':
		        alert('Пустой комментарий.');
			break;
	            default:
			callback(result);
		}
	    }
	    $.ajax('/index.php',{
	        type: 'GET',
	        data: {
	            action: 'commentmodify',
	            requesttype: 'ajax',
	            postID: postID,
	            commentNr: commentNr,
	            commentID: commentID,
	            board: boardID},
	        success: function(data){
		    checkResult(data, function(r){
		        $(commentBlock).html('<form action="/" method="POST" class="commentModify">' +
		          '<textarea name="comment" maxlength="' + r.length + '" required>' + r + '</textarea>' +
                          reasonField +
		          '<input type="hidden" name="postID" value="' + postID + '">' +
		          '<input type="hidden" name="commentNr" value="' + commentNr + '">' + 
		          '<input type="hidden" name="commentID" value="' + commentID + '">' +
		          '<input type="hidden" name="board" value="' + boardID + '">' +
		          '<input type="hidden" name="action" value="commentmodify"><input type="hidden" name="requesttype" value="ajax"> <br>' +
		          '<input type="submit" value="Отправить"> <input type="button" value="Отмена" class="commentModifyEscape">' +
		          '</form>'
		        );
			$(commentBlock+'>form.commentModify>textarea').focus();
		    });
		    $(commentBlock+'>form.commentModify').on('submit', function(e){
		        e.preventDefault();
			var submitBtn = $(this).children('input[type="submit"]');
                        submitBtn.prop('disabled', true);
                        var formData = $(this).serialize();
			$.ajax('/index.php', {
			    type: 'POST',
			    data: formData,
			    success: function (result){
	                        submitBtn.prop('disabled', false);
                                checkResult(result, function(r){
			            $(commentBlock+'>form.commentModify').off();
				    $('#comments'+postID).html(r);
				    document.location.hash = 'comment' + postID + '-' + commentNr;
				});
			},
	                    error: function(){alert('Произошла обишка.\nПовторите попытку позже.');}
			});
		    });
		    $(commentBlock+'>form.commentModify>input.commentModifyEscape').on('click', function(){
		        $(commentBlock+'>form.commentModify').off();
			$(commentBlock).html(preservedComment);
		    });
		}
	    });
	}
}

/**
 * Preserve badGuy label and restore it if user clears cookies.
 */
Forum.Utils.checkBadGuy = function(){
  try{
    if (typeof(localStorage.getItem) == "function"){
      var d = new Date();
      var badGuyPreserved = localStorage.getItem('badGuy');
      var badGuy = getCookie('badGuy');
      if (badGuyPreserved){
        if (!badGuy){
          var days = Math.round((parseInt(badGuyPreserved) - d.getTime())/(24*60*60*1000));
          if (days > 0){
            setCookie('badGuy', 'TRUE', days);
          } else {
            localStorage.removeItem('badGuy');
          }
        } else {
          if (badGuy == 'deleted'){
            localStorage.removeItem('badGuy');
          }
        }
      } else {
        if (badGuy == 'TRUE'){
          localStorage.setItem('badGuy', d.getTime() + (14*24*60*60*1000));
        }
      }
    } // end of check localStorage available
  }catch(e){}
}

/**
 * Browser fingerprinting util.
 * Based on https://github.com/Valve/fingerprintjs2
 */
Forum.Utils.fingerprint = function(){
    var tfp = null;
    var bfp = null;
    var ref = null;
    var reft = null;
    var inputForm = $('form[name="postmodify"], form[name="creator"]');
    if (inputForm.length > 0) {
        tfp = Forum.storage.get('tfp', 'local');
        if (tfp == null) {
            tfp = new Date().getTime().toString(36);
            Forum.storage.set('tfp', tfp, 'local');
        } // if !tfp
        $('<input/>', {
            type: 'hidden',
            name: 'tfp',
            value: tfp
        }).appendTo(inputForm);
        
        bfp = Forum.storage.get('bfp', 'session');
        ref = Forum.storage.get('referer', 'local');
        reft = Forum.storage.get('reftarget', 'local');

        if (bfp == null) {
            var fpjs = document.createElement('script');
            fpjs.setAttribute('src', '/includes/fingerprint2.min.js');
            fpjs.onload = function(){
                if (typeof Fingerprint2 == 'function') {
                    new Fingerprint2({excludeUserAgent: true}).get(function(result){
                        bfp = result;
                        Forum.storage.set('bfp', bfp, 'session');
                        $('input[name="bfp"]').val(bfp);
                    });
                }
            }; // script onload
            document.getElementsByTagName('body')[0].appendChild(fpjs);
        } // if !bfp
        $('<input/>', {
            type: 'hidden',
            name: 'bfp',
            value: bfp
        }).appendTo(inputForm);
        if (ref != null && ref != 'none') {
            $('<input/>', {
                type: 'hidden',
                name: 'guestcamefrom',
                value: ref
            }).appendTo(inputForm);
        } // if referer available
        if (reft != null) {
            $('<input/>', {
                type: 'hidden',
                name: 'reftarget',
                value: reft
            }).appendTo(inputForm);
        } // if referer target available
    } // if form available
}; // Forum.Utils.fingerprint()

/**
 * Query category for recent messages and show result in popup
 */
Forum.Utils.recentCatMessages = function(event) {
    event.preventDefault();
    var targetCat = $(event.currentTarget);
    var catID = targetCat.prop('name');
    //return
    // Open popup window with search result
    var pw = new Forum.Utils.PopupWindow($(document).scrollTop() + 50);
    var pw_html = '<div class="fp-search-container"><div class="fp-search-wait"></div></div>';
    pw.open();
    pw.setContent(pw_html);
    $.ajax('/index.php', {
        type: 'GET',
        timeout: 30000,
        dataType: 'html',
        data: {
            requesttype: 'ajax',
            action: 'api',
            query: 'recent_category_messages',
            cat: catID
        },
        success: function(data){
            $('.popupWindows > #messageContent').css({'max-height': $(window).height() - 124, 'overflow-y': 'auto'});
            $('.fp-search-container').html(data);
            pw.align();
        },
        error: function(){
            $('.fp-search-container').text('Request timeout.');
        }
    });
} // Forum.Utils.recentCatMessages()

/**
 * Highlight selected comment
 */

Forum.Utils.highlightComment = function(){
  $(".highlight").toggleClass("highlight");
  try{
//     $(window).scrollTop($(document.location.hash).offset().top-200);
    $('html, body').animate({
      scrollTop: $(document.location.hash).offset().top-200
    }, 500);
  }catch(e){
    window.scrollBy(0,-200);
  }
  $(document.location.hash).toggleClass("highlight");
}

/**
 * Save session config
 */
Forum.Utils.saveConfig = function(e){
  e.preventDefault();
  $('.session-config-page .response_block').hide();
  var formData;
  if (typeof(btoa) === 'function'){
    formData = btoa(JSON.stringify($('.session-config-page form').serializeArray()));
    setCookie('cfg', formData, 90);
    $('.session-config-page #config_saved').show('slow');
  } else {
    $('.session-config-page #sending_data').show('slow');
    formData = $('.session-config-page form').serialize();
    $.ajax('/index.php?action=sessionconf', {
      type: 'POST',
      data: formData,
      timeout: 7000,
      success: function(response){
        $('.session-config-page .response_block').hide();
        if(response == 'OK'){
          $('.session-config-page #config_saved').show('slow');
        }
        else {
          $('.session-config-page #error_other').html(response).show('slow');
        }
      },
      error: function(){
        $('.session-config-page .response_block').hide();
        $('.session-config-page #error_occured').show('slow');
      }
    });
  }
}

/**
 * Delete message button handler
 */
Forum.Utils.deleteMessage = function(e){
    e.preventDefault();
    if (! Forum.ready) {
        alert("Дождитесь полной загрузки страницы и повторите действие снова.");
    } else {
        var msgObj = $(e.target).parents('tr.message-table-tr');
        var msgid = msgObj.data('msgid').toString();
        var authorID = msgObj.data('userid').toString();
        var requestData = {
            action: 'modify2',
            d: 1,
            msg: msgid,
            requesttype: 'ajax',
            sc: Forum.sessioninfo.sid,
            board: Forum.currentLocation.args.board
        };
        var deletingConfirmed = false;
        if (Forum.sessioninfo.userid != authorID) {
            // Deleting alien message
            var reason = prompt('Укажите причину удаления сообщения', '');
            if (reason != '' && reason != null) {
                requestData.mdfrzn = reason;
                deletingConfirmed = true;
            } // reason not empty
        } // user not author
        else {
            if (confirm('Удалить это сообщение?')) {
                deletingConfirmed = true;
            }
        } // user is author
        if (deletingConfirmed) {
            $.ajax('index.php', {
                    type: 'POST',
                    timeout: 20000,
                    data: requestData,
                    success: function(data) {
                        if (data[0] == 'OK') {
                            msgObj.hide('slow');
                        } else if (data[0] == 'REDIRECT') {
                            document.location.href = data[1];
                        } else {
                            alert(data);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Произошла ошибка.\n' + status + ': ' + error + '\n' + xhr.responseText);
                    }
            }); // ajax
        } // if deletingConfirmed
    } //if Forum.ready
}; // deleteMessage()

/**
 * Delete thread button handler
 */
Forum.Utils.deleteThread = function(e, threadid){
    e.preventDefault();
    if (! Forum.ready) {
        alert("Дождитесь полной загрузки страницы и повторите действие снова.");
    } else {
        if (typeof(Forum.removingThread) == 'undefined' || Forum.removingThread == null) {
            Forum.removingThread = threadid;
        }
        else if (Forum.removingThread == threadid){
            return;
        }
        var requestData = {
            action: 'removethread2',
            threadid: threadid,
            requesttype: 'ajax',
            sc: Forum.sessioninfo.sid,
            board: Forum.currentLocation.args.board
        };
        var deletingConfirmed = false;
        var reason = prompt('Укажите причину удаления сообщения', '');
        if (reason != '' && reason != null) {
            requestData.reason = reason;
            deletingConfirmed = true;
        } // reason not empty
        if (deletingConfirmed) {
            $('body, a').css('cursor', 'wait');
            $.ajax('index.php', {
                    type: 'POST',
                    timeout: 180000,
                    data: requestData,
                    success: function(data) {
                        if (typeof(data) == 'object' && data.length == 2 && data[0] == 'REDIRECT'){
                            document.location.href = data[1];
                        }
                        else if (typeof(data) == 'string') {
                            alert(data);
                        }
                    }, // ajax success
                    error: function(xhr, status, error) {
                        Forum.removingThread = null;
                        alert('Произошла ошибка.\n' + status + ': ' + error + '\n' + xhr.responseText);
                    },
                    complete: function(){
                        $('body, a').css('cursor', '');
                }
            }); // ajax
        } // if deletingConfirmed
    }// if Forum.ready
}; // deleteThread()

Forum.Utils.previewPost = function(trgt) {
    var target = $(trgt);
    var form = target.closest('form');
    var textarea = form.find('textarea.editor').first();
    var content = textarea.val();
    if (content !== '') {
        $.ajax(Forum.sessioninfo.site_root + '/preview/', {
            dataType: 'html',
            type: 'POST',
            data: {message: content, sc: Forum.sessioninfo.sid},
            success: function(response) {
                var popup = new Forum.Utils.PopupWindow($(document).scrollTop() + 10);
                popup.open();
                popup.setContent(response);                
            }
        });
    }
}; // previewPost()

/**
 * Updates now playing on the radio info
 */
Forum.Utils.radioinfo = function(){
  var radioBlock = $("#radioinfo > p");
  if (radioBlock.length > 0){
    var radioURL, radioFmt;
    if (document.location.protocol == 'https:'){
      radioURL = 'index.php?action=api&query=radioinfo';
      radioFmt = 'json';
    } else {
      radioURL = 'http://theprodigy.ru:8000/status-jsonp.xsl';
      radioFmt = 'jsonp';
    }
    $.ajax({url: radioURL,
            dataType: radioFmt,
            jsonpCallback: 'callback',
            timeout: 5000,
            success: function(data){
              if (!$.isEmptyObject(data)){
                var np = null;
                var sources = data.icestats.source;
                var live = autodj = actualStream = null;
                // Prepare metadata
                for (i=0; i<sources.length; i++){
                  if (typeof sources[i].stream_start !== 'undefined'){
                    sources[i].artist = (typeof sources[i].artist === 'undefined') ? 'untitled' : sources[i].artist;
                    sources[i].title = (typeof sources[i].title === 'undefined') ? 'untitled' : sources[i].title;
                    sources[i].server_name = (typeof sources[i].server_name === 'undefined') ? '' : ' - ' + sources[i].server_name;
                    sources[i].nowPlaying = sources[i].artist + ' - ' + sources[i].title + sources[i].server_name;
                    sources[i].listenurl = (typeof sources[i].listenurl === 'undefined') ? '' : sources[i].listenurl;
                      if (sources[i].listenurl.indexOf('live.ogg') != -1){
                        live = i;
                      } else if (sources[i].listenurl.indexOf('autodj.ogg') != -1){
                        autodj = i;
                      }
                  }
                } // End of prepare metadata loop
                if (live !== null){
                  actualStream = live;
                } else if (autodj !== null){
                  actualStream = autodj;
                }
                if (actualStream !== null){
                  np = sources[actualStream].nowPlaying;
                }
                
                if (np){
                  radioBlock.first().html("<b>Сейчас играет</b>: " + np);
  		  radioBlock.last().prepend('<a href="/radio/" target="_blank">Слушать радио</a> | ')
  	        }
              } // response not empty
	    } // success function
            //error: function(data){$("#radioinfo > p").first().html("Радио сейчас не в эфире.");}
    }); // ajax request
  } // if radioblock exists
}; // End of get radio metadata function

Forum.Utils.stickMenubar = function(menubar){
    var windowTop = $(window).scrollTop();
    if (windowTop > menubar.offset){
        menubar.menubar.addClass('sticky');
	menubar.menubar.width(menubar.width);
    } else {
        menubar.menubar.removeClass('sticky');
	menubar.menubar.width('auto');
    }
};

/**
 * Parse URL args, return URL object containing
 * hostname, pathname, port, protocol properties and "args" object. 
 * @param {url) URL string
 * @param {delimeter} args delimeter (; or &)
 */ 
Forum.Utils.URL = function(url, delimeter) {
    if (typeof delimeter == 'undefined') {
        var delimeter = '&';
    }
    var src = document.createElement('a');
    src.href = url;
    var t_args = src.search.substring(1).split(delimeter);
    var args = {};
    for (var i=0; i<t_args.length; i++) {
        arg = t_args[i].split('=');
        args[arg[0]] = arg[1];
    }
    src.args = args;
    
    var _pathname = src.pathname.split('/');
    src.basename = _pathname[_pathname.length - 1];
    
    var _ext = src.basename.split('.');
    if (_ext.length > 1) {
      // Get file extension (ends with .*)
      src.ext = _ext[_ext.length - 1].toLowerCase();
    } else {
        src.ext = null;
    }
    
    return src;
};

Forum.Utils.HTMLEntitiesDecode = function(str){
    return $('<div/>').html(str).text();
};
Forum.Utils.HTMLEntitiesEncode = function(str){
    return $('<div/>').text(str).html();
};

Forum.storage = new function () {
    this._ls = null;
    this._ss = null;
    /**
     * Check storage availability
     * @param storageType storage type, "local" or "session"
     */
    this.available = function (storageType) {
        // check if storage available
        if (typeof storageType === 'undefined') var storageType = 'local';
        if (storageType == 'local') {
            if (this._ls == null) {
                // remember localStorage availability
                if (typeof localStorage === 'undefined') {
                    this._ls = false;
                } else {
                    try {
                        localStorage.setItem('__test__', 'test');
                        var x = localStorage.getItem('__test__');
                        localStorage.removeItem('__test__')
                        if (x == 'test') {
                            this._ls = true;
                        } else {
                            this._ls = false;
                        }
                    } catch (e) {
                        this._ls = false;
                    }
                } // if localStorage object available
            }
            return this._ls;
        } else {
            if (this._ss == null) {
                // remember sessionStorage availability
                if (typeof sessionStorage === 'undefined') {
                    this._ss = false;
                } else {
                    try {
                        sessionStorage.setItem('__test__', 'test');
                        var x = sessionStorage.getItem('__test__');
                        sessionStorage.removeItem('__test__')
                        if (x == 'test') {
                            this._ss = true;
                        } else {
                            this._ss = false;
                        }
                    } catch (e) {
                        this._ss = false;
                    }
                } // if sessionStorage object available
            }
            return this._ss;
        }
    }; // storage.available()
    
    /**
     * query data from storage
     * @param item what to query
     * @param type storage type, "local" or "session"
     */
    this.get = function(item, type) {
        if (typeof type === 'undefined') var type = 'local';
        if (this.available(type)) {
            if (type == 'local') {
                // localStorage query
                return localStorage.getItem(item);
            } else {
                // sessionStorage query
                return sessionStorage.getItem(item);
            }
        } // if storage available
        else return null;
    }; // storage.get()
    
    /**
     * store data in storage
     * @param item what to store
     * @param data value to store
     * @param type storage type, "local" or "session"
     */
    this.set = function(item, data, type) {
        if (typeof type === 'undefined') var type = 'local';
        if (this.available(type)) {
            if (type == 'local') {
                // store in localStorage
                return localStorage.setItem(item, data);
            } else {
                // store in sessionStorage
                return sessionStorage.setItem(item, data);
            }
        } // if storage available
        else return false
    }; // storage.set
    this.del = function(item, type) {
        if (typeof type === 'undefined') var type = 'local';
        if (this.available(type)) {
           if (type == 'local') {
                // delete item from localStorate
                return localStorage.removeItem(item);
            } else {
                // delete item from sessionStorage
                return sessionStorage.removeItem(item);
            }
        } // if storage available
        else return false;
    }; //storage.del()
}; //Forum.storage object
 
/**
 * Profile is a static class. It contains functions for Sources/Profile.php
 * @param {soundURL} URL of an MP3 file.
 */
Forum.Profile = {
	setIMSound: function(soundURL)
	{
		$('embed[id*="imSoundPlayer"]').remove();

		if (soundURL == null || soundURL.length <= 0)
			return;
		
		$('#imSoundPlayer').flash(
			{ src: '/includes/singlemp3player.swf', height: 20, width: 100, id: 'imSoundPlayer'+$('embed').size(), wmode: 'transparent' },
			{ version: 7 },
			function(htmlOptions) {
				htmlOptions.flashvars.file = soundURL;
				$(this).before($.fn.flash.transform(htmlOptions));						
			}
		);
	},
	
	toggleCongratulations: function(oCheckbox) {
		var checked = $(oCheckbox).attr('checked')=='checked';
		$.ajax({
			url: 'index.php?action=receivecongratulations;disable='+(!checked?1:0),
			dataType: 'html',
			success: function(response) {
				if (response == "ERROR")
					this.error();
			},
			error: function() {
				if (checked)
					$(oCheckbox).removeAttr('checked');
				else
					$(oCheckbox).attr('checked') = 'checked';
			}
		});
	}
};

/**
 * Data is a static class for asynchronous check and update of the forum data.
 */
Forum.Data = {
	/**
	 * Interval in milliseconds between queries to the server. Should be synchronized with the DataUpdates.get() query interval!
	 */
	UPDATE_DELAY: 5000,
	
	/**
	 * Latest update time in 'YYYY-MM-DD HH:MM:SS' format.
	 */
	actuality: null,
	
	/**
	 * The function calls update() function, if data actuality timestamp is set and the window is active.
	 * Actuality should be set before calling this function!
	 */
	continuousUpdate: function()
	{
		if (Forum.Data.actuality == '')
			Forum.Data.actuality = null;
		if (Forum.Data.actuality != null && Forum.isActiveWindow && typeof(Forum.sessioninfo.disableajaxupdates) == 'undefined')
			Forum.Data.update();
	},
	
	/**
	 * @private
	 * The function is used to parse the returned JSON data after an asynchronous get-updates-since call.
	 * @param {data} forum data updates as a json array
	 */
	parseDataUpdates: function(data){
		var numNewInstantMessages = 0;
		$.each(data, function(index, jsonObject)
		{
			if (jsonObject != null && jsonObject.className != null)
			{
				switch (jsonObject.className)
				{
					// case strings depend on PHP class names of the returned objects.
					case 'DataActualityUpdate': Forum.Data.updateActuality(jsonObject.dataActuality); break;
					case 'DataUpdatesException': Forum.Data.updateError(jsonObject.message); break;
					case 'NewInstantMessageUpdate': numNewInstantMessages++; break;
					case 'BoardViewersUpdate': Forum.Data.updateViewersList(jsonObject.boardID, jsonObject.html); break;
					default: Forum.Data.updateError('Unknown forum data update class "'+jsonObject.className+'"!');
				}
			}
		});
		
		Forum.Data.updateInstantMessages(numNewInstantMessages);
	},
	
	/**
	 * Make an asynchronous call to the update server page.
	 */
	update: function()
	{
		if (Forum.Data.actuality == null)
			return;

		var since = encodeURI(Forum.Data.actuality);		
		$.ajax({
			url: "index.php",
			data: {
				action: 'getupdates',
				since: since,
				useraction: $.getUrlVar('action'),
				board: $.getUrlVar('board'),
				topic: $.getUrlVar('threadid'),
				requesttype: 'ajax'
			},
			dataType: "json",
			success: Forum.Data.parseDataUpdates,
			error: function(jqXHR, textStatus, errorThrown)
			{
				console.error("ERROR in Forum.Data.update(): " + textStatus + " " + errorThrown);
			}
		});
	},
	
	/**
	 * Outputs an exception message to the console.
	 * @param {exception} an exception message string.
	 */
	updateError: function(exception)
	{
		console.error(exception);
	},
	
	/**
	 * Updates the number of instant messages.
	 * @param {numNewInstantMessages} number of new instant messages.
	 */
	updateInstantMessages: function(numNewInstantMessages)
	{
		if (numNewInstantMessages > 0)
			$.ajax({
				url: "index.php?action=instantmessageshtml;requesttype=ajax",
				dataType: "html",
				success: function(data){
					$('#instantMessages').html(data);
				},
				error: function(jqXHR, textStatus, errorThrown)
				{
					console.error("ERROR in Forum.Data.updateInstantMessages(): " + textStatus + " " + errorThrown);
				}
			});
	},
	
	/**
	 * Updates the update data actuality information.
	 * @param {dataActuality} date and time in 'YYYY-MM-DD HH:MM:SS' format.
	 */
	updateActuality: function(dataActuality)
	{
		Forum.Data.actuality = dataActuality;
	},
	
	/**
	 * Fetches current DB time asynchronously and sets the actuality value.
	 */
	initActuality: function()
	{
		$.ajax({
			url: "index.php?action=getcurrentdbtime;requesttype=ajax",
			dataType: "json",
			success: function(data){
				if (data.currentTime != null)
					Forum.Data.actuality = data.currentTime;
			},
			error: function(jqXHR, textStatus, errorThrown)
			{
				console.error("ERROR in Forum.Data.initActuality(): " + textStatus + " " + errorThrown);
			}
		});
	},
	
	/**
	 * @param {messageID} id of the forum message to show.
	 * @param {callback} a function which should be called upon the reponse.
	 * @returns forum message as html string.
	 */
	getMessageHTML: function(messageID, popupWindow)
	{
		var response = '<div style="text-align: center;"><img src="/YaBBImages/loading2.gif" style="margin: 50px;" width="48" height="48" alt="Загрузка" /><br /><span style="font-family: Verdana, Courier New, sans-serif; size: 16pt; letter-spacing: 3px;">идёт загрузка...</span></div>';
		
		$.ajax({
			url: "index.php?action=displaymessage;requesttype=ajax;id="+messageID,
			dataType: "html",
			success: function(response){
				if (response == '' || response == 'ERROR')
					popupWindow.setContent("Произошла ошибка! Пороверь подключение к интернет и попробуй ещё раз. Если ошибка повторяется, обратись, пожалуйста, к админу <b>dig7er</b>.");
				else
					popupWindow.setContent(response);
			},
			error: function(jqXHR, textStatus, errorThrown)
			{
				console.error("ERROR in Forum.Data.getMessageHTML(): " + textStatus + " " + errorThrown);
				popupWindow.setContent("Произошла ошибка! Пороверь подключение к интернет и попробуй ещё раз. Если ошибка повторяется, обратись, пожалуйста, к админу <b>dig7er</b>.");
			}
		});
		
		return response;
	},
	
	/**
	 * Update the list of users currently viewing the board.
	 * @param {boardID} ID of the board.
	 * @param {html} an HTML string with the list of users and guests currently viewing the board.
	 */
	updateViewersList: function(boardID, html)
	{
		if ($.getUrlVar('board') != null && $.getUrlVar('board') != '')
			// In the board
			$('#viewers').html(unescape(html));
		else if ($('a[name="b'+boardID+'"] ~ .boardViewersPane').length > 0) {
			// On the index page and the board viewers pane is shown to the user
			var curBoardViewersPane = $('a[name="b'+boardID+'"] ~ .boardViewersPane').html();
			var curNumViewers = parseInt($('a[name="b'+boardID+'"] ~ .boardViewersPane').children('.numBoardViewers').text());
			curNumViewers = isNaN(curNumViewers) ? 0 : curNumViewers;
			var newBoardViewersPane;
			var newNumViewers;
			if (html == '') {
				newBoardViewersPane = '';
				newNumViewers = 0;
			} else {
				newBoardViewersPane = $(html).html();
				newNumViewers = parseInt($(html).children('.numBoardViewers').text());
				newNumViewers = isNaN(newNumViewers) ? 0 : newNumViewers;
			}
			
			Forum.Data.updateBoardViewersPane(boardID, curBoardViewersPane, curNumViewers, newBoardViewersPane, newNumViewers);
		} else if ($('a[name="b'+boardID+'"]').length > 0 && html != '')
		{
			// On the index page and the board viewers pane is NOT shown to the user
			$('a[name="b'+boardID+'"] + font').after(html);
			var curBoardViewersPane = '';
			var curNumViewers = 0;
			var newBoardViewersPane = $(html).html();
			var newNumViewers = parseInt($(html).children('.numBoardViewers').text());
			newNumViewers = isNaN(newNumViewers) ? 0 : newNumViewers;
			Forum.Data.updateBoardViewersPane(boardID, curBoardViewersPane, curNumViewers, newBoardViewersPane, newNumViewers);
		}
	},
	
	/**
	 * @private
	 * Show board viewers difference after data update and the actual number of board viewers for the given board.
	 * Remove the board viewers pane when no viewers in the board.
	 */
	updateBoardViewersPane: function(boardID, curBoardViewersPane, curNumViewers, newBoardViewersPane, newNumViewers)
	{
		var userDifference = newNumViewers - curNumViewers;
		var viewersDiff = userDifference > 0 ? '<font color="green">+' + userDifference + '</font> ' : '<font color="red">' + userDifference + '</font> ';
		$('a[name="b'+boardID+'"] ~ .boardViewersPane').html(newBoardViewersPane);
		if (newBoardViewersPane != '')
			$('a[name="b'+boardID+'"] ~ .boardViewersPane').children('img').tooltip({
				bodyHandler: function() {
					return '<img src="YaBBImages/loading2.gif" alt="Идёт загрузка..." title="Идёт загрузка..." />';
				},
				showURL: false
			});
		
		if ($('a[name="b'+boardID+'"] ~ .boardViewersPane').children('.viewersDiff').length == 0 )
			$('a[name="b'+boardID+'"] ~ .boardViewersPane').prepend($('<span/>').addClass('viewersDiff'));
		
		var callback = (newBoardViewersPane == '') ? function () {$('a[name="b'+boardID+'"] ~ .boardViewersPane').remove();} : undefined;
		if (userDifference != 0)
			$('a[name="b'+boardID+'"] ~ .boardViewersPane').children('.viewersDiff').html(viewersDiff).fadeIn('fast').fadeTo(2500, 0.5).fadeOut('fast', callback);
	},
	
	/**
	 * Make an asynchronous call to change the given message's karma.
	 * @param {msgid} id of the message
	 * @param {direction} 'applaud' or 'smite'
	 */
	rateTheMessage: function(msgid, direction) {
		// set the cursor to waiting mode
		$('body').css('cursor', 'wait');

		$.ajax({
			url: 'index.php',
			data:	{
					action: "modifykarma",
					karmaAction: direction,
					mid: msgid,
					requesttype: 'ajax'
					},
			success: function(data) {
				$('#KarmaBtns'+msgid).hide();
				$('#karmaInfo'+msgid).css('display', 'table-row');
				if (data == "applaud"){
					$('span#Applauds'+msgid).html(parseInt($('span#Applauds'+msgid).html(), 10)+1);
				}
				else if (data == "smite"){
					$('span#Smites'+msgid).html(parseInt($('span#Smites'+msgid).html(), 10)+1);
				} else alert(data);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("ERROR ("+textStatus+"): " + errorThrown);
			},
			complete: function(jqXHR, textStatus) {
				// set the cursor to default mode
				$('body').css('cursor', 'default');
			}
		});
	},
	
	/**
	 * Shows in the jQuery tooltip the list of current board viewers.
	 * The tooltip with id "tooltip" is assumed to be existing.
	 */
	showBoardViewersTooltip: function(boardID)
	{
		$.ajax({
			url: 'index.php',
			data:	{
					action: "getboardviewers",
					boardid: boardID,
					requesttype: 'ajax'
					},
			dataType: "html",
			success: function(data) {
				$('#tooltip .body').html(data);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.error("ERROR ("+textStatus+"): " + errorThrown);
			}
		});
	}
};


$(function() {
  // Store current locatioin as object
  Forum.currentLocation = Forum.Utils.URL(document.location.href, ';');

  // Store session info
  Forum.sessioninfo = $('#sessioninfo').data('sessioninfo');
  if (typeof Forum.sessioninfo.username !== 'undefined')
      Forum.sessioninfo.username = Forum.Utils.HTMLEntitiesDecode(Forum.sessioninfo.username);
  if (typeof Forum.sessioninfo.realname !== 'undefined')
      Forum.sessioninfo.realname = Forum.Utils.HTMLEntitiesDecode(Forum.sessioninfo.realname);
  if (typeof Forum.sessioninfo.userid === 'undefined' || Forum.sessioninfo.userid == '') {
      Forum.sessioninfo.userid = '-1';
      Forum.sessioninfo.username = 'Guest';
  }
  
  if (Forum.sessioninfo.userid == '-1') {
      if (Forum.storage.get('referer', 'local') == null) {
          if (typeof document.referrer !== 'undefined' && document.referrer != '') {
              // remember where guest came from
              Forum.storage.set('referer', document.referrer, 'local');
              Forum.storage.set('reftarget', document.location.href, 'local');
          } else {
              Forum.storage.set('referer', 'none', 'local');
          }
      }
  }
  
  // Store mobile mode flag
  if ($('.display-mode-switch').length > 0) {
      Forum.isMobile = true;
      if (Forum.Utils.URL($('.display-mode-switch > a ').attr('href')).args.mobilemode == 'off') {
          Forum.isMobileMode = true;
      } else {
        Forum.isMobileMode = false;
      }
  } else {
      Forum.isMobile = false;
      Forum.isMobileMode = false;
  }
  
  // Submit textarea by pressing Ctrl+Enter
  $('textarea').keydown(function (e) {
    if (e.ctrlKey && e.keyCode == 13) {
      e.target.form.submit();
    }
  });
  $('[id^=commentBox]').bind('input change paste', function(e){
    // reduce commentBox maxLength if it contaims quote symbols
    e.target.maxLength = 256 - ((e.target.value.split("\'").length-1) + (e.target.value.split("\"").length-1));
    // countdown input symbols
    if (typeof e.target.counter === 'undefined') {
        e.target.counter = $(e.target).parent().next('div').children('input');
    }
    //$(e.target).parent().next('div').children('input').val(e.target.maxLength-e.target.value.length);
    e.target.counter.val(e.target.maxLength-e.target.value.length);
    });
  
//   Forum.Utils.checkBadGuy();

  // Scroll selectedd comment a little down
  if (document.location.hash.substring(1,8) == "comment"){
    Forum.Utils.highlightComment();
  }
  $(window).bind('hashchange', function(){
    if (document.location.hash.substring(1,8) == "comment"){
      Forum.Utils.highlightComment();
    }
  });
  
  // Binding for session config page
  $('.session-config-page form').on('submit', Forum.Utils.saveConfig);
  
  // Show now playing on the radio info at the board index page
  Forum.Utils.radioinfo();
  
  // Show full width picture pupup
  $('body').on('click', 'a.msgurl, a.msg-attachment, div.youtube-embed > a, img.msg-embed-img', Forum.Utils.showImage);
  
  // Show Youtube title
  $('div.youtube-embed > a').on('mouseover.yt-title touchstart.yt-title', Forum.Utils.showYtTitle);
  
  // Stick menubar at the top if the page is scrolled down
  // and there is notifications
  var menubar = {};
  if ($('.menubar .yyimbar').length > 0){
    menubar.menubar = $('.menubar');
    menubar.offset = menubar.menubar.offset().top;
    menubar.width = menubar.menubar.width();
    $(window).scroll(function(){Forum.Utils.stickMenubar(menubar);});
    Forum.Utils.stickMenubar(menubar);
  } // End of sticking menubar function

  // Send user's fingerprint when posting a message or registering
  if (Forum.currentLocation.args.action == 'display' ||
      Forum.currentLocation.args.action == 'post' ||
      Forum.currentLocation.args.action == 'post2' ||
      Forum.currentLocation.args.action == 'modify' ||
      Forum.currentLocation.args.action == 'modify2' ||
      Forum.currentLocation.args.action == 'register')
  {
      Forum.Utils.fingerprint();
  }
  
  // Show category recent messages
  $('a.catHead').on('click', Forum.Utils.recentCatMessages);

  // Trigger forum ready event
  Forum.ready = true;
  document.dispatchEvent(Forum.readyEvent);  
}); // End of document ready 

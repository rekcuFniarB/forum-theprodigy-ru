/*
 * Moderation API
 */

var Moderation = new function () {
    var that = this;
    this.popUp = new function () {
        this.window = null;
        this.open = function () {
            // Open popup window with search result
            this.window = new Forum.Utils.PopupWindow($(document).scrollTop() + 50);
            this.window.container = $('<div class="fp-search-container"><div class="fp-search-wait"></div></div>');
            this.window.open();
            this.window.close = Forum.Utils.PopupWindow.close;
            this.close = this.window.close;
            this.window.setContent(this.window.container);
            return this.window;
        }; // window.open()
    };
    this.moveMsg = new function () {
        var _this = this;
        this.isSourceThread = false;
        if (document.location.href.indexOf('action=display') > 0) {
        // It's a thread display page
            $(document).on("dragenter", function(event) {
                var postID = _this.getPostID(event);
                // If dragged element is a post link and it's not a source thread
                if (postID && !_this.isSourceThread) {
                    var dropZone = $('.post-drop-zone');
                    if (dropZone.length == 0) {
                        // Creating drop zone if not exists and binding events
                        $('body').append('<div class="post-drop-zone"><div>Брось сюда для перемещения сообщения в этот тред.</div></div>');
                        dropZone = $('.post-drop-zone');
                        dropZone.on("dragover", function(event) {
                            event.preventDefault();
                        });
                        dropZone.on("drop", function(event) {
                            event.preventDefault();
                            var postID = _this.getPostID(event);
                            _this.move(postID);
                            _this.store.del('postID');
                        }); // drop event handler
                        dropZone.on("dragleave", function(event) {
                            // Hide drop zone
                            dropZone.fadeOut();
                        }); // dragleave event handler
                        dropZone.on("dragend", function(event) {
                            // Hide drop zone
                            dropZone.fadeOut();
                            _this.store.del('postID');
                        }); // dragend event handler
                    } // if drop-zone not exists
                    // Display drop zone
                    dropZone.fadeIn();
                } // if PostID != NaN
            }); // dragenter event handler
            $(document).on('dragstart', function(event) {
                // Event occurs on source page
                _this.isSourceThread = true;
                var postID = _this.getPostID(event);
                if (postID) {
                    // Store dragged message ID to get it in the destination tab
                    _this.store.set('postID', postID);
                    var targetLink = event.target;
                    $(targetLink).on('dragend', function(event) {
                        window.setTimeout(function(){
                            // Check if message is in current thread and remove from the page if not.
                            var currentThreadID = _this.getArgs()['threadid'];
                            $.ajax('/index.php', {
                                type: 'GET',
                                async: true,
                                timeout: 7000,
                                data: {
                                    action: 'movemessage',
                                    msg: postID,
                                    thread: currentThreadID,
                                    requesttype: 'ajax'
                                },
                                success: function (response) {
                                    if (response == '__OK__') {
                                        $('a[name="msg' + postID + '"]').next().fadeOut('slow');
                                    }
                                }
                            }); // ajax
                        }, 4000); // setTimeout()
                        $(targetLink).off('dragend');
                        _this.store.del('postID');
                        _this.isSourceThread = false;
                    }); // source dragend event handler
                } else {
                    _this.isSourceThread = false;
                } // if postID (is a message link)
            }); // dragstart event handler
        } // if is thread view page

        this.getPostID = function (event) {
        // Return dragged message ID
            var postID = null;
            if (!this.isSourceThread) {
                postID = this.store.get('postID');
                if (postID) {
                    // Message ID already stored in the source tab, return it to the destination tab
                    return postID;
                } else {
                    return false;
                }
            }
            // Code above runs only in the destination tab.
            else 
            // Code below runs only in the source tab.
            {
                // var postLink = event.originalEvent.dataTransfer.getData("URL");
                var postLink = $(event.target).prop('href');
                if (!postLink) {
                    return false;
                }
                if (postLink.indexOf(document.location.origin) == 0 || postLink.indexOf('/') == 0) {
                    postLink = postLink.split('/');
                    postID = parseInt(postLink[postLink.length-1]);
                    if (postID != NaN) {
                        return postID;
                    } else {
                        // Not a message URL
                        return false;
                    }
                } else {
                    // It's third party domain
                    return false;
                }
            } // this.isSourceThread
        }; // getPostID()

        this.getArgs = function () {
        // Parse and return URL args
            var loc = document.location.href.split('?')[1].split('#')[0].split(';');
            var args = {};
            for (var i=0; i<loc.length; i++) {
                arg = loc[i].split('=');
                args[arg[0]] = arg[1];
            }
            return args;
        }; // getArgs()
        
        this.move = function (ID) {
        // Performing messaage moving
            var _this = this;
            var currentThreadID = this.getArgs()['threadid'];
            $.ajax('/index.php', {
                type: 'POST',
                async: true,
                timeout: 7000,
                data: {
                    action: 'movemessage',
                    msg: ID,
                    tothread: currentThreadID,
                    requesttype: 'ajax'
                },
                success: function (response) {
                    _this.checkResult(response, function(ok){
                        $('.post-drop-zone').fadeOut();
                        if (ok) {
                            document.location.href = 'index.php?action=gotomsg;msg=' + ID;
                        }
                    }); // checkResult()
                }, // ajax success
                error: function () {
                    alert('Произошла ошибка, повторите попытку позже.');
                    $('.post-drop-zone').fadeOut();
                } // ajax error handler
            }); // end of ajax
        }; // move()
        
        this.checkResult = function (response, callback) {
            switch(response){
                case '__ERROR__':
                   alert('Произошла ошибка!');
                   callback(false);
                   break;
                case '__GUEST__':
                    alert('Гости не могут выполнять это действие.');
                    callback(false);
                    break;
                case '__DENIED__':
                    alert('Вы не можете выполнять это действие.');
                    callback(false);
                    break;
                case '__EMPTY__':
                    alert('Пустое сообщение.');
                    callback(false);
                    break;
                case '__OK__':
                    callback(true);
                    break;
                default:
                    callback(false);
            } // switch case
        }; // End of checkResult()
        
        this.store = new function () {
            this._ls = true;
            if (typeof localStorage == 'undefined') {
                this._ls = false;
            }
            this.get = function (item) {
                if (this._ls) {
                    return localStorage.getItem(item);
                } else {
                    return null;
                }
            }; // store.get()
            this.set = function (item, data) {
                if (this._ls) {
                    localStorage.setItem(item, data);
                }
            }; // store.set()
            this.del = function (item) {
                if (this._ls) {
                    localStorage.removeItem(item);
                }
            }; // store.del()
        }; // store object
    }; // moveMsg object
    
    this.findUsersByFp = new function() {
        var self = this;
        this.handler = function (event){
            // Fingerprint element click handler
            var target = $(event.target);
            var user_lnk = Forum.Utils.URL(target.parents('table.message-table').find('td.userinfo > a').attr('href'), ';');
            var user = user_lnk.args.user;
            var fp = target.text();
            self.search(user, fp);
        };
        this.search = function(user, fp) {
            // Open popup window with search result
            var pw = that.popUp.open();
            $.ajax('index.php', {
                type: 'GET',
                timeout: 30000,
                dataType: 'html',
                data: {
                    requesttype: 'ajax',
                    action: 'api',
                    query: 'find_fingerprint',
                    usr: user,
                    fp: fp
                },
                success: function(data) {
                    $('.popupWindows > #messageContent').css({'max-height': $(window).height() - 124, 'overflow-y': 'auto'});
                    pw.container.html(data);
                    pw.align();
                },
                error: function(){
                    pw.container.text('Request error.');
                }
            }); // ajax
        }; // findUsersByFp.search()
    }; // findUsersByFp object
    this.showDeleted = function(e) {
        e.preventDefault();
        var target = $(e.target);
        var pw = that.popUp.open();
        var requestUrl = target.attr('href');
        $.ajax(requestUrl, {
            type: 'GET',
            timeout: 3000,
            dataType: 'html',
            success: function (data) {
                pw.container.html(data);
                pw.align();
            },
            error: function(){
                pw.container.text('Request error.');
            }
        });
    }; //showDeleted()
    this.toggleProfileBanDialog = function(e) {
        e.preventDefault();
        $('.profile-ban-dialog').toggle();
        $('.profile-ban-buttons').toggle();
    }; // toggleProfileBanDialog()
} // End of Moderation object.

$(function() { // Running below code on document ready
    // Bindings:
      // find users by fingerpring binding
      $('.agent-fingerprint').on('click', Moderation.findUsersByFp.handler);
      // show deleted message binding
      if (Forum.currentLocation.args.action == 'undelete')
          $('a.show-deleted').on('click', Moderation.showDeleted);
});

var kefu_code = 'KF_' + code;
var kefu_name= name;
var kefu_avatar =avatar;

$(function () {
    var kefu = {
        Message: {
            addLog: function (obj, type, code) {
                var chat_body = $('.layout .content .chat .chat-body ');
                if (chat_body.length > 0) {
                    type = type ? type : '';
                    message = obj.message ? obj.message : '你好你好.';
                    time = obj.time ? obj.time : (obj.create_time ? obj.create_time : '');
                    var html = '<div class="message-item ' + type + '" data-log-id="' + obj.log_id + '">';
                    html += '<div class="message-content">' + message + '</div>';
                    html += '<div class="message-action">' + time;
                    if (code == 0) {
                        html += (type ? '<i class="ti-check"></i>' : ''); //如果已读  改成 <i class="ti-double-check"></i>
                    } else {
                        html += '<i title="Message could not be sent" class="ti-info-alt text-danger"></i>'; //如果已读  改成 <i class="ti-double-check"></i>
                    }
                    html += '</div></div>';
                    $('.layout .content .chat .chat-body .messages').append(html);
                    chat_body.scrollTop(chat_body.get(0).scrollHeight, -1).niceScroll({
                        cursorcolor: 'rgba(66, 66, 66, 0.20)',
                        cursorwidth: "4px",
                        cursorborder: '0px'
                    });
                }
                return true;
            },
            getQueue: function (status) {
                status =status?status:1;
                $.getJSON('/index/kefu/getQueue', {
                    status: status,
                    kefu_code: code
                }, function (res) {
                    if (res.code == 200 && res.data.length > 0) {
                        if(status ==1){
                            $('#facing').html('');
                            $.each(res.data, function (key, item) {
                                var open = key == 0 ? 'open-chat' : '';
                                var avatar_state_success = key == 0 ? 'avatar-state-success' : '';
                                var facing_info = ' <li class="list-group-item ' + open + '" data-id="' + item.visitor_id + '">';
                                facing_info += '<figure class="avatar ' + avatar_state_success + '">';
                                facing_info += '<img src="' + item.visitor_avatar + '" class="rounded-circle"></figure>';
                                facing_info += ' <div class="users-list-body"> <h5>' + item.visitor_name + '</h5></div>';
                                $('#facing').append(facing_info);
                                //新建聊天框
                                if (key == 0) {
                                    kefu.Message.getChatLog(item.visitor_id, kefu_code);
                                    kefu.Message.setOnline(item.visitor_name, item.visitor_avatar);
                                }

                            });
                        }else {
                            $('#history').html('');
                            $.each(res.data, function (key, item) {
                                var open = key == 0 ? 'open-chat' : '';
                                var avatar_state_success = key == 0 ? 'avatar-state-success' : '';
                                var facing_info = ' <li class="list-group-item ' + open + '" data-id="' + item.visitor_id + '">';
                                facing_info += '<figure class="avatar ' + avatar_state_success + '">';
                                facing_info += '<img src="' + item.visitor_avatar + '" class="rounded-circle"></figure>';
                                facing_info += ' <div class="users-list-body"> <h5>' + item.visitor_name + '</h5></div>';
                                $('#history').append(facing_info);
                                //新建聊天框
                                if (key == 0) {
                                    kefu.Message.getChatLog(item.visitor_id, kefu_code);
                                    kefu.Message.setOnline(item.visitor_name, item.visitor_avatar,2);
                                }

                            });
                        }
                    }
                });
            },
            getHistLog:function(){

            },
            setOnline: function (visitor_name, visitor_avatar,status) {
                $('#visitor_avatar').html('<img src="' + visitor_avatar + '" class="rounded-circle">');
                $('#visitor_info h5').text(visitor_name);
                status=status?status:1;
                if(status ==1){
                    $('#visitor_info i').text('在线');
                }else {
                    $('#visitor_info i').text('离线');
                }

            },
            setOnlineL:function(){
                $('#visitor_avatar').html(' ');
                $('#visitor_info h5').text(' ');
                $('#visitor_info i').text(' ');
            },
            getfirstChatLog: function () {
                var show = $('.layout .content .chat .chat-body  .show');
                if (show.length > 0) {
                    kefu.Message.getChatLog(show.attr('data-id'), kefu_code);
                }
            },
            getChatLog: function (uid, kefu_code) {
                $('.layout .content .chat .chat-body  .messages').html('');
                $.getJSON('/index/kefu/getUserChatLog', {
                    uid: uid,
                    kefu_code: kefu_code
                }, function (res) {
                    if (res.code == 200 && res.data.length > 0) {
                        $.each(res.data, function (key, item) {
                            if (item.log == 'kefu') {
                                if (item.send_status == 1) {
                                    kefu.Message.addLog(item, 'outgoing-message', 0);
                                } else {
                                    kefu.Message.addLog(item, 'outgoing-message', 1);
                                }
                            } else if (item.log == 'visitor') {
                                if (item.send_status == 1) {
                                    kefu.Message.addLog(item, '', 0);
                                }
                            }
                        });
                    }
                });
            },
            toMeLog: function (obj) {
                $('.layout .content .sidebar-group #chats  #facing li').each(function () {
                    if ($(this).attr('data-id') == obj.id) {
                        if ($(this).hasClass('open-chat') == true) {
                            kefu.Message.addLog(obj, '', 0);
                            return true;
                        } else {
                            var add = $(this).find('.users-list-body');
                            if (add.find('.users-list-action').length > 0) {
                                var num = add.find('.users-list-action').find('.new-message-count').text();
                                add.find('.users-list-action').find('.new-message-count').text(Number(num) + 1);
                            } else {
                                add.append('<div class="users-list-action"><div class="new-message-count">1</div></div>');
                            }
                            return true;
                        }
                    }
                });
            },
            getChat: function (message,vid,vname,vavatar) {
                var data = {
                    from_id: kefu_code,
                    from_name: kefu_name,
                    from_avatar: kefu_avatar,
                    to_id: vid,
                    to_name: vname,
                    to_avatar: vavatar,
                    message: message
                };
                return data;
            }
        }
    };

    var websocket = new WebSocket('ws://' + window.location.hostname + ':' + port);
    websocket.onopen = function (evt) {
        onOpen(evt)
    };
    websocket.onclose = function (evt) {
        onClose(evt)
    };
    websocket.onmessage = function (evt) {
        onMessage(evt)
    };
    websocket.onerror = function (evt) {
        onError(evt)
    };

    function onOpen(evt) {
        console.log("连接成功");
        var msg = {};
        msg.data = {};
        msg.cmd = 'kefuConnection';
        msg.data.uid = kefu_code;
        websocket.send(JSON.stringify(msg));

    }

    function onClose(evt) {
        console.log("断开连接");
    }

    function onMessage(evt) {
        var obj = JSON.parse(evt.data);
        console.log(obj);
        if (obj.cmd == "chatMessage") {
            kefu.Message.toMeLog(obj.data);
        } else if (obj.cmd == "kefu_online") {
            $('#chats').find('.status').text('在线');
            //获取当前会话
            kefu.Message.getQueue()
        }else  if(obj.cmd == "userUpper"){
          //用户上线
            kefu.Message.getQueue();
        }else  if(obj.cmd == "diffClose"){
            $('.layout .content .sidebar-group #chats  #facing li').each(function () {
                  if($(this).attr('data-id') == obj.data.visitor_id){
                      var nextli=$(this).next();
                      $(this).remove();
                      if(nextli.length>0){
                          nextli.addClass('open-chat');
                          nextli.find('figure').addClass('avatar-state-success');
                          kefu.Message.getChatLog(nextli.attr('data-id'),kefu_code)
                      }else {
                          $('.layout .content .chat .chat-body  .messages').html('');
                          kefu.Message.setOnlineL();
                      }
                  }
            });
        }else  if(obj.cmd == "message"){
            if (obj.code == 200) {
                kefu.Message.addLog(obj.data, 'outgoing-message', 0);
            } else if (obj.code == 201) {
                kefu.Message.addLog(obj.data, 'outgoing-message', 1);
            }
        }

    }

    function onError(evt) {
        console.log("服务未开启");
        alert('服务未开启')
    }

    function sendMsg() {
        var content = $('#msg').val();
        var msg = {};
        content = content.replace(" ", "&nbsp;");
        if (!content) {
            return false;
        }
        console.log($('.layout .content .sidebar-group #chats  #facing .open-chat'));

        msg.data = content;
        console.log(msg)
        websocket.send(JSON.stringify(msg));
        $('#msg').val("");
        return true;
    }

    $(document).on('submit', '.layout .content .chat .chat-footer form', function (e) {
        e.preventDefault();

        var input = $(this).find('input[type=text]');
        var message = input.val();
        message = $.trim(message);
        var li=$('.layout .content .sidebar-group #chats  #facing .open-chat');
        if(li.length ==0 ){
            input.focus();
            return false;
        }
        var vid=li.attr('data-id');
        var vname=li.find('h5').text();
        var vavatar =li.find('img').attr('src');

        if (message) {
            var msg = {}
            msg.cmd = 'message';
            msg.data = kefu.Message.getChat(message,vid,vname,vavatar);
            websocket.send(JSON.stringify(msg));

            input.val('');
        } else {
            input.focus();
        }
    });

    $(document).on('click', '.layout .navigation .nav-group li ', function (e) {
        e.preventDefault();
              var obj= $(this).find('a');
             if(obj.hasClass('queue')){
                 $('.layout .content .chat .chat-body  .messages').html('');
                  kefu.Message.getQueue()
             }else if(obj.hasClass('notifiy_badge')){
                 $('.layout .content .chat .chat-body  .messages').html('');
                 kefu.Message.getQueue(2)
             }else  if(obj.hasClass('logout')){
                window.location.href="/index/login/logout";
             }
             return true;
    });

    $(document).on('click', '.layout .content .sidebar-group .sidebar .list-group-item', function () {
        if (jQuery.browser.mobile) {
            $(this).closest('.sidebar-group').removeClass('mobile-open');
        }
    });
    $(document).on('click', '.layout .content .sidebar-group #chats  #facing li', function (e) {
        e.preventDefault();
        if ($(this).hasClass('open-chat') == true) {
            return true
        } else {
            $(this).addClass('open-chat');
            $(this).find('.users-list-body').find('.users-list-action').remove();
            $(this).find('figure').addClass('avatar-state-success');
            $('.layout .content .sidebar-group #chats  #facing li').not(this).removeClass('open-chat');
            $('.layout .content .sidebar-group #chats  #facing li figure').not($(this).find('figure')).removeClass('avatar-state-success');
            //显示当前用户的聊天记录
            kefu.Message.setOnline($(this).find('h5').text(), $(this).find('img').attr('src'));
            var vid = $(this).attr('data-id');
            var obj = $('.chat-body').find('.' + vid);
            kefu.Message.getChatLog(vid, kefu_code);

        }
    });
    $(document).on('click', '.layout .content .sidebar-group #friends  #history li', function (e) {
        e.preventDefault();
        if ($(this).hasClass('open-chat') == true) {
            return true
        } else {
            $(this).addClass('open-chat');
            $(this).find('.users-list-body').find('.users-list-action').remove();
            $(this).find('figure').addClass('avatar-state-success');
            $('.layout .content .sidebar-group #friends  #history li').not(this).removeClass('open-chat');
            $('.layout .content .sidebar-group #friends  #history li figure').not($(this).find('figure')).removeClass('avatar-state-success');
            //显示当前用户的聊天记录
            kefu.Message.setOnline($(this).find('h5').text(), $(this).find('img').attr('src'));
            var vid = $(this).attr('data-id');
            var obj = $('.chat-body').find('.' + vid);
            kefu.Message.getChatLog(vid, kefu_code);

        }
    });
    var userAgent = navigator.userAgent; //取得浏览器的userAgent字符串
    var isOpera = userAgent.indexOf("Opera") > -1; //判断是否Opera浏览器
    var isIE = userAgent.indexOf("compatible") > -1 && userAgent.indexOf("MSIE") > -1 && !isOpera; //判断是否IE浏览器
    var isIE11 = userAgent.indexOf("rv:11.0") > -1; //判断是否是IE11浏览器
    var isEdge = userAgent.indexOf("Edge") > -1 && !isIE; //判断是否IE的Edge浏览器
    if(!isIE && !isEdge && !isIE11) {//兼容chrome和firefox
        var _beforeUnload_time = 0, _gap_time = 0;
        var is_fireFox = navigator.userAgent.indexOf("Firefox") > -1;//是否是火狐浏览器
        window.onunload = function () {
            _gap_time = new Date().getTime() - _beforeUnload_time;
            if (_gap_time <= 5) {
                //执行浏览器关闭你所要做的事情比如登出
                $.post('logout.do');
            } else {//浏览器刷新
            }
        }
        window.onbeforeunload = function () {
            _beforeUnload_time = new Date().getTime();
            if (is_fireFox) {//火狐关闭执行
                //执行浏览器关闭你所要做的事情比如登出
                $.post('logout.do');
            }
        };

    }
    });

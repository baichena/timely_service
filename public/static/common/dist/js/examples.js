var kefu_code = 'KF_' + code;
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
        if (obj.cmd == "chatMessage") {
            console.log(obj.data);
            //  kefu.Message.addLog(obj.data,'',0);
            kefu.Message.toMeLog(obj.data);
        } else if (obj.cmd == "kefu_online") {
            $('#chats').find('.status').text('在线');
            //获取当前会话
            kefu.Message.getQueue()
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
        msg.cmd = 'message';
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
        if (message) {
            var msg = {}
            msg.cmd = 'message';
            msg.data = kefu.Message.getChat(message);
            websocket.send(JSON.stringify(msg));

            input.val('');
        } else {
            input.focus();
        }
    });

    $(document).on('click', '.layout .navigation .nav-group li ', function (e) {
        e.preventDefault();

             if($(this).find('a').hasClass('queue')){
                  kefu.Message.getQueue()
             }else if($(this).find('a').hasClass('notifiy_badge')){
                 kefu.Message.getQueue(2)
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


});

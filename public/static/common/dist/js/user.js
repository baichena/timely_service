// 服务的客服标识
var kefu_code = 0;
// 服务的客服名称
var kefu_name = '';
// 服务的客服头像
var kefu_avatar = '';
var visitor = {
    visitor_id: '',
    visitor_name: '',
    visitor_avatar: '',
    kefu_code:0
};
$(function () {

    var user = {
        Message: {
            initialize:function(){
                var _uid = localStorage.getItem('uid');
                var _name = localStorage.getItem("name");
                var _avatar = localStorage.getItem("avatar");

                if (_uid == null) {
                    _uid = uid;
                    localStorage.setItem('uid', _uid);
                }
                if (_name == null) {
                    _name = uname;
                    localStorage.setItem('name', _name);
                }
                if (_avatar == null) {
                    _avatar = uavatar;
                    localStorage.setItem('avatar', _avatar);
                }
                visitor.visitor_id = _uid;
                visitor.visitor_name = _name;
                visitor.visitor_avatar = _avatar;
                visitor.kefu_code = code;
            },
            add: function (obj, type, code) {
                var chat_body = $('.layout .content .chat .chat-body');
                if (chat_body.length > 0) {
                    type = type ? type : '';
                    message = obj.message ? obj.message : '你好你好.';
                    time = obj.time ? obj.time : (obj.create_time ? obj.create_time : '');
                    var html = '<div class="message-item ' + type + '" data-log-id="' + obj.chat_log_id + '">';
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
            },
            connectKefu: function (data) {
                kefu_code = data.kefu_code;
                kefu_name = data.kefu_name;
                kefu_avatar = data.kefu_avatar;
                $('#kefu_name').text(data.kefu_name);
            },
            getChat: function (message) {
                var data = {
                    from_id: visitor.visitor_id,
                    from_name: visitor.visitor_name,
                    from_avatar: visitor.visitor_avatar,
                    to_id: kefu_code,
                    to_name: kefu_name,
                    to_avatar: kefu_avatar,
                    message: message
                };
                return data;
            },
            getChatLog: function () {
                $.getJSON('/index/index/getUserChatLog', {
                    uid: visitor.visitor_id,
                    kefu_code: kefu_code
                }, function (res) {
                    if (res.code == 200 && res.data.length > 0) {
                        $.each(res.data, function (key, item) {

                            if (item.log == 'visitor') {
                                if (item.send_status == 1) {
                                    user.Message.add(item, 'outgoing-message', 0);
                                } else {
                                    user.Message.add(item, 'outgoing-message', 1);
                                }
                            } else if (item.log == 'kefu') {
                                if (item.send_status == 1) {
                                    user.Message.add(item,'',0);
                                }
                            }
                        });
                    }
                });
            }
        }
    };
    user.Message.initialize();
    var websocket = new WebSocket('ws://'+window.location.hostname+':'+port);
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
        msg.cmd = 'visitorConnection';
        msg.data.visitor_id = visitor.visitor_id;
        msg.data.visitor_name = visitor.visitor_name;
        msg.data.visitor_avatar = visitor.visitor_avatar;
        websocket.send(JSON.stringify(msg));
    }

    function onClose(evt) {
        console.log("断开连接");
    }

    function onMessage(evt) {
        var obj = JSON.parse(evt.data);
        //上线成功 连接客服
        if (obj.cmd == 'online') {
            var msg = {};
            msg.data = {};
            msg.cmd = 'visitorToKefu';
            msg.data.uid = visitor.visitor_id;
            msg.data.name =  visitor.visitor_name;
            msg.data.avatar = visitor.visitor_avatar;
            msg.data.kefu_code = visitor.kefu_code;
            websocket.send(JSON.stringify(msg));
        } else if (obj.cmd == 'visitorToKefu') {
            //连接客服成功
            if(obj.code == 200){
                user.Message.connectKefu(obj.data);
                //获取与该客服聊天记录
                 user.Message.getChatLog();
            }else if(obj.code == 201){
                alert('客服已下线')
            }

        } else if (obj.cmd == "message") {
            if (obj.code == 200) {
                user.Message.add(obj.data, 'outgoing-message', 0);
            } else if (obj.code == 201) {
                user.Message.add(obj.data, 'outgoing-message', 1);
            }
            return true;
        } else if (obj.cmd = "chatMessage") {
            if (obj.code == 200) {
                user.Message.add(obj.data, '', 0);
            }
        }

    }

    function onError(evt) {
        console.log("服务未开启");
        alert('服务未开启')
    }

    $(document).on('submit', '.layout .content .chat .chat-footer form', function (e) {
        e.preventDefault();

        var input = $(this).find('input[type=text]');
        var message = input.val();
        message = $.trim(message);
        if (message) {
            var msg = {}
            msg.cmd = 'message';
            msg.data = user.Message.getChat(message);
            websocket.send(JSON.stringify(msg));

            input.val('');
        } else {
            input.focus();
        }
    });

    $(document).on('click', '.layout .content .sidebar-group .sidebar .list-group-item', function () {
        if (jQuery.browser.mobile) {
            $(this).closest('.sidebar-group').removeClass('mobile-open');
        }
    });

});

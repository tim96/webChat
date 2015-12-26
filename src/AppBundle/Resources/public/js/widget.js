(function($) {
    $.fn.extend({chatWidget: function(options) {
        var o = jQuery.extend({
            wsUri: 'ws://'+location.host+':'+options.port,
            tmplClass: '.template',
            tmplIdle: '#template_idle',
            tmplWait: '#template_wait',
            tmplChat: '#template_chat',
            btnBeginChat: '.begin-chat',
            labelWaitState: '.state',
            messageBox: '#message_box',
            formSend: '#send-msg-form',
            textMessage: '#message',
            btnCloseChat: '.close-chat'
        },options);

        console.log('options', options);

        var websocket, fsm;

        var windowNotifier = function(){
            var
                window_active = true,
                new_message = false;

            $(window).blur(function(){
                window_active = false;
            });
            $(window).focus(function(){
                window_active = true;
                new_message = false;
            });

            var original = document.title;
            window.setInterval(function() {
                if (new_message && window_active == false) {
                    document.title = options.labels.newMessage;
                    setTimeout(function(){
                        document.title = original;
                    }, 750);
                }
            }, 1500);

            return {
                setNewMessage: function() {
                    new_message = true;
                }
            };
        } ();

        var initSocket = function() {
            websocket = new WebSocket(o.wsUri);
            websocket.onopen = function(e) {
                fsm.request();
            };
            websocket.onclose 	= function(e){
                fsm.close();
            };
            websocket.onerror	= function(e){
                console.log(e);
                if (websocket.readyState == 1) {
                    websocket.close();
                }
            };
            websocket.onmessage = function(e) {
                var msg = JSON.parse(e.data);
                switch (msg.type) {
                    case 'response':
                        fsm.response();
                        windowNotifier.setNewMessage();
                        break;
                    case 'message':
                        chatController.addMessage(msg);
                        if (msg.from == 'me') {
                            chatController.unspinChat();
                        } else {
                            windowNotifier.setNewMessage();
                        }
                        $(o.textMessage).focus();
                        break;
                }
            }
        };

        var setView = function(tmpl) {
            $(o.tmplClass).removeClass('active');
            $(tmpl).addClass('active');
        };

        var idleController = function() {
            $(o.btnBeginChat).click(function() {
                fsm.open();
            });

            return {
                show: function() {
                    setView(o.tmplIdle);
                }
            };
        } ();

        var waitController = function() {
            return {
                show: function(label) {
                    $(o.labelWaitState).text(label);
                    setView(o.tmplWait);
                }
            };
        } ();

        var chatController = function() {
            $(o.textMessage).keydown(function (e) {
                if (e.ctrlKey && e.keyCode == 13) {
                    $(o.formSend).trigger('submit');
                }
            });

            $(document).on('submit', o.formSend, function(e) {
                e.preventDefault();
                var text = $(o.textMessage).val();
                text = $.trim(text);
                if (!text) {
                    return;
                }
                var msg = {
                    type: 'message',
                    message: text
                };
                websocket.send(JSON.stringify(msg));
                $(o.textMessage).val('');
                chatController.spinChat();
            });

            $(o.btnCloseChat).click(function(e) {
                websocket.close();
            });

            var htmlForTextWithEmbeddedNewlines = function(text) {
                var htmls = [];
                var lines = text.split(/\n/);
                var tmpDiv = jQuery(document.createElement('div'));
                for (var i = 0 ; i < lines.length ; i++) {
                    htmls.push(tmpDiv.text(lines[i]).html());
                }
                return htmls.join("<br>");
            };

            return {
                clear: function() {
                    $(o.messageBox).empty();
                },
                lockChat: function() {
                    $(o.formSend).find(':input').attr('disabled', 'disabled');
                },
                unlockChat: function() {
                    $(o.formSend).find(':input').removeAttr('disabled');
                },
                spinChat: function() {
                    chatController.lockChat();
                    $(o.formSend).find('.btn').addClass('active');
                },
                unspinChat: function() {
                    $(o.formSend).find('.btn').removeClass('active');
                    chatController.unlockChat();
                },
                showChat: function() {
                    chatController.unlockChat();
                    $('.show-closed').hide();
                    $('.show-chat').show();
                    setView(o.tmplChat);
                },
                showClosed: function() {
                    chatController.lockChat();
                    $('.show-chat').hide();
                    $('.show-closed').show();
                    setView(o.tmplChat);
                },
                addMessage: function(msg) {
                    var d = new Date();
                    var text = htmlForTextWithEmbeddedNewlines(msg.message);
                    $(o.messageBox).append(
                        '<div>' +
                        '<span class="user_name">'+msg.from+'</span> : <span class="user_message">'+text + '</span>' +
                        '<span class="pull-right">'+d.toLocaleTimeString()+'</span>' +
                        '</div>'
                    );

                    $(o.messageBox).scrollTop($(o.messageBox)[0].scrollHeight);
                },
                addSystemMessage: function(msg) {
                    $(o.messageBox).append('<div class="system_msg">'+msg+'</div>');

                }
            };
        } ();

        fsm = StateMachine.create({
            initial: 'idle',
            events: [
                { name: 'open',  from: ['idle', 'closed'],  to: 'connecting' },
                { name: 'request',  from: 'connecting',  to: 'waiting' },
                { name: 'response',  from: 'waiting',  to: 'chat' },
                { name: 'close',  from: ['connecting', 'waiting'],  to: 'idle' },
                { name: 'close',  from: 'chat',  to: 'closed' }
            ],
            callbacks: {
                onidle: function(event, from, to) { idleController.show(); },
                onconnecting: function(event, from, to) { waitController.show(options.labels.serverConnection); },
                onwaiting: function(event, from, to) { waitController.show(options.labels.waitContestant); },
                onchat: function(event, from, to) { chatController.showChat(); },
                onclosed: function(event, from, to) { chatController.showClosed(); },
                onopen:  function(event, from, to) { initSocket(); },
                onrequest: function (event, from, to) {
                    var msg = {
                        type: 'request'
                    };
                    websocket.send(JSON.stringify(msg));
                },
                onresponse: function (event, from, to) {
                    chatController.clear();
                    chatController.addSystemMessage(options.labels.findContestant);
                },
                onclose: function (event, from, to) {
                    chatController.addSystemMessage(options.labels.chatClosed);
                }
            }
        });
    }})
})(jQuery);
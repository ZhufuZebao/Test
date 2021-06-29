var _this = this;
var MessageTypes;
(function (MessageTypes) {
    MessageTypes[MessageTypes["CHAT"] = 0] = "CHAT";
    MessageTypes[MessageTypes["MUTE"] = 1] = "MUTE";
    MessageTypes[MessageTypes["UNMUTE"] = 2] = "UNMUTE";
    MessageTypes[MessageTypes["NAME"] = 3] = "NAME";
    MessageTypes[MessageTypes["NAMEQUERY"] = 4] = "NAMEQUERY";
})(MessageTypes || (MessageTypes = {}));

var DeviceTypes;
(function (DeviceTypes) {
    DeviceTypes[DeviceTypes["AUDIO"] = 0] = "AUDIO";
    DeviceTypes[DeviceTypes["VIDEO"] = 1] = "VIDEO";
})(DeviceTypes || (DeviceTypes = {}));
$(document).ready(function () {
    var api_key = '187780b1-083a-41b4-9232-5e12f5cc3ba9';
    var myId;
    var myName;
    var room;
    var localStream;
    var screenshare;
    var DefaultNames = [
        'メンバー1',
        'メンバー2',
        'メンバー3',
        'メンバー4',
        'メンバー5',
        'メンバー6',
        'メンバー7',
        'メンバー8',
        'メンバー9',
        'メンバー10',
        'メンバー11',
        'メンバー12'
    ];
    var peerId = 'PeerId_' + $('meta[name="csrf-token"]').attr('content');
    var start = function () {

        myName = localStorage.getItem('SkyConf_name') || DefaultNames[Math.floor(Math.random() * DefaultNames.length)];
        RoomUI.SelfUI.setName(myName);
        localStorage.setItem('SkyConf_name', myName);

        $.post('http://tk2-224-21995.vs.sakura.ne.jp/skyway/auth.php', {
            peerId: peerId,
            sessionToken: $('meta[name="csrf-token"]').attr('content')
            //sessionToken: '4CXS0f19nvMJBYK05o3toTWtZF5Lfd2t6Ikr2lID'
        }, function(credential) {

            var peer = new Peer(peerId, {
                key: api_key,
                credential: credential
            });

            peer.on('open', function () {
                myId = peerId;
                var audioSelect = $('#audioSource');
                var videoSelect = $('#videoSource');
                var selectors = [audioSelect, videoSelect];

                navigator.mediaDevices.enumerateDevices().then(function (deviceInfos) {
                    var values = selectors.map(function (select) {
                        return select.val() || '';
                    });
                    for (var i = 0; i < deviceInfos.length; ++i) {
                        var deviceInfo = deviceInfos[i];
                        var option = $('<option>');
                        option.val(deviceInfo.deviceId);
                        if (deviceInfo.kind === 'audioinput') {
                            option.text(deviceInfo.label ||
                                'Microphone ' + (audioSelect.children().length + 1));
                            audioSelect.append(option);
                        }
                        else if (deviceInfo.kind === 'videoinput') {
                            option.text(deviceInfo.label || 'Camera ' +
                                (videoSelect.children().length + 1));
                            videoSelect.append(option);
                        }
                    }
                    selectors.forEach(function (select, selectorIndex) {
                        if (Array.prototype.slice.call(select.children()).some(function (n) {
                            return n.value === values[selectorIndex];
                        })) {
                            select.val(values[selectorIndex]);
                        }
                    });
                    videoSelect.on('change', function () {
                        getStream({ video: true });
                    });
                    audioSelect.on('change', function () {
                        getStream({ audio: true });
                    });
                    getStream({ video: true, audio: true });
                });
            });
            var gumFFTimeout;
            peer.on('error', function (err) {
                console.log(err.message);
                clearTimeout(gumErrorTimeout);
                clearTimeout(gumFFTimeout);
            });
            if ($.ua.browser.name === 'Firefox') {
                gumFFTimeout = setTimeout(function () {
                    RoomUI.Common.popover('permissions');
                }, 1000);
            }
            var gumErrorTimeout = setTimeout(function () {
                RoomUI.Common.modal('gum-error');
            }, 10000);
            var getStream = function (src) {
                var audioSource = $('#audioSource').val();
                var videoSource = $('#videoSource').val();
                var videoConstraints = navigator.webkitGetUserMedia ?
                    {
                        optional: [{
                                sourceId: videoSource
                            }, {
                                minWidth: "640",
                            }, {
                                maxWidth: "640",
                            }, {
                                minHeight: "480",
                            }, {
                                maxHeight: "480",
                            }, {
                                minFrameRate: "10",
                            }, {
                                maxFrameRate: "10"
                            }]
                    }
                    :
                    {
                        width: 640,
                        height: 480,
                        frameRate: { ideal: 10, max: 15 }
                    };
                var constraints = {
                    audio: { deviceId: audioSource ? { exact: audioSource } : undefined },
                    video: videoConstraints
                };
                navigator.mediaDevices.getUserMedia(constraints).then(function (newStream) {
                    RoomUI.Common.popover('permissions', 'hide');
                    if (gumFFTimeout)
                        clearTimeout(gumFFTimeout);
                    RoomUI.Common.modal('gum-error', 'hide');
                    clearTimeout(gumErrorTimeout);
                    localStream = newStream;
                    RoomUI.SelfUI.setVideoSrc(localStream, false);
                    if (room) {
                        replaceStream(src, newStream);
                    }
                    else {
                        joinRoom(newStream);
                    }
                }, function (e) {
                    console.error(e);
                });
            };
            var replaceStream = function (src, newStream) {
                var ms = new window.MediaStream();
                if (src.video || src.screenShare) {
                    newStream.getVideoTracks().forEach(function (track) {
                        ms.addTrack(track.clone());
                    });
                }
                else {
                    localStream.getVideoTracks().forEach(function (track) {
                        ms.addTrack(track.clone());
                    });
                }
                if (src.audio) {
                    newStream.getAudioTracks().forEach(function (track) {
                        ms.addTrack(track.clone());
                    });
                }
                else {
                    localStream.getAudioTracks().forEach(function (track) {
                        ms.addTrack(track.clone());
                    });
                }
                newStream.getTracks().forEach(function (track) {
                    track.stop();
                    newStream.removeTrack(track);
                });
                localStream.getTracks().forEach(function (track) {
                    track.stop();
                    localStream.removeTrack(track);
                });
                localStream = ms;
                if (!newStream.isScreenShare && src.video) {
                    RoomUI.SelfUI.disableShareIcon();
                }
                RoomUI.SelfUI.setVideoSrc(localStream, newStream.isScreenShare);
                room.replaceStream(localStream);
            };
            var joinRoom = function (localStream) {
                var roomName = roomName = $('#join-room').val();
                var roomType = location.hash.slice(1).toLowerCase().includes('mesh') ? 'mesh' : 'sfu';
                room = peer.joinRoom('mesh_video_' + roomName, {mode: 'mesh', stream: localStream});
                room.on('stream', function (stream) {
                    console.log('%caddVideo', 'color: #00ff00', stream.peerId, stream.id);
                    RoomUI.VideoUI.addVideo(stream.id, stream.peerId, stream, false).then(function () {
                        RoomUI.VideoUI.setVideoMute(stream.id, false);
                        RoomUI.VideoUI.setVideoReconnecting(stream.id, false);
                        RoomUI.VideoUI.setShareReconnecting(stream.id, false);
                    });
                    keepSending({ type: MessageTypes.NAME, value: encodeURIComponent(myName) });
                });
                room.on('removeStream', function (stream) {
                    console.log('%cremoveStream', 'color: #00ff00', stream.peerId, stream.id);
                    RoomUI.VideoUI.removeVideoById(stream.id, false);
                });
                room.on('peerLeave', function (src) {
                    console.log('%cpeerLeave', 'color: #00ff00', src);
                    RoomUI.VideoUI.removeVideosByPeerId(src, false);
                });
                room.on('data', function (mesg) {
                    switch (mesg.data.type) {
                        case MessageTypes.CHAT:
                            var chatMessage = mesg.data.value;
                            RoomUI.ChatUI.addMessage(mesg.src, decodeURIComponent(chatMessage.name), chatMessage.img, new Date(), decodeURIComponent(chatMessage.text), false);
                            break;
                        case MessageTypes.MUTE:
                            switch (mesg.data.value) {
                                case DeviceTypes.VIDEO:
                                    RoomUI.VideoUI.setVideoMute(mesg.src, true);
                                    break;
                            }
                            break;
                        case MessageTypes.UNMUTE:
                            switch (mesg.data.value) {
                                case DeviceTypes.VIDEO:
                                    RoomUI.VideoUI.setVideoMute(mesg.src, false);
                                    break;
                            }
                            break;
                        case MessageTypes.NAME:
                            RoomUI.VideoUI.setName(mesg.src, decodeURIComponent(mesg.data.value));
                            break;
                        case MessageTypes.NAMEQUERY:
                            room.send({ type: MessageTypes.NAME, value: myName });
                            break;
                    }
                });
                return room;
            };
            var outgoingMsgs = {};
            var keepSending = function (mesg) {
                if (!mesg || mesg.type === undefined) {
                    return -1;
                }
                if (outgoingMsgs[mesg.type]) {
                    stopSending(mesg.type);
                }
                outgoingMsgs[mesg.type] = setInterval(function () {
                    room.send(mesg);
                }, 1000);
                room.send(mesg);
                return outgoingMsgs[mesg.type];
            };
            var stopSending = function (type) {
                if (!outgoingMsgs[type]) {
                    return;
                }
                clearInterval(outgoingMsgs[type]);
                delete outgoingMsgs[type];
            };
            $('#message form').on('submit', function (ev) {
                ev.preventDefault();
                var $text = $(_this).find('input[type=text]');
                var data = $text.val();
                if (data.length > 0) {
                    data = data.replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    $('p.receive').append(data + '<br>');
                    room.send(data);
                    $text.val('');
                }
            });
            RoomUI.SelfUI.on('micEnabled', function () {
                localStream.getAudioTracks().forEach(function (track) {
                    track.enabled = true;
                });
                stopSending(MessageTypes.MUTE);
                keepSending({ type: MessageTypes.UNMUTE, value: DeviceTypes.AUDIO });
            });
            RoomUI.SelfUI.on('micDisabled', function () {
                localStream.getAudioTracks().forEach(function (track) {
                    track.enabled = false;
                });
                stopSending(MessageTypes.UNMUTE);
                keepSending({ type: MessageTypes.MUTE, value: DeviceTypes.AUDIO });
            });
            RoomUI.SelfUI.on('videoEnabled', function () {
                localStream.getVideoTracks().forEach(function (track) {
                    track.enabled = true;
                });
                stopSending(MessageTypes.MUTE);
                keepSending({ type: MessageTypes.UNMUTE, value: DeviceTypes.VIDEO });
            });
            RoomUI.SelfUI.on('videoDisabled', function () {
                localStream.getVideoTracks().forEach(function (track) {
                    track.enabled = false;
                });
                stopSending(MessageTypes.UNMUTE);
                keepSending({ type: MessageTypes.MUTE, value: DeviceTypes.VIDEO });
            });
            RoomUI.SelfUI.on('screenShareStarted', function () {
                screenshare = ScreenShare.create({});
                if (screenshare.isScreenShareAvailable()) {
                    screenshare.start({
                        width: screen.width,
                        height: screen.height,
                        frameRate: 5
                    }).then(function (screenStream) {
                        screenStream.isScreenShare = true;
                        replaceStream({ screenShare: true }, screenStream);
                        RoomUI.SelfUI.activateShareIcon();
                    }).catch(function (error) {
                        console.log(error);
                    });
                }
                else {
                    RoomUI.Common.modal('extension');
                }
            });
            RoomUI.SelfUI.on('screenShareEnded', function () {
                if (localStream) {
                    screenshare.stop();
                }
                RoomUI.SelfUI.disableShareIcon();
                getStream({ video: true });
            });
            RoomUI.SelfUI.on('nameUpdated', function (newName) {
                if (myName === newName) {
                    return;
                }
                localStorage.setItem('SkyConf_name', newName);
                RoomUI.SelfUI.setName(newName);
                keepSending({ type: MessageTypes.NAME, value: encodeURIComponent(newName) });
                myName = newName;
            });
            RoomUI.ChatUI.on('messageSubmitted', function (message) {
                var img = '';
                if (RoomUI.SelfUI.videoEnabled) {
                    img = generateThumbnail(RoomUI.SelfUI.video, 40, 40);
                }
                RoomUI.ChatUI.addMessage(myId, myName, img, new Date(), message, true);
                room.send({
                    type: MessageTypes.CHAT,
                    value: {
                        name: encodeURIComponent(myName),
                        text: encodeURIComponent(message),
                        img: img
                    }
                });
            });

        }).fail(function() {
            alert('Peer Authentication Failed');
        });

    };
    var detectValidBrowser = function () {
        switch ($.ua.browser.name) {
            case 'Firefox':
            case 'Chrome':
            case 'Opera':
                return true;
            default:
                return false;
        }
    };
    var generateThumbnail = function (video, height, width) {
        var canvas = document.createElement('canvas');
        var tempCanvas = document.createElement('canvas');
        var sx, sy, sw, sh;
        var videoHeight = $(video).height();
        var videoWidth = $(video).width();
        var videoRatio = videoHeight / videoWidth;
        var imageRatio = height / width;
        tempCanvas.height = videoHeight;
        tempCanvas.width = videoWidth;
        var tempContext = tempCanvas.getContext('2d');
        tempContext.drawImage(video, 0, 0, tempCanvas.width, tempCanvas.height);
        if (videoRatio > imageRatio) {
            sx = 0;
            sw = videoWidth;
            sh = imageRatio * sw;
            sy = (videoHeight - sh) / 2;
        }
        else if (videoRatio < imageRatio) {
            sy = 0;
            sh = videoHeight;
            sw = sh / imageRatio;
            sx = (videoWidth - sw) / 2;
        }
        else {
            sx = sy = 0;
            sh = videoHeight;
            sw = videoWidth;
        }
        canvas.width = width;
        canvas.height = height;
        var context = canvas.getContext('2d');
        context.drawImage(tempCanvas, sx, sy, sw, sh, 0, 0, height, width);
        return canvas.toDataURL();
    };
    if ($.ua.os.name === 'iOS') {
        RoomUI.Common.modal('unsupported-device');
        return;
    }
    if (detectValidBrowser() === false) {
        RoomUI.Common.modal('unsupported-browser');
        return;
    }
    start();


});
//# sourceMappingURL=main.js.map
var RoomUI;

!function(a) {
    a.Url = location.origin + location.pathname;
    a.Subject = "SkyWay Conferenceに招待されました";
    a.Message = "SkyWay Conferenceに招待されました。%0d%0a%0d%0a下記のリンクにアクセスすると会議に参加できます。%0d%0a%0d%0a" + encodeURI(a.Url);

    var b = {};

    b.setVideoStream = function(a, b) {
        if ("string" == typeof b) {
            a.attr("src", b);
        } else {
            a[0].srcObject = b;
        }
        return a;
    };

    var c = function() {
        function b() {}

        $(window).on("click", function(b) {
            if ($(b.target).closest(".permission-popover").length > 0) {
                a.Common.modal("permissions");
            }
            a.Common.popover("permissions", "hide");
        });

        $(".permission-dummy").popover({
            html: true,
            template: '<div class="popover permission-popover" role="tooltip" id="permission-popover"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
        });

        b.prototype.modal = function(a, b) {
            b = b || "show";

            var c;

            switch (a) {
              case "permissions":
                c = $("#app-permissions-modal");
                break;

              case "extension":
                c = window.chrome ? $("#chrome-extension-modal") : $("#ff-extension-modal");
                break;

              case "invite":
                c = $("#app-invite-modal");
                break;

              case "maxuser":
                c = $("#app-maxuser-modal");
                break;

              case "gum-error":
                c = $("#app-gum-error-modal");
                break;

              case "unsupported-device":
                c = $("#app-unsupported-device-modal");
                break;

              case "unsupported-browser":
                c = $("#app-unsupported-browser-modal");
            }
            return c.modal(b);
        };

        b.prototype.popover = function(a, b) {
            b = b || "show";
            var c;
            switch (a) {
              case "permissions":
                c = $(".permission-dummy");
            }
            return c.popover(b);
        };

        b.prototype.connectionAlert = function(a) {
            var b = $('<div class="alert alert-danger peer-connection-alert" role="alert">'
                        +'<button type="button" class="close" data-dismiss="alert" aria-label="Close">'
                            +'<span aria-hidden="true">&times;</span>'
                        +'</button>Failed to connect to ' + a + "</div>").hide();

            $(".connection-alert-container").prepend(b);

            b.fadeIn(500);

            return setTimeout(function() {
                b.fadeOut(500, function() {
                    b.remove();
                });
            }, 4e3);
        };

        b.prototype.htmlEncode = function(a) {
            return $("<div/>").text(a).html();
        };

        b.prototype.htmlDecode = function(a) {
            return $("<div/>").html(a).text();
        };

        return b;
    }();
    a.Common = new c();
    var d = function() {
        function c() {
            $(document).on("click", ".tl-zoom", function() {
                var b = $(this).closest("li[data-peer-id]"),
                    c = b.attr("data-peer-id"),
                    d = "true" == String(b.attr("data-peer-share"));

                if ($(this).hasClass("btn-zoomout")) {
                    a.VideoUI.zoomOut();
                } else {
                    a.VideoUI.zoomIn(c, d);
                }
            });

            $(document).on("click", ".btn-down", function() {
                if ($(this).hasClass("active")) {
                    $(this).removeClass("active");
                    $("#main .video-wrapper").removeClass("hidesub");
                } else {
                    $(this).addClass("active");
                }
                $("#main .video-wrapper").addClass("hidesub");
            });
        }

        function d() {
            $("#main .video-wrapper").removeClass("zoom").removeClass("showsub");
            $("li.main").removeClass("main");
            $("li.sub").removeClass("sub").removeAttr("data-sub");
        }

        c.prototype.addVideo = function(a, c, d, e) {
            var f = this,
                g = $("#main .video-wrapper"),
                h = g.find('[data-peer-id="' + c + '"][data-id="' + a + '"][data-peer-share="true"]'),
                i = g.find('[data-peer-id="' + c + '"][data-id="' + a + '"][data-peer-share="false"]');

            if (e && h.size && h.size() > 0) {
                b.setVideoStream(h.find("video"), d);

                return new Promise(function(a, b) {
                    h.find("video").on("loadeddata", function() {
                        this.play();
                        a(this);
                    });
                });
            }

            if (!e && i.size && i.size() > 0) {
                b.setVideoStream(i.find("video"), d);

                return new Promise(function(a, b) {
                    i.find("video").on("loadeddata", function() {
                        this.play();
                        a(this);
                    });
                });
            }

            var j = $('<li data-peer-id="' + c + '" data-id="' + a + '" data-peer-share="' + e + '">'
                        +'<div class="tile">'
                            +'<video class="movie" autoplay id="video-' + a + '" class="video-' + c + '" />'
                            +'<div class="tl-share">'
                                +'<img src="img/icon/icon-share.png">'
                            +'</div>'
                            +'<div class="tl-label">'
                                +'<div class="tl-label-inner" style="display:none;">'
                                    +'<span class="user-name"></span>'
                                    +'<div class="tl-zoom">'
                                        +'<span><img src="../skyway/icon-zoomin.png"></span>'
                                    +'</div>'
                                +'</div>'
                            +'</div>'
                        +'</div>'
                    +'</li>');

            b.setVideoStream(j.find("video"), d);

            if (e) {
                j.find(".user-name").text("Shared Screen");
                j.find(".tl-label-inner").show();
            }

            if (g.hasClass("zoom")) {
                j.addClass("sub");

                var k = g.find("[data-sub]").map(function() {
                        return $(this).attr("data-sub");
                    }).get(),
                    l = Math.max.apply(null, k);

                j.attr("data-sub", l + 1);
            }

            if (g.find('li[data-peer-share="true"]').size && g.find('li[data-peer-share="true"]').size() > 0) {
                j.insertBefore(g.find('li[data-peer-share="true"]'));
                if (h.size() > 0) {
                    f.setShare(c, true);
                }

            } else {
                g.find("ul").append(j);
                j.attr("display", "none");
            }

            return new Promise(function(a, b) {
                j.find("video").on("loadeddata", function() {
                    j.removeAttr("display"), this.play(), a(this);
                });
            });
        };

        c.prototype.setUserName = function(a, b) {
            $("#main .video-wrapper ul")
                .find('[data-peer-id="' + a + '"][data-peer-share="false"] .user-name')
                .text(b);
        };

        c.prototype.setVideoSrc = function(a, c, d) {
            var e = $("#main .video-wrapper");

            if (a) {
                if (e.hasClass("zoom") && e.find('[data-peer-id="' + a + '"][data-peer-share="' + d + '"]').hasClass("main")) {
                    b.setVideoStream(e.find('[data-peer-id="' + a + '"][data-peer-share="' + d + '"] video'), c);
                } else {
                    b.setVideoStream(e.find('li[data-peer-id="' + a + '"][data-peer-share="' + d + '"] video'), c);
                }
            }
        };

        c.prototype.removeVideosByPeerId = function(a, b) {
            var c = $("#main .video-wrapper");

            if (a) {
                if (c.hasClass("zoom")) {
                    if (c.find('li[data-peer-id="' + a + '"][data-peer-share="' + b + '"]').hasClass("main")) {
                        d(), c.find('li[data-peer-id="' + a + '"][data-peer-share="' + b + '"]').remove();
                    } else {
                        c.find('li[data-peer-id="' + a + '"][data-peer-share="' + b + '"]').remove();
                        c.find("li.sub").removeAttr("data-sub").each(function(a, b) {
                            $(this).attr("data-sub", a);
                        });
                    }

                    if (0 === c.find(".sub").size()) {
                        d();
                    }
                } else {
                    c.find('li[data-peer-id="' + a + '"][data-peer-share="' + b + '"]').remove();
                }
            }
        };

        c.prototype.removeVideoById = function(a, b) {
            var c = $("#main .video-wrapper");

            if (a) {
                if (c.hasClass("zoom")) {
                    if (c.find('li[data-id="' + a + '"][data-peer-share="' + b + '"]').hasClass("main")) {
                        d();
                        c.find('li[data-id"' + a + '"][data-peer-share="' + b + '"]').remove();
                    } else {
                        c.find('li[data-id="' + a + '"][data-peer-share="' + b + '"]').remove();
                        c.find("li.sub").removeAttr("data-sub").each(function(a, b) {
                            $(this).attr("data-sub", a);
                        });
                    }

                    if (0 === c.find(".sub").size()) {
                        d();
                    }
                } else {
                    c.find('li[data-id="' + a + '"][data-peer-share="' + b + '"]').remove();
                }
            }
        };

        c.prototype.getVideoPeerIds = function() {
            return $('li[data-peer-id][data-peer-share="false"]').map(function() {
                return $(this).attr("data-peer-id");
            }).get();
        };

        c.prototype.getSharePeerIds = function() {
            return $('li[data-peer-id][data-peer-share="true"]').map(function() {
                return $(this).attr("data-peer-id");
            }).get();
        };

        c.prototype.getAllPeerIds = function() {
            var a = [],
                b = $("li[data-peer-id]").map(function() {
                    return $(this).attr("data-peer-id");
                }).get();

            $.each(b, function(b, c) {
                if (-1 === $.inArray(c, a)) {
                    a.push(c);
                }
            });

            return a;
        };

        c.prototype.setVideoMute = function(a, b) {
            var c = $("#main .video-wrapper"),
                d = c.find('[data-peer-id="' + a + '"][data-peer-share="false"] .tile');

            if (b) {
                d.addClass("no-screen");
            } else {
                d.removeClass("no-screen");
            }
        };

        c.prototype.setAudioMute = function(a, b) {
            var c = $("#main .video-wrapper"),
                d = c.find('[data-peer-id="' + a + '"][data-peer-share="false"] video');

            if (b) {
                d.prop("muted", true);
            } else {
                d.prop("muted", false);
            }
        };

        c.prototype.setShare = function(a, b) {
            var c = $("#main .video-wrapper"),
                d = c.find('[data-peer-id="' + a + '"][data-peer-share="false"] .tile');

            if (b) {
                d.addClass("share");
            } else {
                d.removeClass("share");
            }
        };

        c.prototype.setName = function(a, b) {
            var c = $("#main .video-wrapper"),
                d = c.find('[data-peer-id="' + a + '"][data-peer-share="false"] .user-name');

            if (b) {
                d.text(b).parent().show();
            } else {
                d.text("").parent().hide();
            }
        };

        c.prototype.setVideoReconnecting = function(a, b) {
            var c = $("#main .video-wrapper"),
                d = c.find('[data-peer-id="' + a + '"][data-peer-share="false"] .tile');

            if (b) {
                d.addClass("reconnecting");
            } else {
                d.removeClass("reconnecting");
            }
        };

        c.prototype.setShareReconnecting = function(a, b) {
            var c = $("#main .video-wrapper"),
                d = c.find('[data-peer-id="' + a + '"][data-peer-share="true"] .tile');

            if (b) {
                d.addClass("reconnecting");
            } else {
                d.removeClass("reconnecting");
            }
        };

        c.prototype.zoomIn = function(a, b) {
            b = !!b;

            var c = $("#main .video-wrapper");

            c.addClass("zoom").addClass("showsub");
            c.find("li.main").removeClass("main");

            $("li[data-peer-id=" + a + "][data-peer-share=" + b + "]")
                .addClass("main")
                .removeClass("sub")
                .removeAttr("data-sub");


            c.find("li[data-peer-id]:not(.main)").addClass("sub");
            c.find("li.sub").removeAttr("data-sub").each(function(a, b) {
                $(b).attr("data-sub", a);
            });

            var d = c.find("li[data-peer-id]:not(.main)").size();

            c.attr("data-video-count", d);
        };

        c.prototype.zoomOut = function(a, b) {
            b = !!b;

            var c = $("#main .video-wrapper");

            c.addClass("zoom").addClass("showsub");
            c.find("li.main").removeClass("main");

            $("li[data-peer-id=" + a + "][data-peer-share=" + b + "]").addClass("main").removeClass("sub").removeAttr("data-sub");

            c.find("li[data-peer-id]:not(.main)").addClass("sub");
            c.find("li.sub").removeAttr("data-sub").each(function(a, b) {
                $(b).attr("data-sub", a);
            });

            var d = c.find("li[data-peer-id]:not(.main)").size();

            c.attr("data-video-count", d);
        };

        c.prototype.zoomOut = function() {
            var a = $("#main .video-wrapper");

            d();
            a.removeAttr("data-video-count");
        };

        return c;
    }();

    a.VideoUI = new d();
    var e = function() {
        function b() {
            var b = this;

            $(document).on("keydown", "#send-message", function(a) {
                false === a.shiftKey && 13 === a.which && (a.preventDefault(), $("#message").trigger("submit"));
            });

            $("#message").on("submit", function(c) {
                if (c.preventDefault(), $("#send-message").val()) {
                    var d = a.Common.htmlEncode($("#send-message").val());
                    $("#send-message").val(""), b.emit("messageSubmitted", d);
                }
            });
        }

        b.prototype = new EventEmitter2();

        b.prototype.addMessage = function(a, b, c, d, e, f) {
            var g = $(".chat-list"),
                h = false,
                i = e;

            if (i) {
                if (i.match(/\r\n|\n/g)) {
                    i = i.replace(/(\r\n)|(\n)/g, "<br>");
                }

                if (c) {
                    if (/^img\/chat-icon\/\w+\.svg$/.test(c)) {
                        h = true;
                    }
                } else {
                    c = "img/chat-icon/" + a.replace(/[\w\d]{6}R_/, "") + ".svg";
                }

                h = true;

                var j = d.toLocaleTimeString(),
                    k = "";

                k += '<div class="media new ' + (f ? "own" : "") + '">';
                k += '<a class="clip-circle smaller media-left"><div class="icon' + (h ? " sign" : "") + '" style="background-image: url(' + c + ')"></div></a>';
                k += '<div class="media-body">';
                k += '<h4 class="media-heading">';
                k += b;
                k += '<small class="pull-right">' + j + "</small>";
                k += "</h4>";
                k += i;
                k += "</div>";
                k += "</div>";

                g.append(k);

                g.stop(true, true).animate({
                    scrollTop: g[0].scrollHeight
                }, "slow");
            }
        };

        return b;
    }();

    a.ChatUI = new e();

    var f = function() {
        function c() {
            var b = this;

            this.videoEnabled = true;
            this.audioEnabled = true;
            this.video = $("#user-information video")[0];

            $(document).on("keypress", "#input-name input", function(a) {
                if (13 == a.which) {
                    a.preventDefault();
                    $(this).blur();
                }
            });

            $(document).on("click", "#input-name .input-group-addon", function(a) {
                $("#input-name input").focus();
            });

            $(document).on("focusout", "#input-name input", function() {
                $("#input-name").trigger("submit");
            });

            $(document).on("submit", "#input-name", function(a) {
                a.preventDefault();

                var c = $('input[name="userName"]').val();

                b.emit("nameUpdated", c);
            });

            $(document).on("click", ".guide-reload-btn", function() {
                location.reload();
            });

            $("#app-mic-control img").on("click", function() {
                if ($(this).is(".active")) {
                    $(this).removeClass("active");
                    $("#user-information video").prop("muted", true);

                    b.emit("micEnabled");
                    b.audioEnabled = true;

                } else {
                    $(this).addClass("active");
                    $("#user-information video").prop("muted", false);

                    b.emit("micDisabled");
                    b.audioEnabled = false;
                }
            });

            $("#app-video-control img").on("click", function() {
                if ($(this).is(".active")) {
                    $(this).removeClass("active");
                    $(".user-photo").removeClass("active");
                    b.emit("videoEnabled");
                    b.videoEnabled = true;
                } else {
                    $(this).addClass("active");
                    $(".user-photo").addClass("active");
                    b.emit("videoDisabled");
                    b.videoEnabled = false;
                }
            });

            $(document).on("click", "#app-share-control img", function() {
                if ($(this).is(".active")) {
                    $(this).removeClass("active");
                    b.emit("screenShareEnded");
                } else {
                    $(this).addClass("active");
                    b.emit("screenShareStarted");
                }
            });

            $("#app-invite").on("click", function() {
                a.Common.modal("invite");
            });

            $("#app-invite-modal").on("show.bs.modal", function(b) {
                var c = $(this),
                    d = "mailto:?subject=" + a.Subject + "&body=" + a.Message;

                c.find("#invite-url").val(a.Url);
                c.find("#app-copy").attr("data-clipboard-text", a.Url);
                c.find("#invite-mail").attr("href", d);
            });
        }

        c.prototype = new EventEmitter2();

        c.prototype.setVideoSrc = function(a, c) {
            var d = b.setVideoStream($(".user-video video.movie"), a);
            if (c) {
                d.addClass("screen-share");
            } else {
                d.removeClass("screen-share");
            }
        };

        c.prototype.setName = function(a) {
            $('input[name="userName"]').val(a);
        };

        c.prototype.activateShareIcon = function() {
            $("#app-share-control").addClass("share");
            $("#user-information .icon-share").show(0);
        };

        c.prototype.disableShareIcon = function() {
            var a = $("#app-share-control");

            a.removeClass("share");
            a.find(".active").removeClass("active");
            $("#user-information .icon-share").hide(0);
        };

        return c;
    }();
    a.SelfUI = new f();
}(RoomUI || (RoomUI = {}));

$(document).on("ready", function() {
    new ZeroClipboard(document.getElementById("app-copy")).on("ready", function() {});
});
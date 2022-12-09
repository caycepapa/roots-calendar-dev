"use strict";

import { gsap } from "gsap";

/*
loading
----------------------------------------------------------*/
export default function(){

    var handler = (function () {
        var events = {},
        key = 0;
        return {
            addListener: function (target, type, listener, capture) {
            if (window.addEventListener) {
                target.addEventListener(type, listener, capture);
            } else if (window.attachEvent) {
                target.attachEvent('on' + type, listener);
            }
            events[key] = {
                target: target,
                type: type,
                listener: listener,
                capture: capture
            };
            return key++;
            },
            removeListener: function (key) {
            if (key in events) {
                var e = events[key];
                if (window.removeEventListener) {
                e.target.removeEventListener(e.type, e.listener, e.capture);
                } else if (window.detachEvent) {
                e.target.detachEvent('on' + e.type, e.listener);
                }
            }
            }
        };
    })();


    var page = {
        init: function () {
            if(!device.mobile()){
                mouse.addHoverEvents();
                mouse.addHoverOutEvents();
            }
        }
    }

    var mouse ={

        addHoverEvents: function () {

            var hover = document.querySelectorAll('a');
            for (var i = 0; i < hover.length; i++) {
                handler.removeListener(hover[i]);
                handler.addListener(hover[i], 'mouseenter', function (e) {
                    this.classList.add('is-hover');
                }, false);
                handler.addListener(hover[i], 'mouseleave', function (e) {
                    this.classList.remove('is-hover');
                }, false);
            }

            var btnHover = document.querySelectorAll('button');
            for (var i = 0; i < btnHover.length; i++) {
                handler.removeListener(btnHover[i]);
                handler.addListener(btnHover[i], 'mouseenter', function (e) {
                    this.classList.add('is-hover');
                }, false);
                handler.addListener(btnHover[i], 'mouseleave', function (e) {
                    this.classList.remove('is-hover');
                }, false);
            }

        },

        addHoverOutEvents: function () {

            var hoverOut = document.querySelectorAll('a');
            for (var i = 0; i < hoverOut.length; i++) {
                handler.removeListener(hoverOut[i]);
                handler.addListener(hoverOut[i], 'mouseleave', function (e) {
                    this.classList.add('is-hoverOut');
                }, false);
                handler.addListener(hoverOut[i], 'mouseenter', function (e) {
                    this.classList.remove('is-hoverOut');
                }, false);
            }

        }
    }

    page.init();

}
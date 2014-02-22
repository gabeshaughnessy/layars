
/*Copyright (c) 2012, Layar B.V.
 All rights reserved.

 Redistribution and use in source and binary forms, with or without
 modification, are permitted provided that the following conditions are met:
    *Redistributions of source code must retain the above copyright
     notice, this list of conditions and the following disclaimer.
    *Redistributions in binary form must reproduce the above copyright
     notice, this list of conditions and the following disclaimer in the
     documentation and/or other materials provided with the distribution.
    *Neither the name of the <organization> nor the
     names of its contributors may be used to endorse or promote products
     derived from this software without specific prior written permission.

 THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 ARE DISCLAIMED. IN NO EVENT SHALL LAYAR B.V BE LIABLE FOR ANY DIRECT,
 INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/
/*jshint globalstrict:true*/
/*globals Swipe*/
'use strict';

function Carousel(container) {
    this.carousel   = container;
    this.wrap       = container.parentNode;
    this.list       = container.getElementsByTagName('ul')[0];
    this.images     = container.getElementsByTagName('img');
    this.demo       = document.getElementById('swipedemo');

    this.startSlide = 0;
    this.width      = 0;
    this.height     = 0;

    window.onerror = this.showlog;

    this.inCreator = (top !== window);

    this.registerCarouselDimensions();
    this.handleDetachedCarousel();
    this.setCarouselDimensions();

    if (this.inCreator) {
        this.handleIndexMessage();
    } else {
        this.initCarousel();
        this.setDetachLinkHref(null, this.startSlide);
    }
}

Carousel.prototype = {

    showlog: function(varArgs) {
        var container = document.getElementById('log');
        for (var i = 0, m = arguments.length; i < m; i++) {
            container.innerHTML += arguments[i] + ', ';
        }
        container.innerHTML += '<br>';
    },

    initCarousel: function() {
        var self = this;

        this.swipe = new Swipe(this.carousel, {
            startSlide: this.startSlide,
            callback  : this.setDetachLinkHref
        });

        if (!this.detached) {
            this.startDemoAnims();
            // Stop animations when touching starts, when stopping any later,
            // the animations reset swiping.
            this.images[0].ontouchstart = function() {
                if (self.stopDemoAnims) {
                    self.stopDemoAnims();
                    self.stopDemoAnims = null;
                }
            };
        }
    },

    setDetachLinkHref: function(e, pos) {
        var detachLink = document.getElementById('detach');
        if (detachLink) {
            detachLink.setAttribute('href', '?detached=' + pos);
        }
    },

    handleDetachedCarousel: function() {
        this.detached = window.location.search.match(/detached=([0-9]+)/);
        if (this.detached) {
            this.startSlide = parseInt(this.detached[1], 10);
            this.fitCarouselInWindow();
            this.stopDemoAnims();
        }
    },

    handleIndexMessage: function() {
        if ('addEventListener' in window) {
            window.addEventListener('message', function(message) {
                this.startSlide = message.data;
                Swipe.prototype.slide.call({
                    element: this.carousel,
                    width  : this.width
                }, this.startSlide, 0);
                this.positionSwipeDemo();
            }.bind(this), false);
        }
    },

    getViewport: function() {
        // Android has a different viewport system than iOS and desktop browsers
        if (window.navigator.userAgent.match(/android/gi)) {
            return {
                width : window.outerWidth,
                height: window.outerHeight
            };
        }

        // Others
        return {
            width : document.documentElement.clientWidth,
            height: document.documentElement.clientHeight
        };
    },

    fitCarouselInWindow: function() {
        var scale, viewport = this.getViewport();

        scale = Math.min(
            viewport.width / this.width,
            viewport.height / this.height
        );

        for (var i = 0, m = this.images.length; i < m; i++) {
            var image = this.images[i];
            image.width *= scale;
            image.height *= scale;
        }

        this.width *= scale;
        this.height = viewport.height;
    },

    registerCarouselDimensions: function() {
        for (var i = 0, m = this.images.length; i < m; i++) {
            var width = parseInt(this.images[i].getAttribute('width'), 10),
                height = parseInt(this.images[i].getAttribute('height'), 10);
            if (width > this.width) {
                this.width = width;
            }
            if (height > this.height) {
                this.height = height;
            }
        }
    },

    setCarouselDimensions: function() {
        this.positionSwipeDemo();

        this.wrap.style.width = this.carousel.style.width = this.width + 'px';
        this.wrap.style.height = this.carousel.style.height = this.height + 'px';

        for (var i = 0, m = this.images.length; i < m; i++) {
            var slideStyle = this.images[i].parentNode.style;
            slideStyle.minWidth = this.width + 'px';
            slideStyle.minHeight = this.height + 'px';
            slideStyle.lineHeight = this.height + 'px';
        }
    },

    positionSwipeDemo: function() {
        var style = this.demo.style,
            image = this.images[this.startSlide];
        style.right  = (this.width - image.getAttribute('width')) / 2 + 'px';
        style.bottom = (this.height - image.getAttribute('height')) / 2 + 'px';
    },

    startDemoAnims: function() {
        this.list.className += 'animate';
        this.demo.className += 'animate';
    },

    stopDemoAnims: function() {
        this.list.className = '';
        this.demo.style.display = 'none';
    }

};

window.carousel = new Carousel(
    document.getElementById('carousel')
);
+function ($) {
    'use strict';

    var deepExtend = function(destination, source) {
        var property;
        for (property in source) {
            if (source[property] && source[property].constructor &&
                source[property].constructor === Object) {
                destination[property] = destination[property] || {};
                deepExtend(destination[property], source[property]);
            } else {
                destination[property] = source[property];
            }
        }

        return destination;
    };

    var LazyLoad = function (widget) {
        if ('onscreen' != widget.options.visibility || !widget.$element.is(':visible')) {
            return;
        }

        new Waypoint({
            element: widget.$element[0],
            offset: '200%',
            handler: function () {
                if ('away' === widget.options.visibility) {
                    return;
                }

                widget.load({options: {visibility: 'away'}});
            }
        });
    };

    var AutoRefresh = function (widget) {

        if (!widget.isLoaded()) {
            return;
        }

        if (!widget.options.auto_refresh) {
            return;
        }

        // should not show loading indicator on auto refresh widget.
        widget.control.mask.mode = 'none';

        // will re init when content loaded
        // set and then clear, use setTimeout insteadof setInterval ... we need to wait until loaded
        // http://stackoverflow.com/questions/729921
        var autoRefreshInterval;

        if ('onscreen' === widget.options.auto_refresh) {
            if(!widget.$element.is(':visible')){
                return;
            }

            new Waypoint.Inview({
                element: widget.$element[0],
                entered: function () {
                    autoRefreshInterval = setTimeout(function () {
                        widget.load({});
                        clearTimeout(autoRefreshInterval);
                    }, widget.options.auto_refresh_timer);
                },
                exited: function () {
                    clearTimeout(autoRefreshInterval);
                }
            });
        } else {
            autoRefreshInterval = setTimeout(function () {
                widget.load({});
                clearTimeout(autoRefreshInterval);
            }, widget.options.auto_refresh_timer);
        }
    };

    var Widget = function (element, option) {
        /**
         * @property {object} $element
         * @property {string} name
         * @property {object} options
         * @property {object} remote
         */
        this.$element = $(element);
        this.name = this.$element.data('widget-name');
        this.options = this.$element.data('widget-options');
        this.remote = this.options.remote || {};
        this.control = deepExtend(option, this.options.control || {});
        this.$ticker = null;
        this.$mask = null;

        // unset some options for submitting
        delete this.options.remote;
        delete this.options.control;

        new LazyLoad(this);
        new AutoRefresh(this);

        this.$element.on('click', '[data-widget-reload]', $.proxy(function (e) {
            e.preventDefault();
            this.$ticker = $(e.target);
            this.load.call(this, deepExtend({options: this.$ticker.data('widget-reload') || {}}, {mode: 'reload'}));
        }, this));

        this.$element.on('click', '[data-widget-more]', $.proxy(function (e) {
            e.preventDefault();
            this.$ticker = $(e.target);
            this.load.call(this, deepExtend({options: this.$ticker.data('widget-more') || {}}, {mode: 'more'}));
        }, this));

        this.$element.on('click', '[data-widget-pull]', $.proxy(function (e) {
            e.preventDefault();
            this.$ticker = $(e.target);
            this.load.call(this, deepExtend({options: this.$ticker.data('widget-pull') || {}}, {mode: 'pull'}));
        }, this));

        this.$element.on('click', '[data-widget-prev]', $.proxy(function (e) {
            e.preventDefault();
            this.$ticker = $(e.target);
            this.load.call(this, deepExtend({options: this.$ticker.data('widget-prev') || {}}, {mode: 'prev'}));
        }, this));

        this.$element.on('click', '[data-widget-next]', $.proxy(function (e) {
            e.preventDefault();
            this.$ticker = $(e.target);
            this.load.call(this, deepExtend({options: this.$ticker.data('widget-next') || {}}, {mode: 'next'}));
        }, this));

        this.$element.on('click', '[data-widget-submit]', $.proxy(function (e) {
            e.preventDefault();
            this.$ticker = $(e.target);
            this.submit.call(this);
        }, this));
    };

    Widget.DEFAULTS = {
        mask: {
            mode: 'over', // none | clear | over | ticker | .selector
            style: 'wg-loading' // wg-loading | wg-loading--double | wg-loading-pulse
        }
    };

    Widget.prototype.mask = function (action) {
        this.$mask = this.$element;
        var style = this.control.mask.style;
        var mode = this.control.mask.mode;

        if ('none' === mode) {
            return;
        }

        if ('hide' === action) {
            if (this.$mask.is('button,a')) {
                this.$mask.attr('disabled', false).removeClass('disabled');
            }

            this.$mask.find('.wg-mask').remove();
            return;
        }

        switch (mode) {
            case 'clear':
                this.$mask.html($('<div class="wg-mask wg-mask--clear"/>'));
                break;
            case 'over':
                this.$mask
                // may effect to child style ??
                // if effect calculate mask position with $element offset
                    .css('position', 'relative')
                    .append(
                        $('<div class="wg-mask wg-mask--over"/>').css({
                            'position': 'absolute', 'z-index': 1000,
                            'top': 0, 'bottom': 0, 'left': 0, 'right': 0,
                            'with': '100%', 'height': '100%'
                        })
                    )
                ;
                break;
            case 'ticker':
                this.$mask = this.$ticker;
                break;
            default:
                this.$mask = this.$element.find(this.control.mask.mode);
        }

        var $masking = this.$mask.find('.wg-mask');

        if (!$masking.length) {
            $masking = $('<div class="wg-mask wg-mask--' + mode + '"/>');

            if (this.$mask.is('button,a')) {
                this.$mask.attr('disabled', true).addClass('disabled');
                this.$mask.prepend($masking);
            } else {
                this.$mask.html($masking);
            }
        }

        $masking.html($('<div/>').addClass(style));
    };

    Widget.prototype.isLoading = function () {
        return !!this.$element.find('.wg-mask').length;
    };

    Widget.prototype.isLoaded = function () {
        return this.$element.hasClass('wg-toro--loaded');
    };

    Widget.prototype.load = function (opt) {
        if (this.isLoading()) {
            return;
        }

        var me = this;
        var mode = opt.mode || 'clear';
        var callback = (opt.options || {}).callback || {};

        var success = opt.success || window[callback.success] || function (response) {
                var $response = $(response);

                var $content = this.$element.find('.wg-container').html();

                switch (mode) {
                    case 'pull':
                        $response.find('.wg-container').append($content);
                        break;
                    case 'more':
                        $response.find('.wg-container').prepend($content);
                        break;
                    case 'prev':
                    case 'next':
                        $response.hide();
                        break;
                    default:
                        $content = null;
                }

                this.$element.replaceWith($response);
                this.$element = $response;
                this.$element.addClass('wg-toro--loaded');

                if(!this.$element.is(":visible")){
                    this.$element.fadeIn();
                }

                Plugin.call(this.$element);

                $(document).trigger('dom-node-inserted', [this.$element]);
            };

        var error = opt.error || window[callback.error] || function () {
                this.$element.replaceWith('<div class="wg-error"></div>');
            };

        this.$element.removeClass('wg-toro--loaded');
        me.mask('show');

        $.ajax({
            url: this.remote.url,
            type: this.remote.method || 'GET',
            data: {
                widget: {
                    name: this.name,
                    options: deepExtend(this.options, opt.options || {})
                }
            },
            error: function (response) {
                me.mask('hide');
                error.call(me, response);
            },
            success: function (response) {
                me.mask('hide');
                success.call(me, response);
            }
        });
    };

    // slick extension
    // TODO: slick pull to refresh (or when slide first page)
    Widget.prototype.slick = function (slick, options) {
        var me = this;
        var defaultOptions = deepExtend({
            lazyLoad: true,
            instantLoad: false,
            distancePageLoad: 2,
            slickInstantContainer: '.slick-active',
            slickContainer: '.wg-container',
            paginate: true
        }, options || {});

        sessionStorage.setItem('toro-current-slick-' + slick.instanceUid, slick.currentSlide);

        if (true === defaultOptions.instantLoad) {
            this.load({
                success: function (response) {
                    var $container = me.$element.find(defaultOptions.slickInstantContainer);

                    $container.html($(response).find('.wg-container').html());

                    $(document).trigger('dom-node-inserted', [$container]);
                }
            });

            return;
        }

        if (true === defaultOptions.lazyLoad) {
            var rows = slick.options.rows, slidesToShow = slick.options.slidesToShow, slidesPerPage = rows * slidesToShow;
            var slidedItems = (slick.currentSlide * rows) + (rows * slidesToShow);
            var currentPage = slidedItems / slidesPerPage;
            var totalItems = this.$element.find('.slick-slide').length * rows;
            var totalPages = parseInt(totalItems / slidesPerPage);

            // total pages from server side
            if (defaultOptions.paginate && this.options['total_page'] > 1) {
                if (!slick.$slider.find('.slick-slider-paginate').length) {
                    slick.$slider.prepend($(
                        '<div class="slick-slider-paginate">' +
                        '   <span class="current-page"></span>' +
                        '   <span class="separate-page">/</span>' +
                        '   <span class="total-page"></span>' +
                        '</div>'
                    ));
                }

                var paginator = slick.$slider.find('.slick-slider-paginate');

                paginator.find('.current-page').html(currentPage);
                paginator.find('.total-page').html(Math.ceil(defaultOptions.paginate / slidesPerPage));
            }

            if (0 !== slick.currentDirection || ((totalPages - currentPage) > defaultOptions.distancePageLoad)) {
                return;
            }

            if (this.options['current_page'] == this.options['total_page']) {
                return;
            }

            this.options['page']++;

            this.load({
                success: function (response) {
                    slick.addSlide($(response).find(defaultOptions.slickContainer).html());
                    $(document).trigger('dom-node-inserted', [me.$element]);
                }
            });
        }
    };

    Widget.prototype.submit = function () {
        // TODO;
    };

    // PLUGIN DEFINITION
    function Plugin(option) {
        return this.each(function () {
            var $this = $(this);
            var data = $this.data('twidget');
            var options = deepExtend({}, Widget.DEFAULTS, typeof option == 'object' && option);

            if (!data) $this.data('twidget', new Widget(this, options));
        })
    }

    window['_TORO_WIDGET_ASSETS_'] = {};
    var loadScript = function (href) {
        if (_TORO_WIDGET_ASSETS_[href]) {
            return;
        }

        _TORO_WIDGET_ASSETS_[href] = true;
        $.getScript(href);
    }

    var loadStyle = function (href) {
        if (_TORO_WIDGET_ASSETS_[href]) {
            return;
        }

        _TORO_WIDGET_ASSETS_[href] = true;

        var $d = $.Deferred();
        var $link = $('<link/>', {
            rel: 'stylesheet',
            type: 'text/css',
            href: href
        }).appendTo('head');

        $d.resolve($link);

        return $d.promise();
    }

    var old = $.fn.twidget;
    $.fn.twidget = Plugin;
    $.fn.twidget.Constructor = Widget;

    // NO CONFLICT
    $.fn.twidget.noConflict = function () {
        $.fn.twidget = old;
        return this;
    };

    // DATA-API
    $('[data-widget-name]').each(function () {
        var $this = $(this);

        if ($this.data('widget-style')) {
            loadStyle($this.data('widget-style'));
        }

        if ($this.data('widget-script')) {
            loadScript($this.data('widget-script'));
        }

        Plugin.call($this);
    });
}($);

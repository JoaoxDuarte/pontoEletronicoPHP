/*
 * Denis Lins dialoG
 *
 * dlg by Denis Lins <denislins@hotmail.com>
 *
 * A very nice dialog plugin for jQuery
 *
 * More information:
 *    http://blog.denislins.com.br/2010/09/06/dlg-jquery-dialog-plugin-alert-confirm-prompt/
 *
 * Copyright (c) 2010 Denis Lins
 * Licensed under the MIT License:
 *   http://www.opensource.org/licenses/mit-license.php
 */

(function ($)
{

    var DLDialog = function ()
    {};

    $.extend(DLDialog.prototype, {
        construct: function (elem, options)
        {
            this.options = options;
            this.elem = elem;

            this.init();

            return this;
        },
        init: function ()
        {
            var self = this;

            this.options = $.extend({
                type: 'alert',
                content: '',
                title: 'Aviso',
                okText: 'Ok',
                cancelText: 'Cancel',
                css: {
                    width: 300,
                    height: 'auto'
                },
                easeIn: 'easeOutBack',
                easeOut: 'easeInBack',
                speedIn: 500,
                speedOut: 500,
                onComplete: function ()
                {},
                focusButton: false,
                maskCloseDialog: false,
                drag: false,
                maxlength: false
            }, this.options);

            this.$content = $('#dlgContent').size() == 1 ? $('#dlgContent') : $('<div id="dlgContent"></div>').appendTo('body').css(this.options.css);
            this.$mask = $('#dlgMask').size() == 1 ? $('#dlgMask') : $('<div id="dlgMask"></div>').appendTo('body');

            if (this.elem !== false)
            {
                $(this.elem).bind('click', function (e)
                {
                    e.preventDefault();
                    self.showDialog();
                });
            }
        },
        isActive: function ()
        {
            return $('#dlgMask:visible').size() > 0 || $('#dlgContent:visible').size() > 0;
        },
        bindReposition: function ()
        {
            var self = this;

            $(document).bind('scroll resize', {self: this}, this.repositionContent);
            $(window).bind('scroll resize', {self: this}, this.repositionContent);

            return this;
        },
        updateTitle: function ()
        {
            if ($('h4', this.$content).size() <= 0)
            {
                $('<h4 />').text(this.options.title).prependTo(this.$content);
            }

            return this;
        },
        updateContent: function ()
        {
            this.$content.css({
                left: this.getPosLeft(),
                top: 0
            })
                .html('<div>' + this.options.content + '</div>')
                .append('<p id="dlgCloseButton"><a href="ok">' + this.options.okText + '</a></p>');

            return this;
        },
        createMask: function ()
        {
            var self = this;

            this.$mask.css({
                opacity: 0.7,
                width: $(document).width(),
                height: $(document).height()
            })
                .fadeIn(this.options.speedIn, function ()
                {
                    if (self.options.maskCloseDialog === true)
                    {
                        self.$mask.bind('click', {self: this}, this.closeDialog);
                    }
                });

            return this;
        },
        showContent: function ()
        {
            var self = this;

            this.$content.css('opacity', 0)
                .show()
                .animate({
                    top: this.getPosTop(),
                    opacity: 1
                }, this.options.speedIn, this.options.easeIn, function ()
                {
                    if ($.browser.msie)
                    {
                        $(this).get(0).style.removeAttribute('filter');
                    }
                    if (self.options.focusButton == 'ok')
                    {
                        $('#dlgCloseButton a').eq(0).focus();
                    }
                    else if (self.options.focusButton == 'cancel')
                    {
                        $('#dlgCloseButton a').eq(1).focus();
                    }
                });
        },
        addCancelButton: function ()
        {
            $('#dlgCloseButton').append(' <a href="cancel">' + this.options.cancelText + '</a>');
            return this;
        },
        addTextInput: function ()
        {
            $('#dlgContent div').append('<p><input type="text" id="dlgText" /></p>');

            if (isNaN(this.options.maxlength) === false && this.options.maxlength > 0)
            {
                $('#dlgText').attr('maxlength', this.options.maxlength);
            }
        },
        showDialog: function ()
        {
            if (this.isActive() === true)
            {
                return false;
            }

            var self = this;

            this.bindReposition().updateContent().updateTitle();

            if (this.options.type == 'confirm')
            {
                this.addCancelButton();
            }
            else if (this.options.type == 'prompt')
            {
                this.addCancelButton().addTextInput();
            }

            if (this.options.drag === true)
            {
                $('h4', this.$content).css('cursor', 'move');
                this.$content.draggable({handle: 'h4'});
            }

            this.createMask().showContent();

            $('#dlgCloseButton a').bind('click', {self: this}, this.closeDialog);
        },
        unbindReposition: function ()
        {
            $(window).unbind('scroll resize', this.repositionContent);
            $(document).unbind('scroll resize', this.repositionContent);

            return this;
        },
        destroyMask: function ()
        {
            var self = this;

            this.$mask.delay(this.options.speedOut / 2).fadeOut(this.options.speedOut, function ()
            {
                if (self.options.type == 'prompt' || self.options.type == 'confirm')
                {
                    self.options.onComplete(self.response);
                }
                else
                {
                    self.options.onComplete();
                }

                $(this).unbind();
            });

            return this;
        },
        hideContent: function ()
        {
            var self = this;

            this.$content.stop()
                .animate({
                    left: this.getPosLeft(),
                    top: this.getPosTop() - $(document).scrollTop() - $(window).height(),
                    opacity: 0
                }, this.options.speedOut, this.options.easeOut, function ()
                {
                    self.$content.hide();
                });

            $('#dlgCloseButton a').unbind();
        },
        getResponse: function (response, input)
        {
            var resposta
            =
                if (this.options.type == 'confirm')
            {
                this.response = (response.indexOf("ok") > -1);
            }
            else if (this.options.type == 'prompt')
            {
                if ((response.indexOf("cancel") > -1))
                {
                    this.response = null;
                }
                else
                {
                    this.response = input.length > 0 ? input : null;
                }
            }
        },
        closeDialog: function (e)
        {
            e.preventDefault();

            var self = e.data.self;

            if (self.isActive() === false)
            {
                return false;
            }

            if (self.options.drag === true)
            {
                self.$content.draggable('destroy');
                $('h4', self.$content).css('cursor', 'auto');
            }

            if (self.options.type == 'confirm')
            {
                self.getResponse($(this).attr('href').toLowerCase());
            }
            else if (self.options.type == 'prompt')
            {
                self.getResponse($(this).attr('href').toLowerCase(), $('input#dlgText').val());
            }

            self.unbindReposition().destroyMask().hideContent();
        },
        repositionContent: function (e)
        {
            var self = e.data.self;

            if (self.$content.is(':visible'))
            {
                self.$content.stop()
                    .animate({
                        left: self.getPosLeft(),
                        top: self.getPosTop(),
                        opacity: 1
                    }, self.options.speedIn, self.options.easeIn);
            }
        },
        getPosLeft: function ()
        {
            return $(window).width() / 2 - this.$content.width() / 2 - parseInt(this.$content.css('paddingLeft')) + $(document).scrollLeft();
        },
        getPosTop: function ()
        {
            return $(window).height() / 2 - this.$content.height() / 2 - parseInt(this.$content.css('paddingTop')) + $(document).scrollTop();
        }

    });

    $.dlg = function (options)
    {
        new DLDialog().construct(false, options).showDialog();
    };

    $.fn.extend({
        dlg: function (options)
        {
            new DLDialog().construct(this, options);
        }

    });

})(jQuery);


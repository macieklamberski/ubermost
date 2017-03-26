var $ = require('jquery')
var Velocity = require('velocity-animate')
var ImagesLoaded = require('imagesloaded')
var Handlebars = require('handlebars')

const Sizes = {
    $trigger: $('.dropdown'),
    $selected: $('.dropdown span'),
    $overlay: $('#sizes-overlay'),
    $input: $('form input[name="size_id"]'),

    init() {
        Sizes.bindOpenOverlay()
        Sizes.bindSelectSize()

        if (!Sizes.$input.val()) {
            Sizes.detectInitialResolution()
        }
    },

    bindOpenOverlay() {
        Sizes.$trigger.on('click', function (event) {
            Helper.openOverlay(Sizes.$overlay)
            event.preventDefault()
        })
    },

    bindSelectSize() {
        Sizes.$overlay.find('label').on('click', function (event) {
            var $self = $(this)
            Sizes.$input.val($('input', $self).val())
            Sizes.$selected.html($('span', $self).html())
            Helper.closeOverlay(Sizes.$overlay)
            event.preventDefault()
        })
    },

    detectInitialResolution() {
        var screenX = screen.width
        var screenY = screen.height
        var $matched = null;

        // Check if there's option with exact size.
        $('label[data-width="' + screenX + '"][data-height="' + screenY + '"]').each(function () {
            $matched = $(this)
        })

        // Check if there's option with the same ratio.
        if (!$matched) {
            var $sizes = $('label[data-ratio="' + (screenX / screenY).toFixed(1) + '"]')

            $sizes.each(function () {
                var $self = $(this)
                if ($self.data('width') > screenX && $self.data('height') > screenY) {
                    if (!$matched || $self.data('width') < $matched.data('width') && $self.data('height') < $matched.data('height')) {
                        $matched = $self;
                    }
                }
            })
        }

        // Match any resolution that is bigger.
        if (!$matched) {
            var $sizes = $('label[data-ratio]')

            $sizes.each(function () {
                var $self = $(this)
                if ($self.data('width') > screenX && $self.data('height') > screenY) {
                    if (!$matched || $self.data('width') < $matched.data('width') && $self.data('height') < $matched.data('height')) {
                        $matched = $self;
                    }
                }
            })
        }

        if ($matched) {
            $matched.trigger('click')
            Sizes.$selected.find('em').remove()
            Sizes.$selected.append(Sizes.$selected.data('detected'))
        }
    }
}

export default Sizes

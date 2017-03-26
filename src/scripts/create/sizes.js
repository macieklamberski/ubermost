import $ from 'jquery'
import Velocity from 'velocity-animate'
import ImagesLoaded from 'imagesloaded'
import Handlebars from 'handlebars'
import Helper from './helper'

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
        Sizes.$trigger.on('click', (event) => {
            Helper.openOverlay(Sizes.$overlay)
            event.preventDefault()
        })
    },

    bindSelectSize() {
        Sizes.$overlay.find('label').on('click', function (event) {
            const $self = $(this)
            Sizes.$input.val($('input', $self).val())
            Sizes.$selected.html($('span', $self).html())
            Helper.closeOverlay(Sizes.$overlay)
            event.preventDefault()
        })
    },

    detectInitialResolution() {
        const screenX = screen.width
        const screenY = screen.height
        let $matched = null

        // Check if there's option with exact size.
        $(`label[data-width="${screenX}"][data-height="${screenY}"]`).each((index, value) => {
            $matched = $(value)
        })

        // Check if there's option with the same ratio.
        if (!$matched) {
            const $sizes = $(`label[data-ratio="${(screenX / screenY).toFixed(1)}"]`)

            $sizes.each((index, value) => {
                const $self = $(value)
                if ($self.data('width') > screenX && $self.data('height') > screenY) {
                    if (!$matched || $self.data('width') < $matched.data('width') && $self.data('height') < $matched.data('height')) {
                        $matched = $self;
                    }
                }
            })
        }

        // Match any resolution that is bigger.
        if (!$matched) {
            const $sizes = $('label[data-ratio]')

            $sizes.each((index, value) => {
                var $self = $(value)
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

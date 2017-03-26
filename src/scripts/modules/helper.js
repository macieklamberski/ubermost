import $ from 'jquery'
import Handlebars from 'handlebars'
import Application from './application'

const Helper = {
    compileTemplate(id, data) {
        var template = Handlebars.compile($('#' + id + '-template').html())
        return template(data)
    },

    openOverlay($overlay, callback) {
        Application.$BODY.css('overflow', 'hidden')
        $overlay.addClass('overlay--open')
        $overlay.find('.overlay__close').on('click', function (event) {
            Helper.closeOverlay($overlay)
            event.preventDefault()
        })

        if (callback !== undefined) {
            callback($overlay)
        }
    },

    closeOverlay($overlay) {
        if (!$overlay.length) {
            return;
        }

        Application.$BODY.css('overflow', 'auto')
        $overlay.removeClass('overlay--open')
    },

    startLoading($loading) {
        $loading.removeClass('loading--done')
    },

    stopLoading($loading) {
        $loading.addClass('loading--done')
    }
}

export default Helper

import $ from 'jquery'
import Application from './application'
import Helper from './helper'

const Keyboard = {
    init() {
        Keyboard.bindCloseOverlayOnEscape()
    },

    bindCloseOverlayOnEscape() {
        Application.$DOCUMENT.on('keydown', function (event) {
            if (event.keyCode === 27) {
                Helper.closeOverlay($('.overlay--open'))
            }
        })
    }
}

export default Keyboard

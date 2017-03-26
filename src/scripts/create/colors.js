import $ from 'jquery'
import Preview from './preview'

const Colors = {
    $inputs: $('.colors input[type="radio"]'),

    init() {
        Colors.bindSelectColor()
    },

    getCurrentColorId() {
        return Colors.$inputs.filter(':checked').val()
    },

    bindSelectColor() {
        Colors.$inputs.on('change', (event) => {
            Preview.load(
                Preview.getCurrentPostId(),
                Colors.getCurrentColorId()
            )
            event.preventDefault()
        })
    }
}

export default Colors

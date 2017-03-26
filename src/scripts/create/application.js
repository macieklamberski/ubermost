import $ from 'jquery'
import Sizes from './sizes'
import Colors from './colors'
import Posts from './posts'
import Preview from './preview'
import Download from './download'
import Keyboard from './keyboard'
import ExternalLinks from './external-links'

const Application = {
    $DOCUMENT: $(document),
    $BODY: $('body'),

    init() {
        Sizes.init()
        Colors.init()
        Posts.init()
        Preview.init()
        Download.init()
        Keyboard.init()
        ExternalLinks.init()
    }
}

export default Application

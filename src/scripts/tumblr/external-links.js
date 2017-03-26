import $ from 'jquery'

const ExternalLinks = {
    init() {
        const elements = [
            '.post__text footer a',
            '[rel="external"]'
        ]

        $(elements.join()).attr('target', '_new')
    }
}

export default ExternalLinks

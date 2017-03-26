import $ from 'jquery'

const ExternalLinks = {
    init() {
        const elements = [
            '[rel="external"]'
        ]

        $(elements.join()).attr('target', '_new')
    }
}

export default ExternalLinks

import $ from 'jquery'
import ExternalLinks from './external-links'

const BuyLinks = {
    $quotes: $('[data-tags*="isbn"]'),

    init() {
        BuyLinks.getLinks()
    },

    getISBNMap() {
        const ISBNs = []

        BuyLinks.$quotes.each((index, value) => {
            const $self = $(value)
            const tags = $self.data('tags').split(' ')
            const isbn = tags.filter((item) => /^isbn/.test(item))

            if (isbn.length > 0) {
                $self.attr('data-isbn', isbn[0])
                ISBNs.push(isbn[0])
            }
        })

        return ISBNs
    },

    insertCTAs(links) {
        BuyLinks.$quotes.each((index, value) => {
            const $self = $(value)
            const link = links[$self.data('isbn')]

            if (link) {
                $self.find('footer cite')
                    .append('<span></span>')
                    .append(`<a href="${link}">Buy the book</a>`)
            }
        })
    },

    getLinks() {
        const ISBNs = BuyLinks.getISBNMap()
        let links = []

        $.ajax({
            url: 'http://create.ubermost.com/wp-admin/admin-ajax.php',
            data: {
                action: 'get_isbn_links',
                isbns: ISBNs
            },
            dataType: 'json'
        })
        .done((result) => {
            links = result.data.links
            BuyLinks.insertCTAs(links)
            ExternalLinks.init()
        })
    }
}

export default BuyLinks

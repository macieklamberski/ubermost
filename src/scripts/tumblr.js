var $ = require('jquery')

var BuyLinks = {
    $quotes: $('[data-tags*="isbn"]'),

    init: function () {
        BuyLinks.getLinks()
    },

    getISBNMap: function () {
        var ISBNs = []

        BuyLinks.$quotes.each(function () {
            var $self = $(this)

            var tags = $self.data('tags').split(' ')
            var isbn = tags.filter(function (item) {
                return /^isbn/.test(item)
            })

            if (isbn.length > 0) {
                $self.attr('data-isbn', isbn[0]);
                ISBNs.push(isbn[0])
            }
        })

        return ISBNs
    },

    insertCTAs: function (links) {
        BuyLinks.$quotes.each(function () {
            var $self = $(this)

            var link = links[$self.data('isbn')]

            if (link) {
                $self.find('footer cite')
                .append('<span></span>')
                .append('<a href="' + link + '">Buy the book</a>')
            }
        })
    },

    getLinks: function () {
        var ISBNs = BuyLinks.getISBNMap()
        var links = []

        $.ajax({
            url: 'http://create.ubermost.com/wp-admin/admin-ajax.php',
            data: {
                action: 'get_isbn_links',
                isbns: ISBNs
            },
            dataType: 'json'
        })
        .done(function (result) {
            links = result.data.links
            BuyLinks.insertCTAs(links)
            ExternalLinks.init()
        })
    }
}

var ExternalLinks = {
    init: function () {
        var elements = [
            '.post__text footer a',
            '[rel="external"]'
        ]

        $(elements.join()).attr('target', '_new')
    }
}

$(function () {
    BuyLinks.init()
    ExternalLinks.init()
})

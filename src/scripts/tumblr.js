var $ = require('jquery')

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
  ExternalLinks.init()
})

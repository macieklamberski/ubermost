import $ from 'jquery'
import Velocity from 'velocity-animate'
import ImagesLoaded from 'imagesloaded'
import Helper from './helper'
import Application from './application'
import Preview from './preview'
import Colors from './colors'

const Posts = {
    $container: $('.posts'),
    $endpoint: $('.posts').data('endpoint'),
    $action: $('.posts').data('action'),
    $overlay: $('#posts-overlay'),
    $images: function () { return $('#posts-overlay a') },

    init() {
        Posts.bindOpenOverlay()
        Posts.bindSelectPost()
    },

    bindOpenOverlay() {
        Application.$BODY.on('click', '.button--change', (event) => {
            Helper.openOverlay(Posts.$overlay, Posts.load)
            event.preventDefault()
        })
    },

    bindSelectPost() {
        Posts.$container.on('click', 'a', function (event) {
            Preview.load(
                $(this).data('post-id'),
                Colors.getCurrentColorId()
            )
            Helper.closeOverlay(Posts.$overlay)
            event.preventDefault()
        })
    },

    load($overlay) {
        if (Posts.$container.children().length) {
            Posts.fadeInElements()
            return
        }

        $.get(Posts.$endpoint, {
            action: Posts.$action
        }, (result) => {
            if (!result.data) {
                return
            }

            const html = Helper.compileTemplate('posts', result.data)

            Posts.$container.html(html)
            Posts.$container.find('img').css('opacity', 0)

            Posts.$images().each((index, value) => {
                ImagesLoaded(value, () => Posts.fadeInElement(value))
            })
        }, 'json')
    },

    fadeInElement(element) {
        element = $(element)

        const image = element.find('img')
        image.css('opacity', 0)
        element.attr('data-loaded', true)

        Velocity(image.get(), 'fadeIn', {
            duration: 500
        })
    }
}

export default Posts

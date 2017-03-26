import $ from 'jquery'
import Velocity from 'velocity-animate'
import Helper from './helper'
import Application from './application'

const Posts = {
    $container: $('.posts'),
    $endpoint: $('.posts').data('endpoint'),
    $action: $('.posts').data('action'),
    $overlay: $('#posts-overlay'),
    $images: $('#posts-overlay li'),

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
        Posts.$container.on('click', 'a', (event) => {
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
            return;
        }

        $.get(Posts.$endpoint, {
            action: Posts.$action
        }, (result) => {
            if (result.data) {
                const data = result.data
            } else {
                return
            }

            const html = Helper.compileTemplate('posts', data)
            Posts.$container.html(html)
            Posts.$container.find('img').css('opacity', 0)

            Posts.$images.each(($item) => {
                ImagesLoaded($item.get(0), () => {
                    Posts.fadeInElement($item)
                })
            })
        }, 'json')
    },

    fadeInElement($item) {
        const image = Posts.$container.find('img')
        image.css('opacity', 0)

        Velocity(image.get(), 'fadeIn', {
            duration: 500,
            stagger: 12.5
        })
    }
}

export default Posts

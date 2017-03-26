import $ from 'jquery'
import Velocity from 'velocity-animate'
import Helper from './helper'

const Posts = {
    $container: $('.posts'),
    $endpoint: $('.posts').data('endpoint'),
    $action: $('.posts').data('action'),
    $overlay: $('#posts-overlay'),
    $loading: $('#posts-overlay'),

    init() {
        Posts.bindOpenOverlay()
        Posts.bindSelectPost()
    },

    bindOpenOverlay() {
        Application.$BODY.on('click', '.button--change', function (event) {
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
            return;
        }

        $.get(Posts.$endpoint, {action: Posts.$action}, (result) => {
            if (result.data) {
                var data = result.data
            } else {
                return
            }

            var html = Helper.compileTemplate('posts', data)
            Posts.$container.html(html)
            Posts.$container.find('img').css('opacity', 0)
            ImagesLoaded(Posts.$container.get(0), () => {
                Helper.stopLoading(Posts.$loading)
                Posts.fadeInElements()
            })
        }, 'json')
    },

    fadeInElements() {
        var images = Posts.$container.find('img')
        images.css('opacity', 0)

        Velocity(images.get(), 'fadeIn', {
            duration: 500,
            stagger: 12.5
        })
    }
}

export default Posts

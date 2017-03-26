import $ from 'jquery'
import Velocity from 'velocity-animate'
import ImagesLoaded from 'imagesloaded'
import Application from './application'
import Colors from './colors'
import Download from './download'
import Helper from './helper'

const Preview = {
    $container: $('.preview'),
    $share: $('.share'),
    $endpoint: $('.preview').data('endpoint'),
    $action: $('.preview').data('action'),
    $loading: $('.preview').closest('.loading'),
    $input: $('form input[name="post_id"]'),

    init() {
        Preview.load(
            Preview.getCurrentPostId(),
            Colors.getCurrentColorId()
        )
    },

    getCurrentPostId() {
        return Preview.$input.val()
    },

    load(postId, colorId) {
        var loading = setTimeout(function () {
            Helper.startLoading(Preview.$loading)
        }, 250)

        Download.$trigger.attr('disabled', 'disabled')

        $.get(Preview.$endpoint, {action: Preview.$action, post_id: postId, color_id: colorId}, function (result) {
            if (result.data) {
                var data = result.data;
            } else {
                return;
            }

            Preview.$share.html(Helper.compileTemplate('share', data))
            Preview.$share.addClass('share--enabled')
            Download.$trigger.removeAttr('disabled')

            Preview.$input.val(postId)
            Preview.$container.html(Helper.compileTemplate('post', data))
            Preview.$container.find('img').css('opacity', 0)

            ImagesLoaded(Preview.$container.get(0), function () {
                clearTimeout(loading)
                Helper.stopLoading(Preview.$loading)

                Velocity(Preview.$container.find('img').get(), 'fadeIn', {
                    duration: 400,
                    complete() {
                        Preview.$container.css('background-image', `url(${data.image})`)
                    }
                })
            })
        }, 'json')
    }
}

export default Preview

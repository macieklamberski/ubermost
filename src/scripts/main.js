(function ($) {

  var Helper = {
    compileTemplate: function (id, data) {
      var template = Handlebars.compile($('#' + id + '-template').html());
      return template(data);
    },

    openOverlay: function ($overlay, callback) {
      Application.$BODY.css('overflow', 'hidden');
      $overlay.addClass('overlay--open');
      $overlay.find('.overlay__close').on('click', function (event) {
        Helper.closeOverlay($overlay);
        event.preventDefault();
      });
      if (callback !== undefined) {
        callback($overlay);
      }
    },

    closeOverlay: function ($overlay) {
      if (!$overlay.length) {
        return;
      }
      Application.$BODY.css('overflow', 'auto');
      $overlay.removeClass('overlay--open');
    },

    startLoading: function ($loading) {
      $loading.removeClass('loading--done');
    },

    stopLoading: function ($loading) {
      $loading.addClass('loading--done');
    }
  };

  var Sizes = {
    $trigger: $('.dropdown'),
    $selected: $('.dropdown span'),
    $overlay: $('#sizes-overlay'),
    $input: $('form input[name="size"]'),

    init: function () {
      Sizes.bindOpenOverlay();
      Sizes.bindSelectSize();
    },

    bindOpenOverlay: function () {
      Sizes.$trigger.on('click', function (event) {
        Helper.openOverlay(Sizes.$overlay);
        event.preventDefault();
      });
    },

    bindSelectSize: function () {
      Sizes.$overlay.find('label').on('click', function (event) {
        var $self = $(this);
        Sizes.$input.val($('input', $self).val());
        Sizes.$selected.html($('span', $self).html());
        Helper.closeOverlay(Sizes.$overlay);
        event.preventDefault();
      });
    }
  };

  var Colors = {
    $inputs: $('.colors input[type="radio"]'),

    init: function () {
      Colors.bindSelectColor();
    },

    getCurrentColorId: function () {
      return Colors.$inputs.filter(':checked').val();
    },

    bindSelectColor: function () {
      Colors.$inputs.on('change', function (event) {
        Preview.load(
          Preview.getCurrentPostId(),
          Colors.getCurrentColorId()
        );
        event.preventDefault();
      });
    }
  };

  var Posts = {
    $container: $('.posts'),
    $gateway: $('.posts').data('gateway'),
    $action: $('.posts').data('action'),
    $overlay: $('#posts-overlay'),
    $loading: $('#posts-overlay'),

    init: function () {
      Posts.bindOpenOverlay();
      Posts.bindSelectPost();
    },

    bindOpenOverlay: function () {
      Application.$BODY.on('click', '.button--change', function (event) {
        Helper.openOverlay(Posts.$overlay, Posts.load);
        event.preventDefault();
      });
    },

    bindSelectPost: function () {
      Posts.$container.on('click', 'a', function (event) {
        Preview.load(
          $(this).data('post-id'),
          Colors.getCurrentColorId()
        );
        Helper.closeOverlay(Posts.$overlay);
        event.preventDefault();
      });
    },

    load: function ($overlay) {
      if (Posts.$container.children().length) {
        Posts.fadeInElements();
        return;
      }

      $.get(Posts.$gateway, {action: Posts.$action}, function (result) {
        if (result.data) {
          data = result.data;
        } else {
          return;
        }

        var html = Helper.compileTemplate('posts', data);
        Posts.$container.html(html);
        Posts.$container.find('img').css('opacity', 0);
        Posts.$container.imagesLoaded(function () {
          Helper.stopLoading(Posts.$loading);
          Posts.fadeInElements();
        });
      }, 'json');
    },

    fadeInElements: function () {
      Posts.$container.find('img')
        .css('opacity', 0)
        .velocity('transition.slideLeftIn', {
          duration: 500,
          stagger: 12.5
        });
    }
  };

  var Preview = {
    $container: $('.preview'),
    $share: $('.share'),
    $gateway: $('.preview').data('gateway'),
    $action: $('.preview').data('action'),
    $loading: $('.preview').closest('.loading'),
    $input: $('form input[name="post"]'),

    init: function () {
      Preview.load(
        Preview.getCurrentPostId(),
        Colors.getCurrentColorId()
      );
    },

    getCurrentPostId: function () {
      return Preview.$input.val();
    },

    load: function (postId, colorId) {
      Helper.startLoading(Preview.$loading);
      Download.$trigger.attr('disabled', 'disabled');

      setTimeout(function () {
        $.get(Preview.$gateway, {action: Preview.$action, post: postId, color: colorId}, function (result) {
          if (result.data) {
            data = result.data;
          } else {
            return;
          }

          Preview.$share.html(Helper.compileTemplate('share', data));
          Preview.$share.addClass('share--enabled');
          Download.$trigger.removeAttr('disabled');

          Preview.$input.val(postId);
          Preview.$container.html(Helper.compileTemplate('post', data));
          Preview.$container.find('img').css('opacity', 0);
          Preview.$container.imagesLoaded(function () {
            Helper.stopLoading(Preview.$loading);
            Preview.$container.find('img')
              .velocity('fadeIn', {
                duration: 400,
                complete: function () {
                  Preview.$container.css('background-image', 'url(' + data.image + ')');
                }
              });
          });
        }, 'json');
      }, 1000);
    }
  };

  var Download = {
    $form: $('.main__options'),
    $trigger: $('.button--download'),

    init: function () {
      Download.bindDownloadWallpaper();
    },

    bindDownloadWallpaper: function () {
      Download.$form.on('submit', function (event) {
        var self = this;
        Download.$trigger.text('Good choice, man!');
        event.preventDefault();
        setTimeout(function() { self.submit(); }, 1);
      });
    }
  }

  var Keyboard = {
    init: function () {
      Keyboard.bindCloseOverlayOnEscape();
    },

    bindCloseOverlayOnEscape: function () {
      Application.$DOCUMENT.on('keydown', function (event) {
        if (event.keyCode == 27) {
          Helper.closeOverlay($('.overlay--open'));
        }
      });
    }
  };

  var Application = {
    $DOCUMENT: $(document),
    $BODY: $('body'),

    init: function () {
      Sizes.init();
      Colors.init();
      Posts.init();
      Preview.init();
      Download.init();
      Keyboard.init();
    }
  };

  $(function () {
    Application.init();
  });

})(jQuery);

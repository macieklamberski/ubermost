(function ($) {

  var App = {

    $DOCUMENT: $(document),
    $BODY: $('body'),

    /**
     * Initialize features.
     */
    init: function () {
      App.Sizes.init();
      App.Colors.init();
      App.Posts.init();
      App.Preview.init();
      App.Keyboard.init();
    },

    /**
     * Helper functions used across the script.
     */
    Common: {
      compileTemplate: function (id, data) {
        var template = Handlebars.compile($('#' + id + '-template').html());
        return template(data);
      },

      openOverlay: function ($overlay, callback) {
        App.$BODY.css('overflow', 'hidden');
        $overlay.addClass('overlay--open');
        $overlay.find('.overlay__close').on('click', function (event) {
          App.Common.closeOverlay($overlay);
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

        App.$BODY.css('overflow', 'auto');
        $overlay.removeClass('overlay--open');
      }
    },

    /**
     *
     */
    Sizes: {
      $trigger: $('.dropdown'),
      $selected: $('.dropdown span'),
      $overlay: $('#sizes-overlay'),

      init: function () {
        App.Sizes.bindOpenOverlay();
        App.Sizes.bindSelectSize();
      },

      bindOpenOverlay: function () {
        App.Sizes.$trigger.on('click', function (event) {
          App.Common.openOverlay(App.Sizes.$overlay);
          event.preventDefault();
        });
      },

      bindSelectSize: function () {
        App.Sizes.$overlay.find('label').on('click', function () {
          App.Sizes.$selected.html($(this).find('span').html());
          App.Common.closeOverlay(App.Sizes.$overlay);
        });
      }
    },

    /**
     *
     */
    Colors: {
      $inputs: $('.colors input[type="radio"]'),

      init: function () {
        App.Colors.bindSelectColor();
      },

      getCurrentColorId: function () {
        return App.Colors.$inputs.filter(':checked').val();
      },

      bindSelectColor: function () {
        App.Colors.$inputs.on('change', function (event) {
          var $self = $(this);
          App.Preview.load(
            App.Preview.$preview.data('current-post-id'),
            App.Colors.getCurrentColorId()
          );
          event.preventDefault();
        });
      }
    },

    /**
     *
     */
    Posts: {
      $gateway: $('.posts').data('gateway'),
      $trigger: $('.download'),
      $overlay: $('#posts-overlay'),
      $loading: $('#posts-overlay'),
      $list: $('.posts'),

      init: function () {
        App.Posts.bindOpenOverlay();
        App.Posts.bindSelectPost();
      },

      bindOpenOverlay: function () {
        App.Posts.$trigger.on('click', function (event) {
          App.Common.openOverlay(
            App.Posts.$overlay,
            App.Posts.load
          );
          event.preventDefault();
        });
      },

      bindSelectPost: function () {
        App.Posts.$list.on('click', 'a[data-id]', function (event) {
          App.Preview.load(
            $(this).data('id'),
            App.Colors.getCurrentColorId()
          );
          App.Common.closeOverlay(App.Posts.$overlay);
          event.preventDefault();
        });
      },

      load: function ($overlay) {
        if (App.Posts.$list.children().length) {
          App.Posts.fadeInElements();
          return;
        }

        $.get(App.Posts.$gateway, function (data) {
          var html = App.Common.compileTemplate('posts', data);
          App.Posts.$list.html(html);
          App.Posts.$list.find('img').css('opacity', 0);
          App.Posts.$list.imagesLoaded(function () {
            App.Posts.$loading.addClass('loading--done');
            App.Posts.fadeInElements();
          });
        });
      },

      fadeInElements: function () {
        App.Posts.$list.find('img')
          .css('opacity', 0)
          .velocity('transition.slideLeftIn', {
            duration: 500,
            stagger: 12.5
          });
      }
    },

    /**
     *
     */
    Preview: {
      $gateway: $('.preview').data('gateway'),
      $preview: $('.preview'),
      $loading: $('.preview').closest('.loading'),

      init: function () {
        App.Preview.load(
          App.Preview.$preview.data('initial-post-id'),
          App.Colors.getCurrentColorId()
        );
      },

      load: function (postId, colorId) {
        App.Preview.$loading.removeClass('loading--done');

        $.get(App.Preview.$gateway, {id: postId, color: colorId}, function (data) {
          App.Preview.$preview.data('current-post-id', postId);

          var html = App.Common.compileTemplate('post', data);
          App.Preview.$preview.html(html);
          App.Preview.$preview.find('img').css('opacity', 0);

          App.Preview.$preview.imagesLoaded(function () {
            App.Preview.$loading.addClass('loading--done');
            App.Preview.$preview.find('img')
              .velocity('fadeIn', {
                duration: 350,
                complete: function () {
                  App.Preview.$preview.css('background-image', 'url(' + data.image + ')');
                }
              });
          });
        });
      }
    },

    /**
     *
     */
    Keyboard: {
      init: function () {
        App.Keyboard.bindClosingOverlay();
      },

      bindClosingOverlay: function () {
        App.$DOCUMENT.on('keydown', function (event) {
          if (event.keyCode == 27) {
            App.Common.closeOverlay($('.overlay--open'));
          }
        });
      }
    }
  };

  $(function () {
    App.init();
  });

})(jQuery);

(function ($) {

  var App = {

    $DOCUMENT: $(document),
    $BODY: $('body'),

    /**
     * Initialize features.
     */
    init: function () {
      App.Sizes.init();
      App.Posts.init();
      App.Keyboard.init();
    },

    /**
     * Helper functions used across the script.
     */
    Helpers: {
      compileTemplate: function (id, data) {
        var template = Handlebars.compile($('#' + id + '-template').html());
        return template(data);
      },

      openOverlay: function ($overlay, callback) {
        App.$BODY.css('overflow', 'hidden');
        $overlay.addClass('overlay--open');
        $overlay.find('.overlay__close').on('click', function (event) {
          App.Helpers.closeOverlay($overlay);
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
      $trigger: $('.sizes__selected'),
      $selectedLabel: $('.sizes__selected span'),
      $overlay: $('.sizes__selected').next(),

      init: function () {
        App.Sizes.bindOpenOverlay();
        App.Sizes.bindSelectSize();
      },

      bindOpenOverlay: function () {
        App.Sizes.$trigger.on('click', function (event) {
          App.Helpers.openOverlay(App.Sizes.$overlay);
          event.preventDefault();
        });
      },

      bindSelectSize: function () {
        App.Sizes.$overlay.find('label').on('click', function () {
          App.Sizes.$selectedLabel.html($(this).find('span').html());
          App.Helpers.closeOverlay(App.Sizes.$overlay);
        });
      }
    },

    /**
     *
     */
    Posts: {
      $trigger: $('.download'),
      $overlay: $('.posts-overlay').closest('.overlay'),
      $loader: $('.posts-overlay').closest('.overlay').find('.loader'),
      $postsList: $('.posts-overlay'),

      init: function () {
        App.Posts.bindOpenOverlay();
      },

      bindOpenOverlay: function () {
        App.Posts.$trigger.on('click', function (event) {
          App.Helpers.openOverlay(
            App.Posts.$overlay,
            App.Posts.loadList
          );
          event.preventDefault();
        });
      },

      loadList: function ($overlay) {
        if (App.Posts.$postsList.children().length) {
          App.Posts.fadeInElements();
        } else {
          $.get(App.Posts.$postsList.data('source'), function (data) {
            var html = App.Helpers.compileTemplate('posts', data);
            App.Posts.$postsList.html(html);
            App.Posts.$postsList.find('img').css('opacity', 0);
            App.Posts.$postsList.imagesLoaded(function () {
              App.Posts.$loader.addClass('loader--loaded');
              App.Posts.fadeInElements();
            });
          });
        }
      },

      fadeInElements: function () {
        App.Posts.$postsList.find('img')
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
    Keyboard: {
      init: function () {
        App.Keyboard.bindClosingOverlay();
      },

      bindClosingOverlay: function () {
        $(document).on('keydown', function(event) {
          if (event.keyCode == 27) {
            App.Helpers.closeOverlay($('.overlay--open'));
          }
        });
      }
    }
  };

  $(function () {
    App.init();
  });

})(jQuery);

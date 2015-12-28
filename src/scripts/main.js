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
    }
  };

  var Sizes = {
    $trigger: $('.dropdown'),
    $selected: $('.dropdown span'),
    $overlay: $('#sizes-overlay'),

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
      Sizes.$overlay.find('label').on('click', function () {
        Sizes.$selected.html($(this).find('span').html());
        Helper.closeOverlay(Sizes.$overlay);
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
        var $self = $(this);
        Preview.load(
          Preview.$preview.data('current-post-id'),
          Colors.getCurrentColorId()
        );
        event.preventDefault();
      });
    }
  };

  var Posts = {
    $gateway: $('.posts').data('gateway'),
    $trigger: $('.download'),
    $overlay: $('#posts-overlay'),
    $loading: $('#posts-overlay'),
    $list: $('.posts'),

    init: function () {
      Posts.bindOpenOverlay();
      Posts.bindSelectPost();
    },

    bindOpenOverlay: function () {
      Posts.$trigger.on('click', function (event) {
        Helper.openOverlay(
          Posts.$overlay,
          Posts.load
        );
        event.preventDefault();
      });
    },

    bindSelectPost: function () {
      Posts.$list.on('click', 'a[data-id]', function (event) {
        Preview.load(
          $(this).data('id'),
          Colors.getCurrentColorId()
        );
        Helper.closeOverlay(Posts.$overlay);
        event.preventDefault();
      });
    },

    load: function ($overlay) {
      if (Posts.$list.children().length) {
        Posts.fadeInElements();
        return;
      }

      $.get(Posts.$gateway, function (data) {
        var html = Helper.compileTemplate('posts', data);
        Posts.$list.html(html);
        Posts.$list.find('img').css('opacity', 0);
        Posts.$list.imagesLoaded(function () {
          Posts.$loading.addClass('loading--done');
          Posts.fadeInElements();
        });
      });
    },

    fadeInElements: function () {
      Posts.$list.find('img')
        .css('opacity', 0)
        .velocity('transition.slideLeftIn', {
          duration: 500,
          stagger: 12.5
        });
    }
  };

  var Preview = {
    $gateway: $('.preview').data('gateway'),
    $preview: $('.preview'),
    $loading: $('.preview').closest('.loading'),

    init: function () {
      Preview.load(
        Preview.$preview.data('initial-post-id'),
        Colors.getCurrentColorId()
      );
    },

    load: function (postId, colorId) {
      Preview.$loading.removeClass('loading--done');

      $.get(Preview.$gateway, {id: postId, color: colorId}, function (data) {
        Preview.$preview.data('current-post-id', postId);

        var html = Helper.compileTemplate('post', data);
        Preview.$preview.html(html);
        Preview.$preview.find('img').css('opacity', 0);

        Preview.$preview.imagesLoaded(function () {
          Preview.$loading.addClass('loading--done');
          Preview.$preview.find('img')
            .velocity('fadeIn', {
              duration: 350,
              complete: function () {
                Preview.$preview.css('background-image', 'url(' + data.image + ')');
              }
            });
        });
      });
    }
  };

  var Keyboard = {
    init: function () {
      Keyboard.bindClosingOverlay();
    },

    bindClosingOverlay: function () {
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
      Keyboard.init();
    }
  };

  $(function () {
    Application.init();
  });

})(jQuery);

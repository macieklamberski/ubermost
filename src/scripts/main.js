//= include ../../bower_components/jquery/dist/jquery.js
//= include ../../bower_components/velocity/velocity.js
//= include ../../bower_components/velocity/velocity.ui.js

(function ($) {

  var App = {

    /**
     * Initialize features
     */
    init: function () {
      App.Overlay.init();
      App.SizesOverlay.init();
    },

    /**
     *
     */
    Overlay: {
      // $close: $('.overlay'),

      init: function () {
        // App.SizesOverlay.$trigger.on('click', function () {
        //   App.SizesOverlay.$overlay.addClass('overlay--open');
        // });
      }
    },

    /**
     *
     */
    SizesOverlay: {
      $trigger: $('.sizes__selected'),
      $overlay: $('.sizes__selected').next(),

      init: function () {
        App.SizesOverlay.$trigger.on('click', function () {
          App.SizesOverlay.$overlay.addClass('overlay--open');
        });
      }
    }
  };

  $(function () {
    App.init();
  });

})(jQuery);

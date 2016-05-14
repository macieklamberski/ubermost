(function ($) {

  var Wallpapers = {
    ajaxGateway        : $('#wallpapers').data('gateway'),
    ajaxAction         : $('#wallpapers').data('action'),
    combinations       : $('#wallpapers').data('combinations'),
    $sectionStart      : $('#wallpapers-start'),
    $sectionProcessing : $('#wallpapers-processing'),
    $sectionDone       : $('#wallpapers-done'),
    $counterCurrent    : $('#wallpapers-current'),
    $counterAll        : $('#wallpapers-all'),

    init: function () {
      Wallpapers.bindProcessingSpecific();
      Wallpapers.bindProcessingAll();
    },

    bindProcessingSpecific: function () {
      if (Wallpapers.$sectionStart.length > 0) {
        return;
      }

      Wallpapers.startRegeneration(Wallpapers.combinations);
    },

    bindProcessingAll: function () {
      if (Wallpapers.$sectionStart.length == 0) {
        return;
      }

      Wallpapers.$sectionStart.find('input').on('click', function () {
        Wallpapers.startRegeneration(Wallpapers.combinations);
     });
    },

    startRegeneration: function (combinations) {
      Wallpapers.$sectionProcessing.show();
      Wallpapers.$counterAll.text(combinations.length);

      Wallpapers.runRegeneration(combinations);
    },

    runRegeneration: function (combinations, index) {
      index = index || 0;

      var combination = combinations[index];
      var parameters  = {
        action   : Wallpapers.ajaxAction,
        post_id  : combination.post,
        color_id : combination.color,
        size_id  : combination.size
      };

      $.get(Wallpapers.ajaxGateway, parameters, function () {
        Wallpapers.$counterCurrent.text(index + 1);

        if (index + 1 == combinations.length) {
          Wallpapers.endRegeneration();
        } else {
          Wallpapers.runRegeneration(combinations, index + 1);
        }
      });
    },

    endRegeneration: function () {
      Wallpapers.$sectionDone.show();
    }
  };

  $(function () {
    Wallpapers.init();
  });

})(jQuery);

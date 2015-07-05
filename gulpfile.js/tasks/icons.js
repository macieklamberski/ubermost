module.exports = function (gulp, plugins, config, helpers) {
  gulp.task('icons', function () {
    var stream = gulp.src(config.source + '/icons/*.svg')
      .pipe(plugins.plumber(helpers.onError))
      .pipe(plugins.iconfont({
        fontName: 'Icons',
        className: 'icon',
        'normalize': true,
        'fontHeight': 1001,
        appendCodepoints: false
      }))
      .on('codepoints', function(codepoints, options) {
        options.glyphs = codepoints;
        var stream = gulp.src('gulpfile.js/icons.css')
          .pipe(plugins.consolidate('lodash', options))
          .pipe(helpers.ifNotDev(plugins.minifyCss()))
          .pipe(plugins.rename({ suffix: '.min' }));

        helpers.copyToTargets(stream, 'styles', '/styles');
      });

    return helpers.copyToTargets(stream, 'fonts', '/fonts');
  });
};

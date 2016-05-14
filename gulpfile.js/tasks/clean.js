module.exports = function (gulp, plugins, config) {
  gulp.task('clean', function () {
    var directories = [];
    config.targets.forEach(function (target) {
      target.tasks.forEach(function (task) {
        switch (task) {
          case 'templates':
            directories.push(target.path + '/*.html');
          break;
          case 'sprites':
            directories.push(target.path + '/images');
          break;
          default:
            directories.push(target.path + '/' + task);
        }
      });
    });
    return plugins.del(directories, { force: true });
  });
};

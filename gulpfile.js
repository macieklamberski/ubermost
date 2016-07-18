require('frontkit')(require('gulp'), {
  "source": "src",
  "targets": [
    {
      "path": "dist",
      "tasks": [
        "templates",
        "scripts",
        "styles",
        "images",
        "sprites",
        "icons",
        "fonts",
        "media"
      ]
    },
    {
      "path": "wp/wp-content/themes/ubermost-create/assets",
      "tasks": [
        "scripts",
        "styles",
        "images"
      ]
    }
  ],
  "deploy": require('./deploy.json')
})

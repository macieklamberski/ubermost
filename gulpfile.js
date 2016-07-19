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
    }
  ],
  "deploy": require('./deploy.json')
})

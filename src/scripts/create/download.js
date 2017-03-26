import $ from 'jquery'

const Download = {
    $form: $('.main__options'),
    $trigger: $('.button--download'),

    init() {
        Download.bindDownloadWallpaper()
    },

    bindDownloadWallpaper() {
        Download.$form.on('submit', (event) => {
            const self = this
            event.preventDefault()
            setTimeout(() => self.submit(), 1)
        })
    }
}

export default Download

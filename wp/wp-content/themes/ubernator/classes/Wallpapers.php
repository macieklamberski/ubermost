<?php
namespace Ubernator;

use Ubernator\Generator;

/**
 * Sack for functions related to building wallpapers regeneration page.
 */
class Wallpapers {

    /**
     * Initialize the page.
     */
    public static function build() {
        ?>
            <div class="wrap">
                <h2>Regenerate wallpapers</h2>
                <p>Cras mattis consectetur purus sit amet fermentum. Vestibulum id ligula porta felis euismod semper. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
                <p>To begin, just press the button below.</p>
                <p id="regenerate-start">
                    <input type="submit" class="button button-primary" value="Regenerate all wallpapers">
                </p>
                <div id="regenerate-processing" style="display: none">
                    <h3>
                        Processing&hellip;
                        (<span id="regenerate-current">0</span>/<span id="regenerate-all">0</span>)
                    </h3>
                    <ol>
                        <li>Nullam quis risus eget urna mollis ornare vel eu leo.</li>
                    </ol>
                </div>
                <div id="regenerate-done" style="display: none">
                    <h3>All done!</h3>
                    <p>Cras justo odio, dapibus ac facilisis in, egestas eget quam. Sed posuere consectetur est at lobortis. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec sed odio dui.</p>
                    <p><input type="submit" class="button" value="Return to list of posts"></p>
                </div>
                <script>
                </script>
            </div>

        <?php
    }
}

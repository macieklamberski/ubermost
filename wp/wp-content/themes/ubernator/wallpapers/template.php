<?php
use Ubernator\Helper;

if ($_GET['ids']) {
    $posts = explode(',', $_GET['ids']);
} else {
    $posts = array_map(function ($post) { return $post->ID; }, get_posts(['posts_per_page' => -1]));
}

$combinations  = [];
$public_posts  = array_map(function ($post) { return $post->ID; }, Helper::load_public_posts());
$public_colors = get_posts(['post_type' => 'color', 'posts_per_page' => -1]);
$public_sizes  = get_posts(['post_type' => 'size', 'posts_per_page' => -1]);

$posts = array_intersect($posts, $public_posts);

foreach ($posts as $post) {
    foreach ($public_colors as $color) {
        foreach ($public_sizes as $size) {
            $combinations[] = [
                'post'  => $post,
                'color' => $color->ID,
                'size'  => $size->ID,
            ];
        }
    }
}
?>
<div class="wrap" id="wallpapers" data-gateway="<?php echo admin_url('admin-ajax.php'); ?>" data-action="regenerate_wallpaper" data-combinations='<?php echo json_encode($combinations); ?>'>
    <h2>Regenerate wallpapers</h2>
    <?php if (!$_GET['ids']): ?>
        <p>To begin, just press the button below.</p>
        <p id="wallpapers-start">
            <input type="submit" class="button button-primary" value="Regenerate all wallpapers">
        </p>
    <?php endif; ?>
    <div id="wallpapers-processing" style="display: none">
        <h3>
            Processing&hellip;
            (<span id="wallpapers-current">0</span>/<span id="wallpapers-all">0</span>)
        </h3>
        <ol></ol>
    </div>
    <div id="wallpapers-done" style="display: none">
        <h3>All done!</h3>
        <p>
            <a href="<?php echo admin_url('edit.php?post_status=publish'); ?>" class="button">
                Return to list of posts
            </a>
        </p>
    </div>
</div>
<script src="<?php echo get_stylesheet_directory_uri(); ?>/wallpapers/script.js"></script>

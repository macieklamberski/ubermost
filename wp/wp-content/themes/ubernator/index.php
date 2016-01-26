<?php
use Ubernator\Helper;

$current_post  = Helper::load_selected_post($_GET['post_id']);
$current_color = Helper::load_selected_color($_GET['color_id']);
$current_size  = Helper::load_selected_size($_GET['size_id']);

Helper::download($current_post, $current_color, $current_size);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
        <title>Ubermost&mdash;Create</title>
        <link href="//assets.ubermost.com/images/favicon.png" rel="shortcut icon">
        <link href="//fonts.googleapis.com/css?family=Source+Sans+Pro|Source+Serif+Pro:400,600" rel="stylesheet">
        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" rel="stylesheet">
        <link href="//assets.ubermost.com/styles/main.min.css" rel="stylesheet">
        <link href="http://feeds.feedburner.com/ubermost" rel="alternate" type="application/rss+xml">
    </head>
    <body>
        <header class="header">
            <div class="header__body">
                <a href="<?php echo site_url(); ?>" class="header__body__logo">
                    <img src="//assets.ubermost.com/images/logo-alt.svg" alt="">
                </a>
                <ul class="header__body__menu">
                    <li><a href="http://ubermost.com">Blog</a></li>
                    <li>
                        <a href="http://facebook.com/ubermost">
                            <i class="fa fa-facebook"></i>
                            <span>Facebook</span>
                        </a>
                    </li>
                    <li>
                        <a href="http://instagram.com/ubermost">
                            <i class="fa fa-instagram"></i>
                            <span>Instagram</span>
                        </a>
                    </li>
                </ul>
                <div class="header__body__info">
                    <p>Create inspiring wallpaper from your favourite lettering! You can download it as a wallpaper for your smartphone and PC, or if you&apos;re feeling social&mdash;post it on social network of choice to share the wisdom with your friends.</p>
                </div>
            </div>
        </header>

        <main class="main">
            <div class="main__preview loading">
                <div class="loading__body preview" data-gateway="<?php echo admin_url('admin-ajax.php'); ?>" data-action="get_post"></div>
                <span class="loading__indicator"></span>
            </div>

            <form method="get" action="<?php echo site_url(); ?>" class="main__options">
                <input type="hidden" name="action" value="download">
                <input type="hidden" name="post_id" value="<?php echo $current_post ? $current_post->ID : null; ?>">
                <input type="hidden" name="size_id" value="<?php echo $current_size ? $current_size->ID : null; ?>">

                <section class="option">
                    <h2 class="option__heading">Color</h2>
                    <ul class="colors">
                        <?php foreach (Helper::load_public_colors() as $color): ?>
                            <li>
                                <label class="color" style="color: <?php the_field('bg_color', $color->ID); ?>">
                                    <input type="radio" name="color_id" value="<?php echo $color->ID; ?>" <?php echo $current_color && $current_color->ID == $color->ID ? 'checked' : ''; ?>>
                                    <span><i class="fa fa-check" style="color: <?php the_field('fg_color', $color->ID); ?>"></i></span>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
                <section class="option">
                    <h2 class="option__heading">Screen Size</h2>
                    <div class="dropdown">
                        <?php if ($current_size): ?>
                            <span>
                                <?php echo $current_size->post_title; ?>
                                <em><?php the_field('device_name', $current_size->ID); ?></em>
                            </span>
                        <?php else: ?>
                            <span data-detected=" <em>(detected)</em>"></span>
                        <?php endif; ?>
                        <i class="fa fa-bars"></i>
                    </div>
                </section>
                <section class="option option--download">
                    <button type="submit" class="button button--download">Download wallpaper</button>
                    <div class="share"></div>
                </section>
            </form>
        </main>

        <footer class="footer-alt">
            <div class="footer-alt__body">
                <form action="" method="post" class="footer-alt__body__newsletter">
                  <h3>Newsletter</h3>
                  <p>Monthly digest with links to valuable content and ocassional news.</p>
                  <fieldset>
                    <input type="email" name="email" placeholder="Your email address">
                    <button type="submit"><i class="fa fa-envelope"></i></button>
                    </fieldset>
                </form>
                <p class="footer-alt__body__links">
                  <a href="http://feeds.feedburner.com/ubermost">RSS</a>
                  &middot;
                  <a href="http://facebook.com/ubermost">Facebook</a>
                  &middot;
                  <a href="http://instagram.com/ubermost">Instagram</a>
                  &middot;
                  <a href="mailto:hello@ubermost.com">hello@ubermost.com</a>
              </p>
              <small class="footer-alt__body__copy">Part of the <a href="http://ubermost.com">Ubermost</a>. Built by <a href="http://lamberski.com">Maciej Lamberski</a>.</small>
          </div>
        </footer>

        <section id="sizes-overlay" class="overlay">
            <a href="#" class="overlay__close"></a>
            <div class="overlay__body overlay__body--scale sizes">
                <?php foreach (get_terms(['group']) as $group): ?>
                    <section class="sizes__group">
                        <h3 class="sizes__group__heading">
                            <i class="fa fa-<?php the_field('icon', $group); ?>"></i>
                            <?php echo $group->name; ?>
                        </h3>
                        <?php foreach (Helper::load_public_sizes($group->name) as $size): ?>
                            <?php
                                $width  = get_field('width', $size->ID);
                                $height = get_field('height', $size->ID);
                                $ratio  = round($width / $height, 1);
                                $name   = get_field('device_name', $size->ID);
                            ?>
                            <label data-width="<?php echo $width; ?>" data-height="<?php echo $height; ?>" data-ratio="<?php echo $ratio; ?>">
                                <input type="radio" name="size" value="<?php echo $size->ID; ?>">
                                <span>
                                    <?php echo $size->post_title; ?>
                                    <?php if ($name): ?>
                                        <em><?php echo $name; ?></em>
                                    <?php endif; ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </section>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="posts-overlay" class="overlay loading">
            <a href="#" class="overlay__close"></a>
            <div class="overlay__body">
                <ul class="loading__body posts" data-gateway="<?php echo admin_url('admin-ajax.php'); ?>" data-action="get_posts"></ul>
            </div>
            <span class="loading__indicator"></span>
        </section>

        <script id="post-template" type="x-handlebars-template">
            <img src="{{image}}" alt="" class="preview__image">
            <div class="preview__details">
                <div class="preview__details__body">
                    <p class="preview__details__body__change">
                        <a href="#" class="button button--change">Change&hellip;</a>
                    </p>
                    <p class="preview__details__body__or">
                        <span>or</span> <a href="{{blog_link}}">see it on blog</a>
                    </p>
                </div>
            </div>
        </script>

        <script id="share-template" type="x-handlebars-template">
            <span class="share__label">Or share the wisdom:</span>
            <a href="http://facebook.com/sharer/sharer.php?u={{blog_link}}" class="share__link share__link--facebook">
                <i class="fa fa-facebook"></i>
            </a>
            <a href="http://pinterest.com/pin/create/button/?url={{blog_link}}&amp;media={{blog_image}}" class="share__link share__link--pinterest">
                <i class="fa fa-pinterest-p"></i>
            </a>
            <a href="http://twitter.com/intent/tweet?source=Ubermost&amp;text=&url={{blog_link}}" class="share__link share__link--twitter">
                <i class="fa fa-twitter"></i>
            </a>
            {{#if reblog_link}}
                <a href="{{reblog_link}}" class="share__link share__link--tumblr">
                    <i class="fa fa-tumblr"></i>
                </a>
            {{/if}}
        </script>

        <script id="posts-template" type="x-handlebars-template">
            {{#each posts}}
            <li>
                <a href="{{permalink}}" data-post-id="{{ID}}">
                    <img src="{{thumbnail}}" alt="">
                </a>
            </li>
            {{/each}}
        </script>

        <?php wp_footer(); ?>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/handlebars.js/3.0.0/handlebars.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/3.2.0/imagesloaded.pkgd.min.js"></script>
        <script src="//cdn.jsdelivr.net/velocity/1.2.3/velocity.min.js"></script>
        <script src="//cdn.jsdelivr.net/velocity/1.2.3/velocity.ui.min.js"></script>
        <script src="//assets.ubermost.com/scripts/main.min.js"></script>
    </body>
</html>

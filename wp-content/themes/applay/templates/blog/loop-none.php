<?php
$paths = explode('/', $_SERVER['REQUEST_URI']);
if (in_array('404', $paths)) { ?>
    <div class="single-content-none content-pad">
        <div class="row">
            <div class="col-md-4 col-md-offset-4" role="main">
                <article class="single-page-content text-center">
                    <span class="main-color-1-border banner-404">
                        <span class="main-color-1"><?php _e('404', 'leafcolor') ?></span>
                    </span>
                    <br />
                    <div class="content-text-404"><?php echo apply_filters('the_content', $page_content); ?></div>
                    <br />
                    <?php
                    if (ot_get_option('page404_search', 'on') != 'off') {
                        if (is_active_sidebar('search_sidebar')) : ?>
                            <?php dynamic_sidebar('search_sidebar'); ?>
                        <?php else : ?>
                            <form class="form-404 search-form" action="<?php echo home_url() ?>">
                                <input type="text" name="s" class="form-control" placeholder="<?php echo esc_attr(__('Try a search...', 'leafcolor')); ?>">
                            </form>
                    <?php endif;
                    } ?>
                </article>
            </div>
        </div>
    </div>
<?php } else { ?>
    <div class="single-content-none content-pad">
        <div class="row">
            <div class="col-md-4 col-md-offset-4" role="main">
                <a id="ia-icon-box-999" class="media ia-icon-box search-toggle" href="#" title="<?php echo esc_attr(__('Search', 'leafcolor')) ?>">
                    <div class="text-center">
                        <div class="ia-icon">
                            <i class="fa fa-search"></i>
                        </div>
                    </div>
                    <div class="media-body text-center">
                        <h4 class="media-heading"><?php _e('No results found', 'leafcolor'); ?></h4>
                        <p><?php _e('Click here to try another search', 'leafcolor'); ?></p>
                    </div>
                    <div class="clearfix"></div>
                </a>
            </div>
        </div>
    </div>
<?php } ?>
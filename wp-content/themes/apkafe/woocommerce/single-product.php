<?php

/**
 * The Template for displaying all single products.
 *
 * Override this template by copying it to yourtheme/woocommerce/single-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if (!defined('ABSPATH')) exit;

get_header(); ?>

<div class="container">
    <?php get_template_part('templates/product', 'product');
    get_sidebar(); ?>
</div>

<?php get_footer(); ?>
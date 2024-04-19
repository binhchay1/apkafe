<?php
use Lasso\Classes\Post;

$lasso_post = Post::create_instance( $lasso_url->lasso_id, $lasso_url );
if ( $lasso_post->is_show_description() && $is_show_description ) {
	echo $lasso_url->description;
}

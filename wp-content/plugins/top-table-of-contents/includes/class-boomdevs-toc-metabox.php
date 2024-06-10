<?php
// Control core classes for avoid errors
if( class_exists( 'CSF' ) ) {
    $prefix = 'boomdevs_metabox';
    global $post;
    $post_type = array('post', 'page');
    if(isset($_GET['post']) || isset($_GET['post_type'])) {
        if(isset($_GET['post_type'])) {
            $post_type = array($_GET['post_type']);
        } else {
            $post_name = get_post_type($_GET['post']);
            $post_type = array($post_name);
        }
    }

    CSF::createMetabox( $prefix, array(
        'title'     => __('Disable TOP Table Of Contents', 'boomdevs-toc'),
        'post_type' => $post_type,
    ) );
  
    CSF::createSection( $prefix, array(
      'fields' => array(
            array(
                'id'      => 'disable_auto_insert',
                'type'    => 'switcher',
                'title'   => __('Disable', 'boomdevs-toc'),
                'default' => false
            ),
        )
    ));
}
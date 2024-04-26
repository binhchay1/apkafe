<?php
/**
 * Content audit scope settings template.
 *
 * @var array $option
 * @var array $posts_list
 * @var array $pages_list
 * @var array $posts_checked
 * @var array $pages_checked
 */

declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Messages\Message;

Message::view_not_allowed( __( 'Sorry, you do not have sufficient permissions to access this page.', 'ahrefs-seo' ) )->show();

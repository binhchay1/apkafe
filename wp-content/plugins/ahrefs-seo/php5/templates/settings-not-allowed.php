<?php

namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Messages\Message;
Message::view_not_allowed( __( 'Sorry, you do not have sufficient permissions to access this page.', 'ahrefs-seo' ) )->show();
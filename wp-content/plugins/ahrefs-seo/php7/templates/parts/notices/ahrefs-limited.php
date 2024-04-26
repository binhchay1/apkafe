<?php
/**
 * Ahrefs monthly integrations rows limit reached notice template.
 */

declare(strict_types=1);
namespace ahrefs\AhrefsSeo;

use ahrefs\AhrefsSeo\Messages\Message;

Message::ahrefs_limited()->show();

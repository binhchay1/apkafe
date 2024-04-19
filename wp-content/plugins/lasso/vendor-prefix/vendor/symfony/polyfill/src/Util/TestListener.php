<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace LassoVendor\Symfony\Polyfill\Util;

if (\version_compare(\LassoVendor\PHPUnit\Runner\Version::id(), '9.1.0', '<')) {
    \class_alias('LassoVendor\\Symfony\\Polyfill\\Util\\TestListenerForV7', 'LassoVendor\\Symfony\\Polyfill\\Util\\TestListener');
} else {
    \class_alias('LassoVendor\\Symfony\\Polyfill\\Util\\TestListenerForV9', 'LassoVendor\\Symfony\\Polyfill\\Util\\TestListener');
}
if (\false) {
    class TestListener
    {
    }
}

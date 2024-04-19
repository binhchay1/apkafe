<?php

declare (strict_types=1);
namespace LassoVendor\Bamarni\Composer\Bin\ApplicationFactory;

use LassoVendor\Composer\Console\Application;
final class FreshInstanceApplicationFactory implements NamespaceApplicationFactory
{
    public function create(Application $existingApplication) : Application
    {
        return new Application();
    }
}

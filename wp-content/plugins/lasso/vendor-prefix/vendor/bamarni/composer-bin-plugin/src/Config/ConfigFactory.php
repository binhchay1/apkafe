<?php

declare (strict_types=1);
namespace LassoVendor\Bamarni\Composer\Bin\Config;

use LassoVendor\Composer\Config as ComposerConfig;
use LassoVendor\Composer\Factory;
use LassoVendor\Composer\Json\JsonFile;
use LassoVendor\Composer\Json\JsonValidationException;
use LassoVendor\Seld\JsonLint\ParsingException;
final class ConfigFactory
{
    /**
     * @throws JsonValidationException
     * @throws ParsingException
     */
    public static function createConfig() : ComposerConfig
    {
        $config = Factory::createConfig();
        $file = new JsonFile(Factory::getComposerFile());
        if (!$file->exists()) {
            return $config;
        }
        $file->validateSchema(JsonFile::LAX_SCHEMA);
        $config->merge($file->read());
        return $config;
    }
    private function __construct()
    {
    }
}

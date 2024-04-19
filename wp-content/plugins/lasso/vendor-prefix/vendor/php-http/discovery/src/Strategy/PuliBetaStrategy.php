<?php

namespace LassoVendor\Http\Discovery\Strategy;

use LassoVendor\Http\Discovery\ClassDiscovery;
use LassoVendor\Http\Discovery\Exception\PuliUnavailableException;
use LassoVendor\Puli\Discovery\Api\Discovery;
use LassoVendor\Puli\GeneratedPuliFactory;
/**
 * Find candidates using Puli.
 *
 * @internal
 *
 * @final
 *
 * @author David de Boer <david@ddeboer.nl>
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class PuliBetaStrategy implements DiscoveryStrategy
{
    /**
     * @var GeneratedPuliFactory
     */
    protected static $puliFactory;
    /**
     * @var Discovery
     */
    protected static $puliDiscovery;
    /**
     * @return GeneratedPuliFactory
     *
     * @throws PuliUnavailableException
     */
    private static function getPuliFactory()
    {
        if (null === self::$puliFactory) {
            if (!\defined('LassoVendor\\PULI_FACTORY_CLASS')) {
                throw new PuliUnavailableException('Puli Factory is not available');
            }
            $puliFactoryClass = PULI_FACTORY_CLASS;
            if (!ClassDiscovery::safeClassExists($puliFactoryClass)) {
                throw new PuliUnavailableException('Puli Factory class does not exist');
            }
            self::$puliFactory = new $puliFactoryClass();
        }
        return self::$puliFactory;
    }
    /**
     * Returns the Puli discovery layer.
     *
     * @return Discovery
     *
     * @throws PuliUnavailableException
     */
    private static function getPuliDiscovery()
    {
        if (!isset(self::$puliDiscovery)) {
            $factory = self::getPuliFactory();
            $repository = $factory->createRepository();
            self::$puliDiscovery = $factory->createDiscovery($repository);
        }
        return self::$puliDiscovery;
    }
    public static function getCandidates($type)
    {
        $returnData = [];
        $bindings = self::getPuliDiscovery()->findBindings($type);
        foreach ($bindings as $binding) {
            $condition = \true;
            if ($binding->hasParameterValue('depends')) {
                $condition = $binding->getParameterValue('depends');
            }
            $returnData[] = ['class' => $binding->getClassName(), 'condition' => $condition];
        }
        return $returnData;
    }
}

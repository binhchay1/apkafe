<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ahrefs\AhrefsSeo_Vendor\Monolog\Handler;

use ahrefs\AhrefsSeo_Vendor\Monolog\Logger;
use ahrefs\AhrefsSeo_Vendor\Monolog\Formatter\NormalizerFormatter;
use ahrefs\AhrefsSeo_Vendor\Doctrine\CouchDB\CouchDBClient;
/**
 * CouchDB handler for Doctrine CouchDB ODM
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class DoctrineCouchDBHandler extends \ahrefs\AhrefsSeo_Vendor\Monolog\Handler\AbstractProcessingHandler
{
    private $client;
    public function __construct(\ahrefs\AhrefsSeo_Vendor\Doctrine\CouchDB\CouchDBClient $client, $level = \ahrefs\AhrefsSeo_Vendor\Monolog\Logger::DEBUG, $bubble = \true)
    {
        $this->client = $client;
        parent::__construct($level, $bubble);
    }
    /**
     * {@inheritDoc}
     */
    protected function write(array $record)
    {
        $this->client->postDocument($record['formatted']);
    }
    protected function getDefaultFormatter()
    {
        return new \ahrefs\AhrefsSeo_Vendor\Monolog\Formatter\NormalizerFormatter();
    }
}

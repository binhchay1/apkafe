<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ahrefs\AhrefsSeo_Vendor\Monolog\Formatter;

use ahrefs\AhrefsSeo_Vendor\Monolog\Logger;
/**
 * Serializes a log message according to Wildfire's header requirements
 *
 * @author Eric Clemmons (@ericclemmons) <eric@uxdriven.com>
 * @author Christophe Coevoet <stof@notk.org>
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class WildfireFormatter extends \ahrefs\AhrefsSeo_Vendor\Monolog\Formatter\NormalizerFormatter
{
    const TABLE = 'table';
    /**
     * Translates Monolog log levels to Wildfire levels.
     */
    private $logLevels = array(\ahrefs\AhrefsSeo_Vendor\Monolog\Logger::DEBUG => 'LOG', \ahrefs\AhrefsSeo_Vendor\Monolog\Logger::INFO => 'INFO', \ahrefs\AhrefsSeo_Vendor\Monolog\Logger::NOTICE => 'INFO', \ahrefs\AhrefsSeo_Vendor\Monolog\Logger::WARNING => 'WARN', \ahrefs\AhrefsSeo_Vendor\Monolog\Logger::ERROR => 'ERROR', \ahrefs\AhrefsSeo_Vendor\Monolog\Logger::CRITICAL => 'ERROR', \ahrefs\AhrefsSeo_Vendor\Monolog\Logger::ALERT => 'ERROR', \ahrefs\AhrefsSeo_Vendor\Monolog\Logger::EMERGENCY => 'ERROR');
    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        // Retrieve the line and file if set and remove them from the formatted extra
        $file = $line = '';
        if (isset($record['extra']['file'])) {
            $file = $record['extra']['file'];
            unset($record['extra']['file']);
        }
        if (isset($record['extra']['line'])) {
            $line = $record['extra']['line'];
            unset($record['extra']['line']);
        }
        $record = $this->normalize($record);
        $message = array('message' => $record['message']);
        $handleError = \false;
        if ($record['context']) {
            $message['context'] = $record['context'];
            $handleError = \true;
        }
        if ($record['extra']) {
            $message['extra'] = $record['extra'];
            $handleError = \true;
        }
        if (\count($message) === 1) {
            $message = \reset($message);
        }
        if (isset($record['context'][self::TABLE])) {
            $type = 'TABLE';
            $label = $record['channel'] . ': ' . $record['message'];
            $message = $record['context'][self::TABLE];
        } else {
            $type = $this->logLevels[$record['level']];
            $label = $record['channel'];
        }
        // Create JSON object describing the appearance of the message in the console
        $json = $this->toJson(array(array('Type' => $type, 'File' => $file, 'Line' => $line, 'Label' => $label), $message), $handleError);
        // The message itself is a serialization of the above JSON object + it's length
        return \sprintf('%s|%s|', \strlen($json), $json);
    }
    public function formatBatch(array $records)
    {
        throw new \BadMethodCallException('Batch formatting does not make sense for the WildfireFormatter');
    }
    protected function normalize($data, $depth = 0)
    {
        if (\is_object($data) && !$data instanceof \DateTime) {
            return $data;
        }
        return parent::normalize($data, $depth);
    }
}

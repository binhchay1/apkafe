<?php

namespace ahrefs\AhrefsSeo_Vendor;

if (\class_exists('ahrefs\\AhrefsSeo_Vendor\\Google_Client', \false)) {
    // Prevent error with preloading in PHP 7.4
    // @see https://github.com/googleapis/google-api-php-client/issues/1976
    return;
}
$classMap = ['ahrefs\\AhrefsSeo_Vendor\\Google\\Client' => 'ahrefs\\AhrefsSeo_Vendor\Google_Client', 'ahrefs\\AhrefsSeo_Vendor\\Google\\Service' => 'ahrefs\\AhrefsSeo_Vendor\Google_Service', 'ahrefs\\AhrefsSeo_Vendor\\Google\\AccessToken\\Revoke' => 'ahrefs\\AhrefsSeo_Vendor\Google_AccessToken_Revoke', 'ahrefs\\AhrefsSeo_Vendor\\Google\\AccessToken\\Verify' => 'ahrefs\\AhrefsSeo_Vendor\Google_AccessToken_Verify', 'ahrefs\\AhrefsSeo_Vendor\\Google\\Model' => 'ahrefs\\AhrefsSeo_Vendor\Google_Model', 'ahrefs\\AhrefsSeo_Vendor\\Google\\Utils\\UriTemplate' => 'ahrefs\\AhrefsSeo_Vendor\Google_Utils_UriTemplate', 'ahrefs\\AhrefsSeo_Vendor\\Google\\AuthHandler\\Guzzle6AuthHandler' => 'ahrefs\\AhrefsSeo_Vendor\Google_AuthHandler_Guzzle6AuthHandler', 'ahrefs\\AhrefsSeo_Vendor\\Google\\AuthHandler\\Guzzle7AuthHandler' => 'ahrefs\\AhrefsSeo_Vendor\Google_AuthHandler_Guzzle7AuthHandler', 'ahrefs\\AhrefsSeo_Vendor\\Google\\AuthHandler\\Guzzle5AuthHandler' => 'ahrefs\\AhrefsSeo_Vendor\Google_AuthHandler_Guzzle5AuthHandler', 'ahrefs\\AhrefsSeo_Vendor\\Google\\AuthHandler\\AuthHandlerFactory' => 'ahrefs\\AhrefsSeo_Vendor\Google_AuthHandler_AuthHandlerFactory', 'ahrefs\\AhrefsSeo_Vendor\\Google\\Http\\Batch' => 'ahrefs\\AhrefsSeo_Vendor\Google_Http_Batch', 'ahrefs\\AhrefsSeo_Vendor\\Google\\Http\\MediaFileUpload' => 'ahrefs\\AhrefsSeo_Vendor\Google_Http_MediaFileUpload', 'ahrefs\\AhrefsSeo_Vendor\\Google\\Http\\REST' => 'ahrefs\\AhrefsSeo_Vendor\Google_Http_REST', 'ahrefs\\AhrefsSeo_Vendor\\Google\\Task\\Retryable' => 'ahrefs\\AhrefsSeo_Vendor\Google_Task_Retryable', 'ahrefs\\AhrefsSeo_Vendor\\Google\\Task\\Exception' => 'ahrefs\\AhrefsSeo_Vendor\Google_Task_Exception', 'ahrefs\\AhrefsSeo_Vendor\\Google\\Task\\Runner' => 'ahrefs\\AhrefsSeo_Vendor\Google_Task_Runner', 'ahrefs\\AhrefsSeo_Vendor\\Google\\Collection' => 'ahrefs\\AhrefsSeo_Vendor\Google_Collection', 'ahrefs\\AhrefsSeo_Vendor\\Google\\Service\\Exception' => 'ahrefs\\AhrefsSeo_Vendor\Google_Service_Exception', 'ahrefs\\AhrefsSeo_Vendor\\Google\\Service\\Resource' => 'ahrefs\\AhrefsSeo_Vendor\Google_Service_Resource', 'ahrefs\\AhrefsSeo_Vendor\\Google\\Exception' => 'ahrefs\\AhrefsSeo_Vendor\Google_Exception'];
foreach ($classMap as $class => $alias) {
    \class_alias($class, $alias);
}
/**
 * This class needs to be defined explicitly as scripts must be recognized by
 * the autoloader.
 */
class Google_Task_Composer extends \ahrefs\AhrefsSeo_Vendor\Google\Task\Composer
{
}
/** @phpstan-ignore-next-line */
if (\false) {
    class Google_AccessToken_Revoke extends \ahrefs\AhrefsSeo_Vendor\Google\AccessToken\Revoke
    {
    }
    class Google_AccessToken_Verify extends \ahrefs\AhrefsSeo_Vendor\Google\AccessToken\Verify
    {
    }
    class Google_AuthHandler_AuthHandlerFactory extends \ahrefs\AhrefsSeo_Vendor\Google\AuthHandler\AuthHandlerFactory
    {
    }
    class Google_AuthHandler_Guzzle5AuthHandler extends \ahrefs\AhrefsSeo_Vendor\Google\AuthHandler\Guzzle5AuthHandler
    {
    }
    class Google_AuthHandler_Guzzle6AuthHandler extends \ahrefs\AhrefsSeo_Vendor\Google\AuthHandler\Guzzle6AuthHandler
    {
    }
    class Google_AuthHandler_Guzzle7AuthHandler extends \ahrefs\AhrefsSeo_Vendor\Google\AuthHandler\Guzzle7AuthHandler
    {
    }
    class Google_Client extends \ahrefs\AhrefsSeo_Vendor\Google\Client
    {
    }
    class Google_Collection extends \ahrefs\AhrefsSeo_Vendor\Google\Collection
    {
    }
    class Google_Exception extends \ahrefs\AhrefsSeo_Vendor\Google\Exception
    {
    }
    class Google_Http_Batch extends \ahrefs\AhrefsSeo_Vendor\Google\Http\Batch
    {
    }
    class Google_Http_MediaFileUpload extends \ahrefs\AhrefsSeo_Vendor\Google\Http\MediaFileUpload
    {
    }
    class Google_Http_REST extends \ahrefs\AhrefsSeo_Vendor\Google\Http\REST
    {
    }
    class Google_Model extends \ahrefs\AhrefsSeo_Vendor\Google\Model
    {
    }
    class Google_Service extends \ahrefs\AhrefsSeo_Vendor\Google\Service
    {
    }
    class Google_Service_Exception extends \ahrefs\AhrefsSeo_Vendor\Google\Service\Exception
    {
    }
    class Google_Service_Resource extends \ahrefs\AhrefsSeo_Vendor\Google\Service\Resource
    {
    }
    class Google_Task_Exception extends \ahrefs\AhrefsSeo_Vendor\Google\Task\Exception
    {
    }
    interface Google_Task_Retryable extends \ahrefs\AhrefsSeo_Vendor\Google\Task\Retryable
    {
    }
    class Google_Task_Runner extends \ahrefs\AhrefsSeo_Vendor\Google\Task\Runner
    {
    }
    class Google_Utils_UriTemplate extends \ahrefs\AhrefsSeo_Vendor\Google\Utils\UriTemplate
    {
    }
}

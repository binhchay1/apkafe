<?php

namespace ahrefs\AhrefsSeo_Vendor;

// For older (pre-2.7.2) verions of google/apiclient
if (\file_exists(__DIR__ . '/../apiclient/src/Google/Client.php') && !\class_exists('ahrefs\\AhrefsSeo_Vendor\\Google_Client', \false)) {
    require_once __DIR__ . '/../apiclient/src/Google/Client.php';
    if (\defined('Google_Client::LIBVER') && \version_compare(\ahrefs\AhrefsSeo_Vendor\Google_Client::LIBVER, '2.7.2', '<=')) {
        $servicesClassMap = ['ahrefs\\AhrefsSeo_Vendor\\Google\\Client' => 'Google_Client', 'ahrefs\\AhrefsSeo_Vendor\\Google\\Service' => 'Google_Service', 'ahrefs\\AhrefsSeo_Vendor\\Google\\Service\\Resource' => 'ahrefs\AhrefsSeo_Vendor\Google_Service_Resource', 'ahrefs\\AhrefsSeo_Vendor\\Google\\Model' => 'Google_Model', 'ahrefs\\AhrefsSeo_Vendor\\Google\\Collection' => 'Google_Collection'];
        foreach ($servicesClassMap as $alias => $class) {
            \class_alias($class, $alias);
        }
    }
}
\spl_autoload_register(function ($class) {
    if (0 === \strpos($class, 'ahrefs\AhrefsSeo_Vendor\Google_Service_')) {
        // Autoload the new class, which will also create an alias for the
        // old class by changing underscores to namespaces:
        //     Google_Service_Speech_Resource_Operations
        //      => Google\Service\Speech\Resource\Operations
        $classExists = \class_exists($newClass = 'ahrefs\\AhrefsSeo_Vendor\\' . \str_replace('_', '\\', str_replace( 'ahrefs\\AhrefsSeo_Vendor\\', '', $class )));
        if ($classExists) {
            return \true;
        }
    }
}, \true, \true);

<?php

// Config
require_once '/var/www/it-tools/.config.php';
require_once 'GoogleApi.php';
require_once 'MicrosoftApi.php';
require_once 'ZoomLicenseService.php'; // Updated to use the new ZoomLicenseService

// Applications Configuration
$applications = [
    'zoom' => [
        'apiClass' => 'ITESC\Services\ZoomLicenseService', // Updated to use the new ZoomLicenseService
        'productId' => 'Zoom-Video',
        'skuId' => 'Zoom-Video-Webinar',
        'customerId' => getVaultValue('zoom', 'domain'),
        'apiEndpoint' => 'https://api.example.com/zoom/licenses',
        'licenseCap' => 500,
    ],
    'google' => [
        'apiClass' => 'GoogleApi',
        'productId' => 'google-Apps',
        'skuId' => 'Google-Apps-For-Business',
        'customerId' => getVaultValue('google', 'domain'),
        'apiEndpoint' => 'https://api.example.com/google/licenses',
        'licenseCap' => 500,
    ],
    'microsoft-office' => [
        'apiClass' => 'MicrosoftApi',
        'productId' => 'Microsoft-Office',
        'skuId' => 'Microsoft-Office-365',
        'customerId' => getVaultValue('microsoft', 'domain'),
        'apiEndpoint' => 'https://api.example.com/office/licenses',
        'licenseCap' => 500,
    ]
];

// Functions
function createApiInstance($className, $config) {
    $apiInstance = new $className($config);
    $apiInstance->connect();
    return $apiInstance;
}

function fetchLicenseCount($apiInstance) {
    return $apiInstance->getLicenseCount();
}

function checkAndNotifySlackChannel($slackMessage, $licenseCount, $licenseCap, $date) {
    $licensesRemaining = $licenseCap - $licenseCount;

    if ($licensesRemaining <= 25) {
        // Notify Slack Channel 
    }
}

// Main Script
$scriptOps = new ScriptOps();
$_GET = $scriptOps->parseArgs($argv);

if (!isset($_GET[1])) die('Argument 1 needs to be set (action)');

$action = strtolower($_GET[1]);

foreach ($applications as $app => $appConfig) {
    $app = strtolower($app);

    $date = $scriptOps->getDBDateTime();

    // Access the license cap for the specific application
    $licenseCap = $appConfig['licenseCap'];

    // Create API Instance
    $apiInstance = createApiInstance($appConfig['apiClass'], $appConfig);

    // Get the license count from the API
    $licenseCount = fetchLicenseCount($apiInstance);

    // Compare the license count with the license cap
    if ($licenseCount >= $licenseCap) {
        // Threshold met, notify Slack channel
    }
}
<?php

// Config
require_once '/var/www/it-tools/.config.php';

// Application Configurations
$applications = [
    'google' => [
        'productId' => 'Google-Apps',
        'skuId' => 'Google-Apps-For-Business',
        'customerId' => getVaultValue('google', 'domain'),
        'apiClass' => 'GoogleInstance',
        'licenseCap' => 3481
        'archiveCap' => 500
    ],
    'microsoft' => [
        'productId' => 'Microsoft-Office',
        'skuId' => 'Microsoft-Office-365',
        'customerId' => getVaultValue('microsoft', 'domain'),
        'apiClass' => 'MicrosoftInstance',
        'licenseCap' => 5000 // Example value
    ]
    'zoom' => [
        'productId' => 'Zoom-Video',
        'skuId' => 'Zoom-Video-Webinar',
        'customerId' => getVaultValue('zoom', 'domain'),
        'apiClass' => 'ZoomInstance',
        'licenseCap' => 1000 // Example value
    ], //add more applications here
];

// Get Arguments
$so = new ScriptOps();
$_GET = $so->parseArgs($argv);

if (!isset($_GET[1])) die('Argument 1 needs to be set (action)');
if (!isset($_GET[2])) die('Argument 2 needs to be set (application)');

$action = strtolower($_GET[1]);
$app = strtolower($_GET[2]);

if (!isset($applications[$app])) die('Unsupported application');

$appConfig = $applications[$app];
$date = $so->getDBDateTime();

// Access the license cap for the specific application
$licenseCap = $appConfig['licenseCap'];

// Create API Instance
$apiInstance = createApiInstance($appConfig['apiClass']);
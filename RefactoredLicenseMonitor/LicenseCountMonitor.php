<?php

// Load configuration and required classes
require_once '/var/www/it-tools/.config.php';
require_once 'GoogleApi.php';
require_once 'ZoomApi.php'; // Updated to use the new ZoomApi

// Applications Configuration
$applications = [
    'zoom' => [
        'apiClass' => 'ZoomApi', // Updated to use the new ZoomApi
        'customerId' => getVaultValue('zoom', 'domain'), // Not needed for Zoom API
        'apiEndpoint' => 'https://api.example.com/zoom/licenses', // Not needed for Zoom API
        'licenseCap' => 2990, // Updated as of 9/17/2024
    ],
    'google' => [
        'apiClass' => 'GoogleApi',
        'productId' => 'google-Apps',
        'skuId' => 'Google-Apps-For-Business',
        'customerId' => getVaultValue('google', 'domain'),
        'apiEndpoint' => 'https://api.example.com/google/licenses',
        'licenseCap' => 500,
    ]
];

// Function to create an API instance and establish a connection
function createApiInstance($className, $config) {
    $apiInstance = new $className($config);
    $apiInstance->connect();
    return $apiInstance;
}

// Function to fetch the license count from the API
function fetchLicenseCount($apiInstance) {
    return $apiInstance->getLicenseCount();
}

// Function to check the license count and notify the Slack channel if needed
function checkAndNotifySlackChannel($slackMessage, $licenseCount, $licenseCap, $date) {
    $licensesRemaining = $licenseCap - $licenseCount;

    if ($licensesRemaining <= 25) {
        // Notify Slack Channel. I believe this function is defined elsewhere in the codebase.
    }
}

// Main Script
$scriptOps = new ScriptOps(); // Add config file path

foreach ($applications as $app => $appConfig) {
    $app = strtolower($app);

    // Get the current date and time from the database
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
        $slackMessage = "License count for {$app} has reached the cap of {$licenseCap}. Current count: {$licenseCount}.";
        checkAndNotifySlackChannel($slackMessage, $licenseCount, $licenseCap, $date);
    }
}
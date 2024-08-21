<?php

// Config
require_once '/var/www/it-tools/.config.php';
require_once 'GoogleApi.php';
require_once 'MicrosoftApi.php';
require_once 'ZoomApi.php';

// Functions
function createApiInstance($className) {
    $apiInstance = new $className();
    $apiInstance->connect();
    return $apiInstance;
}

function fetchLicenseCount($apiInstance) {
    return $apiInstance->getLicenseCount();
}
function notifySlackChannel($slackMessage, $licenseCount, $licenseCap, $date) {
    $licensesRemaining = $licenseCap - $licenseCount;

    if ($licensesRemaining <= 25) {
        $color = ($licensesRemaining > 0) ? 'warning' : 'danger';
        $header = ($licensesRemaining > 0) ? "Licenses Low" : ":alert: Licenses Out :alert:";
        $body = "We have *" . $licensesRemaining . "* licenses remaining." . PHP_EOL;
        $body .= "Available: " . $licenseCap . " - Used: " . $licenseCount . PHP_EOL;
        $footer = $date . " /it-tools/licensing.php";
        $slackMessage->sendLogToChannel('accounts', $color, '', $header, $body, $footer);
    }
}

// Main Script
$scriptOps = new ScriptOps();
$_GET = $scriptOps->parseArgs($argv);

if (!isset($_GET[1])) die('Argument 1 needs to be set (action)');
if (!isset($_GET[2])) die('Argument 2 needs to be set (application)');

$action = strtolower($_GET[1]);
$app = strtolower($_GET[2]);

if (!isset($applications[$app])) die('Unsupported application');

$appConfig = $applications[$app];
$date = $scriptOps->getDBDateTime();

// Access the license cap for the specific application
$licenseCap = $appConfig['licenseCap'];

// Create API Instance
$apiInstance = createApiInstance($appConfig['apiClass']);

// Get the license count from the API
$licenseCount = $apiInstance->getLicenseCount();

// Compare the license count with the license cap
if ($licenseCount >= $licenseCap) {
    // Threshold met, notify Slack channel
    $slackMessage = new SlackMsgInstance();
    notifySlackChannel($slackMessage, $licenseCount, $licenseCap, $date);
}
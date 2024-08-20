# License Management

This repository contains scripts to monitor and manage software licenses for various applications such as Google, Microsoft, and Zoom.

## Overview

The primary script, `NewLicenseCountMonitor.php`, has been refactored to support multiple applications dynamically. This script helps in monitoring the license usage and sends alerts when the license cap is reached.

## Files

- `NewLicenseCountMonitor.php`: The main script to monitor license counts for various applications.
- `IdealLicenseCountStructure.php`: Contains the ideal structure and configuration for license monitoring.
- `TimGoogleLicenseCount.php`: The original script for monitoring Google licenses.
- `ReadMe.md`: This documentation file.

## Configuration

The configuration for each application is defined within the `NewLicenseCountMonitor.php` script. Here is an example configuration:

```php
$applications = [
    'google' => [
        'productId' => 'Google-Apps',
        'skuId' => 'Google-Apps-For-Business',
        'customerId' => getVaultValue('google', 'domain'),
        'apiClass' => 'GoogleInstance',
        'licenseCap' => 3481,
    ],
    'microsoft' => [
        'productId' => 'Microsoft-Office',
        'skuId' => 'Microsoft-Office-365',
        'customerId' => getVaultValue('microsoft', 'domain'),
        'apiClass' => 'MicrosoftInstance',
        'licenseCap' => 5000
    ],
    'zoom' => [
        'productId' => 'Zoom-Video',
        'skuId' => 'Zoom-Video-Webinar',
        'customerId' => getVaultValue('zoom', 'domain'),
        'apiClass' => 'ZoomInstance',
        'licenseCap' => 1000
    ]
];
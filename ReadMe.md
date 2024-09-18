# License Management

This repository contains scripts to monitor and manage software licenses for various applications such as Google, Microsoft, and Zoom.

## Overview

The primary script, `LicenseCountMonitor.php`, has been refactored to support multiple applications dynamically. This script helps in monitoring the license usage and sends alerts when the license cap is reached.

## Files

- `LicenseCountMonitor.php`: The main script to monitor license counts for various applications.
- `IdealLicenseCountStructure.php`: Contains the ideal structure and configuration for license monitoring.
- `ZoomApi.php`: The updated service class for managing Zoom licenses.
- `GoogleApi.php`: The service class for managing Google licenses.

## Project Structure

The project is organized into several files and directories to manage different aspects of license monitoring. Below is an example of the project structure:

```txt
License-Management/
├── ReadMe.md
├── IdealLicenseCountStructure.php
├── RefactoredLicenseMonitor/
│   ├── LicenseCountMonitor.php
│   ├── ZoomApi.php
│   ├── GoogleApi.php
├── TimExistingCodeFiles/
│   ├── TimExistingGoogleScript.php
│   ├── TimExistingZoomScript.php
```

## Requirements

- PHP 7.4 or higher
- Composer
- GuzzleHttp
- Firebase JWT

## Installation

1. Clone the repository:
   ```sh
   git clone https://github.com/yourusername/license-management.git
   cd license-management
   ```
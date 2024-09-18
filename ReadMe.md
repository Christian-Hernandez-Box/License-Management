# License Management

This repository contains scripts to monitor and manage software licenses for various applications such as Google, Microsoft, and Zoom.

## Overview

The primary script, `LicenseCountMonitor.php`, has been refactored to support multiple applications dynamically. This script helps in monitoring the license usage and sends alerts when the license cap is reached.

## Files

- `LicenseCountMonitor.php`: The main script to monitor license counts for various applications.
- `IdealLicenseCountStructure.php`: Contains the ideal structure and configuration for license monitoring.
- `ZoomLicenseService.php`: The updated service class for managing Zoom licenses.
- `GoogleService.php`: The service class for managing Google licenses.
- `MicrosoftApi.php`: The service class for managing Microsoft licenses.

## Project Structure

The project is organized into several files and directories to manage different aspects of license monitoring. Below is an example of the project structure:

```txt
License-Management/
├── ReadMe.md
├── IdealLicenseCountStructure.php
├── RefactoredLicenseMonitor/
    ├── LicenseCountMonitor.php
    ├── ZoomService.php
    ├── GoogleService.php
    ├── MicrosoftService.php
├── TimExistingCodeFiles/
    ├── TimExistingGoogleScript.php
    ├── TimExistingZoomScript.php
```

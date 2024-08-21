# License Management

This repository contains scripts to monitor and manage software licenses for various applications such as Google, Microsoft, and Zoom.

## Overview

The primary script, `NewLicenseCountMonitor.php`, has been refactored to support multiple applications dynamically. This script helps in monitoring the license usage and sends alerts when the license cap is reached.

## Files

- `NewLicenseCountMonitor.php`: The main script to monitor license counts for various applications.
- `IdealLicenseCountStructure.php`: Contains the ideal structure and configuration for license monitoring.
- `TimGoogleLicenseCount.php`: The original script for monitoring Google licenses.
- `ReadMe.md`: This documentation file.

## Project Structure

The project is organized into several files and directories to manage different aspects of license monitoring. Below is an example of the project structure:

```txt
License-Management/
├── ReadMe.md
├── Notes.txt
├── TimGoogleLicenseCount.php
├── IdealLicenseCountStructure.php
├── RefactoredLicenseMonitor/
    ├── NewLicenseCountMonitor.php
    ├── GoogleApi.php
    ├── MicrosoftApi.php
    ├── ZoomApi.php
```
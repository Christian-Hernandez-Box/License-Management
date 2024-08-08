<?php

	// Config
	require_once '/var/www/it-tools/.config.php';

	// Script Config
    // License Cap
    // Needs to be changed when more licenses are purchased
    $licenseCap = 3481;
	$archiveCap = 500;

	$productId 	= "Google-Apps";
	$skuId 		= "Google-Apps-For-Business";
	$customerId = getVaultValue('google','domain');

	//Get Arguments
	$so = new ScriptOps();
	$_GET = $so->parseArgs($argv);

	if(!isset($_GET[1])) die ('Argument 1 needs to be set (action)');

    $date = $so->getDBDateTime();

	// Create Google API Instance
	$google = new GoogleInstance();
	$google->connect();
	$google_lic = $google->licensing();
	$gdir = $google->directory();

	switch(strtolower($_GET[1])) {

        case 'auto':
			monitorTotalLicenses($google_lic, $productId, $customerId);

			monitorArchivedUsers();

			break;

		case 'check':
			$gappsCount = getGoogleLicenses($google_lic, $productId, $customerId);

			echo 'Google License Count: ' . $gappsCount . PHP_EOL;
            echo 'Google License Cap: ' . $licenseCap . PHP_EOL;

			break;

		case 'delete':
			if(!isset($_GET[2])) die ('Argument 2 needs to be set (userid)');

			$result = $google_lic->licenseAssignments->delete($productId, $skuId, $_GET[2]);

			print_r($result);

			break;

		case 'add':
			if(!isset($_GET[2])) die ('Argument 2 needs to be set (userid)');

			$licAssign = new Google_Service_Licensing_LicenseAssignmentInsert();
			$licAssign->setUserId($_GET[2]);
			$result = $google_lic->licenseAssignments->insert($productId, $skuId, $licAssign);

			print_r($result);

			break;
	}

	function monitorTotalLicenses($google_lic, $productId, $customerId) {
		global $licenseCap, $date;

		$gappsCount = getGoogleLicenses($google_lic, $productId, $customerId);

		//echo 'GApps License Count: ' . $gappsCount . PHP_EOL;

		$licensesRemaining = $licenseCap - $gappsCount;
		if ($licensesRemaining <= 25) {
			$sm = new SlackMsgInstance();

			if(($licenseCap - $gappsCount) > 0) {
				$color = 'warning';
				$header = "G Suite Licenses Low";
			} else {
				$color = 'danger';
				$header = ":alert: G Suite Licenses Out :alert:";
			}

			$body = "We have *" . $licensesRemaining . "* Google Workspace licenses remaining." . PHP_EOL;
			$body .= "Available: " . $licenseCap . " - Used: " . $gappsCount . PHP_EOL;

			$footer = $date . " LV7-IT-PRODTOOLS1 /it-tools/google/licensing.php";
			$sm->sendLogToChannel('accounts', $color, '', $header, $body, $footer);
		}
	}

	function monitorArchivedUsers() {
		global $gdir, $archiveCap;

		$qry = 'isArchived=true';
		$archivedUsers = searchUsers($qry, $gdir);
		
		if(count($archivedUsers) > $archiveCap) {
			//Pick any x users with archived licenses, where x is the number over the cap, and convert them to suspended
			$archiveExcess = count($archivedUsers) - $archiveCap;

			foreach($archivedUsers as $user) {
				$user->archived = false;
				$user->suspended = true;
				
				$options = [];
				$gdir->users->patch($user);

				//Check if we still have any excess archived licenses left
				$archiveExcess--;
				if($archiveExcess < 1) {
					break;
				}
			}
		}
	}

	function getGoogleLicenses($google_lic, $productId, $customerId) {
		$licenses = $google_lic->licenseAssignments->listForProduct($productId, $customerId);

		$gappsCount = 0;

		while(true) {
			foreach ($licenses->getItems() as $license) {
				//echo $license->getSummary() . "\n";
				$gappsCount++;
			}
			$pageToken = $licenses->getNextPageToken();
			if ($pageToken) {
				$optParams = array('pageToken' => $pageToken);
				$licenses = $google_lic->licenseAssignments->listForProduct($productId, $customerId, $optParams);
			} else {
				break;
			}
		}

		return $gappsCount;
	}

	function searchUsers($qry, $gdir) {
		//Search users for the alias
		try {
			//Call group
			$query = rawurlencode($qry);
	
			$optParams = [
				'domain' => 'box.com', // Set the domain to 'box.com'
				'query' => $query, // Use the provided query
				'pageToken' => $results['nextPageToken'] // Use the next page token from the previous results
			];
	
			// Fetch the list of users with the specified options
			$results = $gdir->users->listUsers($optParams);
	
			// Iterate through the list of users in the results
			foreach($results['users'] as $user) {
				// Add each user to the $users array
				array_push($users, $user);
			}
		} catch(Google_Service_Exception $e) {
			// Handle Google service-specific exceptions
			// Error message is formatted as "Error calling <REQUEST METHOD> <REQUEST URL>: (<CODE>) <MESSAGE OR REASON>".
			echo 'No users exist' . "Error message: " . $e->getMessage() . "\n" . PHP_EOL;
		} catch (Google_Exception $e) {
			// Handle general Google exceptions
			echo 'No users exist' . "An error occurred: (" . $e->getCode() . ") " . $e->getMessage() . PHP_EOL;
		}
	
		// Return the list of users
		return $users;
	}
<?php
	
	// grab user input if present (optional)
	$comm = $argv[1];

	// display the about info for this extension
	if ($comm == "about") {

		echo "Extension Updater is an extension created by David Ferguson (@jdfwarrior) that makes it easy to update your Alfred extensions. This extension will scan Alfred for supporting extensions, and then check for updates. If an update is available, the update will automatically download and install.";
		exit(1);
		
	} // end if about

	// display the version info for this extension
	else if ($comm == "version") {
			
		// if update.xml exists, display version info
		if (file_exists("update.xml")) {
			$xml = simplexml_load_file("update.xml");
			echo "Extension Updater $xml->version";
		}

		// if update.xml doesn't exist, show an error
		else {
			echo "No version information found for this extension";
		}

		exit(1);

	} // end else version

	// display the changelog for this extension
	else if ($comm == "changelog") {
		
		// if changelog exists, show contents
		if (file_exists("changelog.txt")) {

			$f = fopen("changelog.txt", "r");
			while ($output = fgets($f)) {
				echo $output."\r";
			}
			fclose($f);

		}

		// if changelog doesn't exist, display error
		else {
			echo "No changelog found.";
		}
		exit(1);

	} // end else changelog

	// display help menu to user
	else if ($comm == "help") {
		
		echo "¬ update list - List extensions that use Extension Update\r";
		echo "¬ update check - Check for available updates\r";
		echo "¬ update - Update all available extensions\r";
		echo "¬ update about - About extension\r";
		echo "¬ update version - Extension version\r";
		echo "¬ update changelog - Display changelog\r";
		echo "¬ update help - Display help menu\r";
		exit(1);	
		
	} //end else help

	// Get the current directory
	$cd = getcwd();

	// Use glob to find all valid extensions with update.xml
	// and get the directory they're in
	$dirs = array();
	foreach(glob("$cd/../../*/*/update.xml") as $xml) {
		$dirs[] = dirname(realpath($xml));
	}

	$updates = false;


	// Extension Update
	// 1. Search through each script directory searching for an update.xml
	// 2. If an update.xml is available, read the current version and remote xml path.
	// 3. Check remote xml verion against local
	// 4. If a new version is available, read remote xml update url
	// 5. Download updated extension
	// 6. Unzip extension
	foreach($dirs as $ext):

		$dir = $ext['name'];
		$path = $ext['path'];
		// Check for the existence of update.xml in the path
		if (file_exists($dir."/"."update.xml")) {

			// Read the local version and update url for the extension
			$lxml 	  = simplexml_load_file($dir."/"."update.xml");
			$lversion = floatval($lxml->version);
			$lurl 	  = $lxml->url;
			
			if ($comm != "list") {

				// Read the remote version and update url for the extension}
				$rxml 	  = @simplexml_load_file($lurl);
			
				if ($rxml != false) {
				
					$rversion = floatval($rxml->version);
					$rurl 	  = $rxml->url;

					// If a new version exists, update
					if ($lversion < $rversion) {
					
						// Set flag indicating that updates were found and then save
						// remote filename
						$updates = true;
						$file = basename($rurl);

						if ($comm != "check") {
							// Download the remote file via cURL then unzip and remove the
							// newly downloaded extension
							exec("curl -s '$rurl' > '$dir/$file'");
							if (file_exists("$dir/$file")) {
						
								str_replace("%20", " ", $file);
								exec("unzip -q -o  '$dir/$file' -d '$dir/'");
								exec("rm '$dir/$file'");

							}

							// Inforom the user that the extension was updated
							$lxml 	  = simplexml_load_file($dir."/"."update.xml");
							$lversion = floatval($lxml->version);
							if ($lversion == $rversion) {
								echo "Updated $dir\r";
							}
							else {
								echo "Error updating $dir from $lversion to $rversion\r";
							}
						}
						else if ($comm == "check") {
						
							echo "¬ $dir $rversion is available.\r";
							if (isset($rxml->comments)) {
								echo $rxml->comments."\r";
							}

						}

					}

				}
			}
			
			else {
				echo "¬  $dir $lversion\r";
			}

		}
	endforeach;

	// If no updates were found, inform the user
	if (!$updates && $comm != "list") { echo "No updates available"; }

?>
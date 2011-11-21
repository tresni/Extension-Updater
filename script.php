<?php

	/**
	* Get the current directory
	*/
	$cd = getcwd();

	/**
	* Split the full path into an array so that the current folder can be removed
	* returning the parent folder.
	*/
	$path = explode("/", $cd);

	/**
	* Count the number of elements in the full path so that the current folder can be removed
	* and return only the parent
	*/
	$size = count($path);
	$size--;
	unset($path[$size]);

	/**
	* Glue the full path back together for the parent folder
	*/
	$path = implode("/", $path);

	/**
	* Get a list of all elements in the parent folder
	*/
	$dirs = scandir($path);
	$inc = 0;

	/**
	* Remove all items in the list that are not of interest (files and the parent items)
	*/
	foreach($dirs as $dir):
		if (!is_dir($path."/".$dir) || $dir == "." || $dir == "..") { unset($dirs[$inc]); }
		$inc++;
	endforeach;

	$updates = false;

	/**
	* Extension Update
	* 1. Search through each script directory searching for an update.xml
	* 2. If an update.xml is available, read the current version and remote xml path.
	* 3. Check remote xml verion against local
	* 4. If a new version is available, read remote xml update url
	* 5. Download updated extension
	* 6. Unzip extension
	*/
	foreach($dirs as $dir):

		/**
		* Check for existence of update.xml
		*/
		if (file_exists($path."/".$dir."/"."update.xml")) {

			/**
			* Read local version and update url
			*/
			$lxml 	  = simplexml_load_file($path."/".$dir."/"."update.xml");
			$lversion = floatval($lxml->version);
			$lurl 	  = $lxml->url;

			/**
			* Read remote version and url
			*/
			$rxml 	  = simplexml_load_file($lurl);
			$rversion = floatval($rxml->version);
			$rurl 	  = $rxml->url;

			/**
			* If new version exists, update
			*/
			if ($lversion < $rversion) {
				
				/**
				* Set flag indicating that updates were found and then save
				* remote filename
				*/
				$updates = true;
				$file = basename($rurl);

				/**
				* Download the remote file via cURL then unzip and remove the
				* newly downloaded extension
				*/
				exec("curl \"$rurl\" > \"$path/$dir/$file\"");
				if (file_exists("$path/$dir/$file")) {
					exec("unzip -o \"$path/$dir/$file\" -d \"$path/$dir/\"");
					exec("rm \"$path/$dir/$file\"");
				}

				/**
				* Inform user that the extension was updated.
				*/
				$lxml 	  = simplexml_load_file($path."/".$dir."/"."update.xml");
				$lversion = floatval($lxml->version);
				if ($lversion == $rversion) {
					echo "Updated $dir\r";
				}
				else {
					echo "Error updating $dir\r";
				}
			}

		}
	endforeach;

	/**
	* If no available updates were found, inform the user
	*/
	if (!$updates) { echo "No updates available"; }

?>
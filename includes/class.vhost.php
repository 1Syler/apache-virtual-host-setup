<?php
/**
 * class.vhost.php
 *
 * Creates a new virtual host for Apache server.
 *
 */

class Vhost extends VhostConfig
{
	/*
	* Private variables
	*
	* @var bool $vhostDirCreated.
	*
	*/
	private $vhostDirCreated = FALSE;
	private $projectDirCreated = FALSE;
	private $projectConFileCreated = FALSE;
	private $apacheConFileEdited = FALSE;
	private $hostsFileEdited = FALSE;
	
	private function setVhostDirCreated($bool) {
  		$this->vhostDirCreated = $bool;
	}
	
	private function setProjectDirCreated($bool) {
  		$this->projectDirCreated = $bool;
	}
	
	private function setProjectConFileCreated($bool) {
  		$this->projectConFileCreated = $bool;
	}
	
	private function setApacheConFileEdited($bool) {
  		$this->apacheConFileEdited = $bool;
	}
	
	private function setHostsFileEdited($bool) {
  		$this->hostsFileEdited = $bool;
	}
	
	
	/**
	* Creates the virtual hosts directory that contains the project directories.
	*
	* @param string $vhostDir is the name of the virtual hosts directory.
	* @return FALSE if there was an error, TRUE otherwise.
	*/
	public function createVhostsDir() {
        displayMsg("Creating the virtual hosts directory", "93");
        
        // Create the virtual hosts directory.
	    if(!mkdir($this->vhostDir, 0755, FALSE)) {
            $this->setError("Error creating the virtual hosts directory '".$this->vhostDir."'");
            return FALSE;
        }
        
        // Set virtual hosts directory created to TRUE.
        $this->setVhostDirCreated(TRUE);
        
        // Take Ownership of the virtual host directory.
        if(!$this->takeOwnership($this->vhostDir, "directory")) {
            return FALSE;
        }
        
        displayMsg("The virtual hosts directory was successfully created", "32");
        return TRUE;
	}
	
	/**
	* Creates the new hosts project directory and any nested directories.
	*
	* @param string $fullPath is the full path of the project directory.
	* @param string $projectDir is the path of the project directory and any nested directories.
	* @return FALSE if there was an error, TRUE otherwise.
	*/
	public function createProjectDir() {
        displayMsg("Creating the project directory", "93");

        // Create the projects directory.
	    if(!mkdir($this->fullPathProjectDir, 0755, TRUE)) {
            $this->setError("Error creating the project directory '$this->fullPathProjectDir'");
            return FALSE;
        }
        
        // Set project directory created to TRUE.
        $this->setProjectDirCreated(TRUE);
        
        // Get the number of directories.
        $num = count(array_filter(explode("/", $this->projectDir)));
        
        // Take ownership of each directory.
        $fullPath = $this->fullPathProjectDir;
        for($i = 0; $i < $num; $i++) {
            if(!$this->takeOwnership($fullPath, "directory")) {
                return FALSE;
            }
            $fullPath = dirname($fullPath);
        }
        
        displayMsg("The project directory was successfully created", "32");
        return TRUE;
	}
	
	/**
	* Creates the projects config file.
	*
	* @return FALSE if there was an error, TRUE otherwise.
	*/
	public function createConFile() {
        displayMsg("Creating the vitrtual hosts config file", "93");

        // Get the default config file contents.
	    if(!$lines = file($this->defaultConFile, FILE_IGNORE_NEW_LINES)) {
            $this->setError("Error getting the contents of the default config file '".$this->defaultConFile."'");
            return FALSE;
	    }
	    
        // Find and edit the lines in the default config file.
	    $serverName = $serverAdmin = $documentRoot = FALSE;
        for($i = 0; $i < count($lines); $i++) {
            // Edit the Sever Name.
			if(strpos($lines[$i], "#ServerName") !== FALSE) {
                $lines[$i] = "\tServerName ".$this->domainName;
                
                // Add server alisas to the config file.
                $newline = "\tServerAlias www.".$this->domainName;
                array_splice($lines, $i+1, 0, $newline);
                $serverName = TRUE;
			}
            // Edit the Sever Admin.
			if(strpos($lines[$i], "ServerAdmin") !== FALSE) {
                $lines[$i] = "\tServerAdmin admin@".$this->domainName;
                $serverAdmin = TRUE;
            }
            // Edit the Document Root.
			if(strpos($lines[$i], "DocumentRoot") !== FALSE) {
                $lines[$i] = "\tDocumentRoot '".$this->fullPathProjectDir."'";
                $documentRoot = TRUE;
            }
        }
        
        // Check if all the lines were edited.
        if($serverName != TRUE || $serverAdmin != TRUE || $documentRoot != TRUE) {
            $this->setError("Error editing the default config file '".$this->defaultConFile."'");
            return FALSE;
        }
        
        // Create the new config file.
        if(file_put_contents($this->projectConFile, implode( "\n", $lines)) === FALSE) {
            $this->setError("Error writing the contents to '".$this->projectConFile."'");
            return FALSE;
        }
        
        // Set project config file created to TRUE.
        $this->setProjectConFileCreated(TRUE);
        
        displayMsg("The config file was successfully created", "32");
        return TRUE;
	}
	
	/**
	* Edits the apache config file to allow access for the new virtual host.
	*
	* @var string $this->apacheConFile the path of the apache config file.
	* @var string $this->projectDir is the path of the projects directory and any nested directories.
	* @return FALSE if there was an error, TRUE otherwise.
	*/
	public function allowVhostAccess() {
        displayMsg("Backing up the apache config file", "93");
        if (!copy($this->apacheConFile, $this->apacheConFile.".bk")) {
            $this->setError("Error failed to backup the hosts file '".$this->apacheConFile."'");
            return FALSE;
        }
        displayMsg("Apache config file successfully backed up", "32");
        displayMsg("Allowing access for the new virtual host", "93");
        
        // Get the apache config file contents.
	    if(!$lines = file($this->apacheConFile, FILE_IGNORE_NEW_LINES)) {
            $this->setError("Error getting the contents of the apache config file '".$this->apacheConFile."'");
            return FALSE;
	    }
	    
        // Check if the virtual host directory already has access in the apache config file.
        $allowed = FALSE;
        for($i = 0; $i < count($lines); $i++) {
		    if(strpos($lines[$i], "<Directory ".$this->vhostDir.">") !== FALSE) {
                $allowed = TRUE;
                break;
		    }
		    if(strpos($lines[$i], "<Directory '".$this->vhostDir."'>") !== FALSE) {
                $allowed = TRUE;
                break;
		    }
	    }
        
        // Allow access for the virtual hosts directory.
        if(!$allowed) {
            $found = FALSE;
            for($i = 0; $i < count($lines); $i++) {
		        if(strpos($lines[$i], "</Directory>") !== FALSE) {
                    // Splice the new lines into the content array.
                    $newlines = ["\n<Directory '".$this->vhostDir."'>", "\tOptions Indexes FollowSymLinks", "\tAllowOverride None", "\tRequire all granted", "</Directory>"];
                    array_splice($lines, $i+1, 0, $newlines);
                    
                    $found = TRUE;
                    break;
                }
		    }
        
            // Check if the line was found and edited.
            if($found != TRUE) {
                $this->setError("Error editing the apache config file '".$this->apacheConFile."'");
                return FALSE;
            }
            
            // Create the edited config file.
            if(file_put_contents($this->apacheConFile, implode( "\n", $lines)) === FALSE) {
                $this->setError("Error writing the contents to '".$this->apacheConFile."'");
                return FALSE;
            }
        
            // Set apache config file edited to TRUE.
            $this->setApacheConFileEdited(TRUE);
        }
        
        displayMsg("Access was successfully allowed", "32");
        return TRUE;
	}
	
	/**
	* Edits the hosts file to add the new virtual host.
	*
	* @param string $hostsfile is the path of the hosts file.
	* @param string $domain is the domain name.
	* @param string $ip is the virtual hosts IP address.
	* @return FALSE if there was an error, TRUE otherwise.
	*/
	public function editHostsFile() {
        displayMsg("Backing up the hosts file", "93");
        if (!copy($this->hostsFile, $this->hostsFile.".bk")) {
            $this->setError("Error failed to backup the hosts file '".$this->hostsFile."'");
            return FALSE;
        }
        displayMsg("Hosts file successfully backed up", "32");
        displayMsg("Editing the hosts file to add the new virtual host", "93");
        
        // Get the hosts files contents.
	    if(!$lines = file($this->hostsFile, FILE_IGNORE_NEW_LINES)) {
            $this->setError("Error getting the contents of the hosts file '".$this->hostsFile."'");
            return FALSE;
	    }
        
        // Find the line number where to insert the new virtual host in the hosts file.
        $found = FALSE;
        for($i = 0; $i < count($lines); $i++) {
		    if($lines[$i] == "") {
                // Splice the new line into the content array.
                $newline = $this->vhostIp."\t".$this->domainName;
                array_splice($lines, $i, 0, $newline);
                
                $found = TRUE;
                break;
		    }
	    }
        
        // Create the edited hosts file.
        if(file_put_contents($this->hostsFile, implode( "\n", $lines)) === FALSE) {
            $this->setError("Error writing the contents to '".$this->hostsFile."'");
            return FALSE;
        }
        
        // Set hosts file edited to TRUE.
        $this->setHostsFileEdited(TRUE);
        
        displayMsg("The hosts file was successfully edited", "32");
        //return FALSE;
        return TRUE;
	}
	
	/**
	* Downloads Bootstrap and unzips it to the project folder.
	*
	* @param string $url is the URL of the Bootstrap files to download.
	* @return FALSE if there was an error, TRUE otherwise.
	*/
	public function getBootstrap() {
        displayMsg("Downloading and installing Bootstrap", "93");
        
        // Download the Bootstrap zip file.
        $path = $this->fullPathProjectDir."/bootstrap.zip";
        $fp = fopen($path, 'w');

        $ch = curl_init($this->bootstrapUrl);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $data = curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        
        // Extract the zip file contents.
        $zip = new ZipArchive;
        if($zip->open($path) === TRUE) {
            $zipDir = explode("/", $zip->getNameIndex(0));
            for($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                
                // Skip files not in $zipDir[0]
                if(strpos($name, "{$zipDir[0]}/") !== 0) continue;
                
                // Determine output filename (removing the $zipDir prefix)
                $file = $this->fullPathProjectDir.'/'.substr($name, strlen($zipDir[0])+1);
                
                // Create the directories if necessary
                $dir = dirname($file);
                if(!is_dir($dir)) {
	                if(!mkdir($dir, 0755, TRUE)) {
                        $this->setError("Error creating the directory '$dir'");
                        return FALSE;
                    }
                    
                    // Take Ownership of the directory.
                    if(!$this->takeOwnership($dir, "directory")) {
                        return FALSE;
                    }
                }
                
                // Read from Zip and write to disk
                if($dir != $this->fullPathProjectDir) {
                    $fpr = $zip->getStream($name);
                    $fpw = fopen($file, 'w');
                    
                    while ($data = fread($fpr, 1024)) {
                        fwrite($fpw, $data);
                        
                        // Take Ownership of the directory.
                        if(!$this->takeOwnership($file, "file")) {
                            return FALSE;
                        }
                    }
                    fclose($fpr);
                    fclose($fpw);
                }
            }
            
            $zip->close();
            unlink($path);
        }
        else {
            $this->setError("Error opening the zip file '$path'");
            return FALSE;
        }

        displayMsg("Bootstrap was successfully installed", "32");
        return TRUE;
	}
    
    // Empties a directory recursivly.
    private function emptyDir($fullPath) {
        $fileList = array_diff(scandir($fullPath), ["..", "."]);
        
        foreach($fileList as $file) {
            $path = "$fullPath$file";
            
            if(is_dir($path)) {
                $this->emptyDir("$path/");
                if(!rmdir($path)) {
                    $this->setError("Error removing the directory '$path'", "91");
                    return FALSE;
                }
            }
            else {
                if(!unlink($path)) {
                    $this->setError("Error removing the file '$path'", "91");
                    return FALSE;
                }
            }
        }
        return TRUE;
    }
    
    // Delete the virtual host
    public function deleteHost() {
        displayMsg("Deleting the virtual host", "93");
        $savedConFile = $this->scriptDir."/saved/" . $this->saveFile . ".conf";
        
        // Check if the saved config file exists.
	    if(!file_exists($savedConFile)) {
            $this->setError("Error the saved config file does not exist '$savedConFile'");
            return FALSE;
	    }
        
        // Get the saved config files contents.
	    if(!$lines = file($savedConFile, FILE_IGNORE_NEW_LINES)) {
            $this->setError("Error getting the contents of the hosts file '$savedConFile'");
            return FALSE;
	    }
        
	    // Set the configuration variables needed for deletion.
	    foreach($lines as $line) {
	        // Set the parameter and value on each line of the saved file.
	        $param = substr($line, 0, strpos($line, "="));
	        $arg = substr($line, strpos($line, "=")+1);
	        
		    // Get the project directory.
		    if($param == "-P") {
		        $projectDir = "$arg/";
	        }
		    // Get the virtual hosts directory.
		    else if($param == "-V") {
		        $vhostDir = $arg;
	        }
		    // Get the apache config file.
		    else if($param == "-a") {
		        $apacheConFile = $arg;
	        }
		    // Get the projects config file.
		    else if($param == "-p") {
		        $projectConFile = $arg;
	        }
		    // Get the domain name.
		    else if($param == "-D") {
		        $domainName = $arg;
	        }
		    // Get the hosts file.
		    else if($param == "-h") {
		        $hostsFile = $arg;
	        }
        }
        
        // Disable the host
        exec("a2dissite " . pathinfo($projectConFile, PATHINFO_FILENAME));
        
        // Get the full path of the project directory.
        $fullPath = $this->joinPath($vhostDir, substr($projectDir, 0, strpos($projectDir, "/")));
        
        // Check if the project directory exists.
        if(!file_exists($fullPath)) {
            displayMsg("Error the project directory does not exist '$fullPath'", "91");
        }
        
        // Delete the project directory and any contents.
        if(!$this->emptyDir("$fullPath/")) {
            return FALSE;
        }
        if(!rmdir($fullPath)) {
            displayMsg("Error removing the project directory '$fullPath'", "91");
        }
        
        // Check if the virtual hosts directory path contains the users home directory and is empty.
        // Remove the directory if it is empty and remove access in the apache config file.
        if(strpos($vhostDir, $_SERVER['HOME']) !== FALSE) {
            if(count(array_diff(scandir($vhostDir), ["..", "."])) == 0) {
                if(!rmdir($vhostDir)) {
                    $this->setError("Error deleting the virtual hosts directory '$vhostDir'");
                    return FALSE;
                }
                
                // Check if the apache config file exists and get it's contents as an array of lines.
                if(!$lines = $this->getFileContents($apacheConFile)) {
                    return FALSE;
                }
                
                // Find the line with a matching domain and remove the line from the array.
                $vhostLine = "<Directory '$vhostDir'>";
                for($i = 0; $i < count($lines); $i++) {
			        if(strpos($lines[$i], $vhostLine) !== FALSE) {
			            unset($lines[$i]);
			            unset($lines[$i+1]); // "\tOptions Indexes FollowSymLinks"
			            unset($lines[$i+2]); // "\tAllowOverride None"
			            unset($lines[$i+3]); // "\tRequire all granted"
			            unset($lines[$i+4]); // "</Directory>"
			            unset($lines[$i+5]); // "\n"
			            break;
                    
			        }
                }
                
                // Create the edited apache config file.
                if(file_put_contents($apacheConFile, implode( "\n", $lines)) === FALSE) {
                    $this->setError("Error creating the apache config file '$apacheConFile'");
                    return FALSE;
                }
            }
        }
        
        // Delete the projects configuration file.
        if(!unlink($projectConFile)) {
            $this->setError("Error removing the projects config file '$projectConFile'", "91");
            return FALSE;
        }
                
        // Check if the hosts file exists and get it's contents as an array of lines.
        if(!$lines = $this->getFileContents($hostsFile)) {
            return FALSE;
        }
        
        // Find the line with a matching domain and remove the line from the array.
        for($i = 0; $i < count($lines); $i++) {
			if(strpos($lines[$i], $domainName) !== FALSE) {
			    unset($lines[$i]);
			    break;
			}
        }
        
        // Create the edited file.
        if(file_put_contents($hostsFile, implode( "\n", $lines)) === FALSE) {
            $this->setError("Error creating the hosts file '$hostsFile'");
            return FALSE;
        }
        
        // Restart the server
        exec("service apache2 restart");
        
        displayMsg("The virtual host was successfully deleted", "32");
        return TRUE;
    }
    
    private function getFileContents($file) {
        // Check if the file exists.
        if(!file_exists($file)) {
            $this->setError("Error '$file' does not exist");
            return FALSE;
        }
        
        // Backup the file.
        if(!copy($file, "$file.bk")) {
            $this->setError("Error failed to backup the file '$file'");
            return FALSE;
        }
        
        // Get the files contents.
	    if(!$lines = file($file, FILE_IGNORE_NEW_LINES)) {
            $this->setError("Error getting the contents of the file '$file'");
            return FALSE;
	    }
	    
	    return $lines;
    }
    
    // A function for cleaning up if the script fails at any point.
    public function cleanup() {
        displayMsg("Script failed. Cleaning up everything that was done", "97");
        
        // Remove the project directory if it was created.
        if($this->projectDirCreated) {
            $projectDir = $this->projectDir."/";
            $fullPath = $this->vhostDir.substr($projectDir, 0, strpos($projectDir, "/"));
            
            if(!$this->emptyDir("$fullPath/")) {
                return FALSE;
            }
            if(!rmdir($fullPath)) {
                $this->setError("Error removing the directory '$fullPath'", "91");
                return FALSE;
            }
        }
        
        // Remove the vitrtual host directory if it was created.
        if($this->vhostDirCreated) {
            if(!rmdir($this->vhostDir)) {
                displayMsg("Error removing the virtual hosts directory", "91");
            }
        }
        
        // Remove the project config file if it was created.
        if($this->projectConFileCreated) {
            if(!unlink($this->projectConFile)) {
                displayMsg("Error removing the project config file", "91");
            }
        }
        
        // Restore the apache config file.
        if($this->apacheConFileEdited) {
            if(!copy($this->apacheConFile.".bk", $this->apacheConFile)) {
                displayMsg("Error restoring apache config file backup", "91");
            }
        }
        
        // Restore the hosts file.
        if($this->hostsFileEdited) {
            if(!copy($this->hostsFile.".bk", $this->hostsFile)) {
                displayMsg("Error restoring hosts file backup", "91");
            }
        }
        
        displayMsg("Cleaning complete", "97");
    }
}

?>

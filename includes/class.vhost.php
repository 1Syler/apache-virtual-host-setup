<?php
/**
 * class.vhost.php
 *
 * Creates a new virtual host for Apache server.
 *
 */

class Vhost
{
	/*
	* Private variables
	*
	* @var string $error is an error message.
	*
	*/
	private $error;
	
	public function getError() {
  		return $this->error;
	}
	
	private function setError($error) {
  		$this->error = "\033[91m$error\033[0m\n";
	}
	
	/**
	* Creates the virtual hosts directory that contains the project directories.
	*
	* @param string $vhostDir is the name of the virtual hosts directory.
	* @return FALSE if there was an error, TRUE otherwise.
	*/
	public function createVhostsDir($vhostDir) {
        displayMsg("Creating the virtual hosts directory", "93");
        
        // Create the virtual hosts directory.
	    if(!mkdir($vhostDir, 0755, FALSE)) {
            $this->setError("Error creating the directory '$vhostDir'");
            return FALSE;
        }
        
        // Take Ownership of the virtual host directory.
        if(!$this->takeOwnership($vhostDir, "directory")) {
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
	public function createProjectDir($fullPath, $projectDir) {
        displayMsg("Creating the project directory", "93");

        // Create the projects directory.
	    if(!mkdir($fullPath, 0755, TRUE)) {
            $this->setError("Error creating the directory '$fullPath'");
            return FALSE;
        }
        
        // Take ownership of each directory.
        $dirs = array_filter(explode("/", $projectDir));
        $numDirs = count($dirs);
        for($i = 0; $i < $numDirs; $i++) {
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
	* @param string $projectConFile is the path of the projects config file.
	* @param string $defaultConFile is the path of the default config file.
	* @param string $projectDir is the path of the projects directory and any nested directories.
	* @param string $domainName is the domain name.
	* @return FALSE if there was an error, TRUE otherwise.
	*/
	public function createConFile($projectConFile, $defaultConFile, $projectDir, $domainName) {
        displayMsg("Creating the vitrtual hosts config file", "93");

        // Get the default config file contents.
	    if(!$content = file($defaultConFile, FILE_IGNORE_NEW_LINES)) {
            $this->setError("Error getting the contents of '$defaultConFile'");
            return FALSE;
	    }
        
        // Find the line number where to modify the server name.
        if(!$lineNum = $this->findLine("\t#ServerName www.example.com", $content, $defaultConFile)) {
            return FALSE;
        }
        // Modify the server name line.
        $content[$lineNum] = "\tServerName $domainName";
        
        // Add server alisas to the config file.
        $newline = "\tServerAlias www.$domainName";
        array_splice($content, $lineNum+1, 0, $newline);
        
        // Find the line number where to modify the server admin.
        if(!$lineNum = $this->findLine("\tServerAdmin webmaster@localhost", $content, $defaultConFile)) {
            return FALSE;
        }
        // Modify the document root line.
        $content[$lineNum] = "\tServerAdmin admin@$domainName";
        
        // Find the line number where to modify the document root.
        if(!$lineNum = $this->findLine("\tDocumentRoot /var/www/html", $content, $defaultConFile)) {
            return FALSE;
        }
        // Modify the document root line.
        $content[$lineNum] = "\tDocumentRoot '$projectDir'";
        
        // Create the new config file.
        if(file_put_contents($projectConFile, implode( "\n", $content)) === FALSE) {
            $this->setError("Error writing the contents to '$projectConFile'");
            return FALSE;
        }
        
        displayMsg("The config file was successfully created", "32");
        return TRUE;
	}
	
	/**
	* Edits the apache config file to allow access for the new virtual host.
	*
	* @param string $apacheConFile the path of the apache config file.
	* @param string $projectDir is the path of the projects directory and any nested directories.
	* @return FALSE if there was an error, TRUE otherwise.
	*/
	public function allowVhostAccess($apacheConFile, $projectDir) {
        displayMsg("Backing up the apache config file", "93");
        if (!copy($apacheConFile, "$apacheConFile.bk")) {
            $this->setError("Error failed to backup the hosts file '$apacheConFile'");
            return FALSE;
        }
        displayMsg("Allowing access for the new virtual host", "93");
        
        // Get the apache config file contents.
	    if(!$content = file($apacheConFile, FILE_IGNORE_NEW_LINES)) {
            $this->setError("Error getting the contents of '$apacheConFile'");
            return FALSE;
	    }
        
        // Find the line number where to insert the directory access configuration.
        if(!$lineNum = $this->findLine("</Directory>", $content, $apacheConFile)) {
            return FALSE;
        }
        
        // Splice the new lines into the content array.
        $newlines = ["\n<Directory '$projectDir'>", "\tOptions Indexes FollowSymLinks", "\tAllowOverride None", "\tRequire all granted", "</Directory>\n"];
        array_splice($content, $lineNum+1, 0, $newlines);
        
        // Create the new config file.
        if(file_put_contents($apacheConFile, implode( "\n", $content)) === FALSE) {
            $this->setError("Error writing the contents to '$apacheConFile'");
            return FALSE;
        }
        
        displayMsg("Access was successfully allowed", "32");
        return TRUE;
	}
	
	/**
	* Edits the hosts file to add the nnew virtual host.
	*
	* @param string $hostsfile is the path of the hosts file.
	* @param string $domain is the domain name.
	* @param string $ip is the virtual hosts IP address.
	* @return FALSE if there was an error, TRUE otherwise.
	*/
	public function editHostsFile($hostsFile, $domain, $ip) {
        displayMsg("Backing up the hosts file", "93");
        if (!copy($hostsFile, "$hostsFile.bk")) {
            $this->setError("Error failed to backup the hosts file '$hostsFile'");
            return FALSE;
        }
        displayMsg("Editing the hosts file to add the new virtual host", "93");
        
        // Get the hosts files contents.
	    if(!$content = file($hostsFile, FILE_IGNORE_NEW_LINES)) {
            $this->setError("Error getting the contents of '$hostsFile'");
            return FALSE;
	    }
        
        // Find the line number where to insert the new virtual host in the hosts file.
        if(!$lineNum = $this->findLine("", $content, $hostsFile)) {
            return FALSE;
        }
        
        // Splice the new line into the content array.
        $newline = "$ip\t$domain";
        array_splice($content, $lineNum, 0, $newline);
        
        // Create the new hosts file.
        if(file_put_contents($hostsFile, implode( "\n", $content)) === FALSE) {
            $this->setError("Error writing the contents to '$hostsFile'");
            return FALSE;
        }
        
        displayMsg("The hosts file was successfully edited", "32");
        return TRUE;
	}
	
	/**
	* Downloads Bootstrap and unzips it to the project folder.
	*
	* @param string $url is the URL of the Bootstrap files to download.
	* @return FALSE if there was an error, TRUE otherwise.
	*/
	public function getBootstrap($url, $projectDir) {
        displayMsg("Downloading and installing Bootstrap", "93");
        
        // Download the Bootstrap zip file.
        $path = "$projectDir/bootstrap.zip";
        $fp = fopen($path, 'w');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $data = curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        
        // Extract the zip file contents.
        $zip = new ZipArchive;
        $zipDir = pathinfo($url, PATHINFO_FILENAME);
        if($zip->open($path) === TRUE) {
            for($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                
                // Skip files not in $source
                if(strpos($name, "{$zipDir}/") !== 0) continue;
                
                // Determine output filename (removing the $zipDir prefix)
                $file = $projectDir.'/'.substr($name, strlen($zipDir)+1);
                
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
                if($dir != $projectDir) {
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

        return TRUE;
	}
	
	/**
	* Takes ownership of a file or directory.
	*
	* @param string $path is the file or directory to take ownership of.
	* @param string $type either 'directory' or 'file'.
	* @return FALSE if there was an error, TRUE otherwise.
	*/
	public function takeOwnership($path, $type) {
        // Change the directory owner to the current user.
        if(!chown($path, $_SERVER['SUDO_USER'])) {
            $this->setError("Error setting the $type owner for '$path'");
            return FALSE;
        }
        // Change the directory group to the current user.
        if(!chgrp($path, $_SERVER['SUDO_USER'])) {
            $this->setError("Error setting the $type group for '$path'");
            return FALSE;
        }
        return TRUE;
	}
	
	/**
	* Finds a given line in a file.
	*
	* @param string $find the line to find.
	* @param array $lines an array of lines in the file.
	* @param string $file the path of the file.
	* @return FALSE if there was an error, TRUE otherwise.
	*/
	private function findLine($find, $lines, $file) {
        $lnum = 0;
        $found = FALSE;
        
        foreach($lines as $line)
        {
            if($line == $find)
            {
                $found = TRUE;
                break;
            }
            $lnum++;
        }
        
        if($found == FALSE) {
            $this->setError("Error finding the line '$find' to edit in '$file'");
            return FALSE;
        }
        return $lnum;
    }
}

?>

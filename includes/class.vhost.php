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
	    if(!$this->createDir($vhostDir, FALSE)) {
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
	    if(!$this->createDir($fullPath, TRUE)) {
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
        if(!$content = $this->getFileContents($defaultConFile)) {
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
        if(!$this->createFile($projectConFile, $content)) {
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
        echo exec("cp '$apacheConFile' '$apacheConFile.bk'");
        displayMsg("Allowing access for the new virtual host", "93");
        
        // Get the apache config file contents.
        if(!$content = $this->getFileContents($apacheConFile)) {
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
        if(!$this->createFile($apacheConFile, $content)) {
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
        echo exec("cp '$hostsFile' '$hostsFile.bk'");
        displayMsg("Editing the hosts file to add the new virtual host", "93");
        
        // Get the hosts files contents.
        if(!$content = $this->getFileContents($hostsFile)) {
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
        if(!$this->createFile($hostsFile, $content)) {
            return FALSE;
        }
        
        displayMsg("The hosts file was successfully edited", "32");
        return TRUE;
	}
	
	/**
	* Creates a directory(ies).
	*
	* @param string $dir is the path of the directory to create.
	* @param bool $recurse is TRUE if there are multiple directories to create. FALSE if just one directory.
	* @return FALSE if there was an error, TRUE otherwise.
	*/
	public function createDir($dir, $recurse) {
	    if(!mkdir($dir, 0755, $recurse)) {
            $this->setError("Error creating the directory '$dir'");
            return FALSE;
        }
        return TRUE;
	}
	
	/**
	* Get the contents of a given file.
	*
	* @param string $file is the path of a file.
	* @return FALSE if there was an error, TRUE otherwise.
	*/
	private function getFileContents($file) {
	    if(!$lines = file($file, FILE_IGNORE_NEW_LINES)) {
            $this->setError("Error getting the contents of '$file'");
            return FALSE;
	    }
	    return $lines;
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
	
	/**
	* Creates a file.
	*
	* @param string $file is the path of the file.
	* @param array $lines is an array of lines to put in the file.
	* @return FALSE if there was an error, TRUE otherwise.
	*/
    private function createFile($file, $lines) {
        if(file_put_contents($file, implode( "\n", $lines)) === FALSE) {
            $this->setError("Error writing the contents to '$file'");
            return FALSE;
        }
        return TRUE;
    }
}

?>

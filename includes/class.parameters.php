<?php
/**
 * class.parameters.php
 *
 * Checks and validates input parameters.
 *
 */
     
class Parameters
{
	/*
	* Private variables
	*
	* @var int    $argc is the the number of arguments.
    * @var array  $argv is an array of the arguments.
	* @var string $error is an error message.
	* @var string $vhostDir is the full path of the directory used to store the virtual hosts.
	*             This can be set by the user, the default is /var/www/.
    * @var bool   $vhostDirExists is set to FALSE when the virtual host directory doesn't exist.
	*             The default is set to TRUE.
	* @var string $projectDir is the name of the project directory and any nested directories to create in $vhostDir.
	*             This can be set by the user the default is $projectName.
	* @var string $projectConFile is the full path of the projects .conf file.
	*             This can be set by the user, the default is /etc/apache2/sites-available/[$projectDir].conf.
	* @var string $defaultConFile is the full path of the apache default vhost config file used to create the project config file.
	*             This can be set by the user, the default is /etc/apache2/sites-available/000-default.conf.
	* @var string $apacheConFile is the full path the apache default config file used to create access for the $vhostDir.
	*             This can be set by the user, the default is /etc/apache2/apache2.conf.
	* @var string $hostsFile is the full path of the systems hosts file.
	*             This can be set by the user, the default is /etc/hosts.
	* @var string $domainName is the domain name for the new project, it is also used to set other variable defaults.
	*             This must be set by the user, it will set $projectDir, $projectConFile if no arguments are passed in.
	* @var string $vhostIp is the IP address of the vhost used in the hosts file.
	*             This can be set by the user, the default is 127.0.0.1.
	* @var string $bootstrapUrl is the URL of the Bootstrap zip file to download.
	*             This can be set by the user.
	* @var bool   $save is set to TRUE when the user opts to save the config.
	*             The default is set to FALSE.
	* @var bool   $load is set to TRUE when the user opts to load the config.
	*             The default is set to FALSE.
	* @var bool   $load is set to TRUE when the user opts to load the config.
	*             The default is set to FALSE.
	* @var bool   $delete is set to TRUE when the user opts to show the saved configuration files.
	*             The default is set to FALSE.
    * @var string $saveFile is the name of the saved config minus the .conf extension.
	*
	*/
	private $argc;
	private $argv = [];
	private $error;
	private $vhostDir = "/var/www/";
	private $vhostDirExists = TRUE;
	private $projectDir;
	private $fullPathProjectDir;
	private $projectConFile;
	private $defaultConFile = "/etc/apache2/sites-available/000-default.conf";
    private $apacheConFile = "/etc/apache2/apache2.conf";
    private $hostsFile = "/etc/hosts";
	private $domainName;
	private $vhostIp = "127.0.0.1";
	private $bootstrapUrl;
	private $save = FALSE;
	private $load = FALSE;
	private $show = FALSE;
	private $delete = FALSE;
	private $saveFile;

	/*
	*
	* Sets $argc and $argv.
	* Check if the --help parameter was passed.
	* Check if the --load-config parameter was passed.
	* Check if the --show-save-files parameter was passed.
	* Check if the --delete-host parameter was passed.
	* Check if the required -D parameter was passed in.
	*
	* @param int $argc is the the number of arguments passed in.
	* @param array $argv is an array of the arguments passed in.
	* @param array $dir the directory path that avhs.php is being executed from..
	* @return void
	*
	*/
	public function __construct($argc, $argv) {
	    $this->argc = $argc;
	    $this->argv = $argv;

		// Check if specified parameters were passed in.
		$domainSet = FALSE;
		while($param = current($argv)) {
		    $arg = next($argv);
		    
		    // Check for the help parameter
			if(strpos($param, "--help") !== FALSE) {
			    $this->helpMsg();
			    exit;
			}
			
		    // Check for the load parameter
			if(strpos($param, "--load-config") !== FALSE) {
				$this->setLoad(TRUE);
				$this->setSaveFile($arg);
			}
			
		    // Check for the show parameter
			if(strpos($param, "--show-save-files") !== FALSE) {
				$this->setShow(TRUE);
			}
			
		    // Check for the delete parameter
			if(strpos($param, "--delete-host") !== FALSE) {
				$this->setDelete(TRUE);
				$this->setSaveFile($arg);
			}
			
		    // Check for the domain parameter
			if(strpos($param, "-D") !== FALSE) {
				$domainSet = TRUE;
			}
		}
		
		// Check if the user has not set a domain name set and load or delete is not set to TRUE.
		if(!$domainSet && !$this->load && !$this->show && !$this->delete) {
			die("\nYou must include a domain name using the -D parameter\nTry 'avhs.php --help' for more information.\n");
		}
	}
	
	public function getArgc() {
  		return $this->argc;
	}
	
	private function setArgc($num) {
  		$this->argc = $num  ;
	}
	
	public function getArgv() {
  		return $this->argv;
	}
	
	private function setArgv($arg) {
  		array_push($this->argv, $arg);
	}
	
	private function unsetArgv() {
  		$this->argv = [];
	}
	
	public function getError() {
  		return $this->error;
	}
	
	private function setError($error) {
  		$this->error = "\033[91m$error\033[0m\n";
	}
	
	public function getVhostDir() {
  		return $this->vhostDir;
	}
	
	private function setVhostDir($dir) {
  		$this->vhostDir = $dir;
	}
	
	public function getVhostDirExists() {
  		return $this->vhostDirExists;
	}
	
	private function setVhostDirExists($bool) {
  		$this->vhostDirExists = $bool;
	}
	
	public function getProjectDir() {
  		return $this->projectDir;
	}
	
	private function setProjectDir($dir) {
  		$this->projectDir = $dir;
	}
	
	public function getFullPathProjectDir() {
  		return $this->fullPathProjectDir;
	}
	
	private function setFullPathProjectDir($dir) {
  		$this->fullPathProjectDir = $dir;
	}
	
	public function getProjectConFile() {
  		return $this->projectConFile;
	}
	
	private function setProjectConFile($file) {
  		$this->projectConFile = $file;
	}
	
	public function getDefaultConFile() {
  		return $this->defaultConFile;
	}
	
	private function setDefaultConFile($file) {
  		$this->defaultConFile = $file;
	}
	
	public function getApacheConFile() {
  		return $this->apacheConFile;
	}
	
	private function setApacheConFile($file) {
  		$this->apacheConFile = $file;
	}
	
	public function getHostsFile() {
  		return $this->hostsFile;
	}
	
	private function setHostsFile($file) {
  		$this->hostsFile = $file;
	}
	
	public function getDomainName() {
  		return $this->domainName;
	}
	
	private function setDomainName($domain) {
  		$this->domainName = $domain;
	}
	
	public function getVhostIp() {
  		return $this->vhostIp;
	}
	
	private function setVhostIp($ip) {
  		$this->vhostIp = $ip;
	}
	
	public function getBootstrapUrl() {
  		return $this->bootstrapUrl;
	}
	
	private function setBootstrapUrl($url) {
  		$this->bootstrapUrl = $url;
	}
	
	public function getSave() {
  		return $this->save;
	}
	
	private function setSave($bool) {
  		$this->save = $bool;
	}
	
	public function getLoad() {
  		return $this->load;
	}
	
	private function setLoad($bool) {
  		$this->load = $bool;
	}
	
	public function getShow() {
  		return $this->show;
	}
	
	private function setShow($bool) {
  		$this->show = $bool;
	}
	
	public function getDelete() {
  		return $this->delete;
	}
	
	private function setDelete($bool) {
  		$this->delete = $bool;
	}
	
	public function getSaveFile() {
  		return $this->saveFile;
	}
	
	private function setSaveFile($file) {
  		$this->saveFile = $file;
	}
	
    
	/**
	* Check and validate the parameters and arguments that have been passed in and set them. 
	*
	* @return FALSE if there was an error, TRUE otherwise.
	*/
	public function setParams() {
        displayMsg("Checking the input parameters", "93");
        
        for($i = 1; $i <= $this->argc - 1; $i += 2) {
            // Set the parameter variable.
            $param = $this->argv[$i];
            
            // Check that an argument element exist if not set the arguments element to an empty string.
            if(!isset($this->argv[$i+1])) {
                $this->argv[$i+1] = "";
            }
            
            // Set the argument variable.
            $arg = $this->argv[$i+1];
            
            // Check for all the valid parameters.
            if($param == "-P" || $param == "--project-directory") {
                if(!$this->checkArg($param, $arg, "invalid project directory")) {
                    return FALSE;
                }
                
                // Set the project directory.
                $this->setProjectDir($arg);
            }
            else if($param == "-V" || $param == "--vhosts-directory") {
                if(!$this->checkArg($param, $arg, "invalid virtual hosts directory")) {
                    return FALSE;
                }
                
                // Slice the virtual hosts directory off the path.
                $dir = dirname($arg);
                // Check if the folder containing the virtual hosts directory exists.
                if(!file_exists($dir)) {
                    $this->setError("Error '$dir' does not exist");
                    return FALSE;
                }
                
                // Check if the virtual hosts directory exists, if it doesn't set $vhostDirExists to FALSE.
                if(!file_exists($arg)) {
                    $this->setVhostDirExists(FALSE);
                }
                
                // Set the virtual hosts directory.
                $this->setVhostDir($arg);
            }
            else if($param == "-p" || $param == "--project-config-file") {
                if(!$this->checkArg($param, $arg, "invalid config file")) {
                    return FALSE;
                }
                
                // Check if the project config file exists.
                if(file_exists($arg)) {
                    $this->setError("Error '$arg' already exists");
                    return FALSE;
                }
                
                // Slice the file name off the path.
                $configDir = dirname($arg);
                // Check if the files config directory exists.
                if(!file_exists($configDir)) {
                    $this->setError("Error '$configDir' does not exist");
                    return FALSE;
                }

                // Set the project config file.
                $this->setProjectConFile($arg);
            }
            else if($param == "-d" || $param == "--default-config-file") {
                if(!$this->checkArg($param, $arg, "invalid default config file")) {
                    return FALSE;
                }
                
                // Checks if the default config file exists.
                if(!file_exists($arg)) {
                    $this->setError("Error '$arg' does not exist");
                    return FALSE;
                }
                
                // Set the default config file.
                $this->setDefaultConFile($arg);
            }
            else if($param == "-a" || $param == "--apache-config-file") {
                if(!$this->checkArg($param, $arg, "invalid apache config file")) {
                    return FALSE;
                }
                
                // Checks if the apache config file exists.
                if(!file_exists($arg)) {
                    $this->setError("Error '$arg' does not exist");
                    return FALSE;
                }
                
                // Set the apache config file.
                $this->setApacheConFile($arg);
            }
            else if($param == "-h" || $param == "--hosts-file") {
                if(!$this->checkArg($param, $arg, "invalid hosts file")) {
                    return FALSE;
                }
                
                // Checks if the apache config file exists.
                if(!file_exists($arg)) {
                    $this->setError("Error '$arg' does not exist");
                    return FALSE;
                }
                
                // Set the hosts file.
                $this->setHostsFile($arg);
            }
            else if($param == "-D" || $param == "--domain-name") {
                if(!$this->checkArg($param, $arg, "invalid domain name")) {
                    return FALSE;
                }
                
                // Check if the domain is valid.
                if(!filter_var("validate@$arg", FILTER_VALIDATE_EMAIL)) {
                    $this->setError("Error '$arg' is not a valid domain name");
                    return FALSE;
                }
                
                // Set the domain name.
                $this->setDomainName($arg);
            }
            else if($param == "-v" || $param == "--vhost-ip") {
                if(!$this->checkArg($param, $arg, "invalid virtual host IP")) {
                    return FALSE;
                }
                
                // Check if the IP address is valid.
                if(!filter_var($arg, FILTER_VALIDATE_IP)) {
                    $this->setError("Error '$arg' is not a valid IP address");
                    return FALSE;
                }
                
                // Set the virtual host IP.
                $this->setVhostIp($arg);
            }
            else if($param == "-B" || $param == "--install-bootstrap") {
                if(!$this->checkArg($param, $arg, "invalid Bootstrap URL")) {
                    return FALSE;
                }
                
                // Check if the URL is valid.
                if(!filter_var($arg, FILTER_VALIDATE_URL)) {
                    $this->setError("Error '$arg' is not a valid URL");
                    return FALSE;
                }
                
                // Set the Bootstrap URL.
                $this->setBootstrapUrl($arg);
            }
            else if($param == "--save-config") {
                $this->setSave(TRUE);
                $i--;
            }
            else {
                $this->setError("avhs.php: invalid option -- '$param'\nTry 'avhs.php --help' for more information.");
                return FALSE;
            }
        }
        
        // Check if the project directory was set. If not set it to the default.
        if(!isset($this->projectDir)) {
            $this->setProjectDir($this->domainName);
        }
        
        // Set the full path for the project directory.
        $this->setFullPathProjectDir($this->joinPath($this->vhostDir, $this->projectDir));
        
        // Check if the project directory already exists.
        if(file_exists($this->fullPathProjectDir)) {
            $this->setError("Error: '" . $this->fullPathProjectDir . "' already exists!");
            return FALSE;
        }
        
        // Check if the project config file has been set. If not set it to the default.
        if(!isset($this->projectConFile)) {
            $this->setProjectConFile("/etc/apache2/sites-available/" . $this->domainName . ".conf");
            
            // Check if the config file exists.
            if(file_exists($this->projectConFile)) {
                $this->setError("Error '" . $this->projectConFile . "' already exists");
                return FALSE;
            }
        }
        
        // Check if domain already in hosts file.
        $content = file_get_contents($this->hostsFile);
        $domain = "/\b" . $this->domainName . "\b/i";
        if(preg_match($domain, $content)) {
            $this->setError("Error '" . $this->domainName . "' already exists in the hosts file");
            return FALSE;
        }
        
        displayMsg("Input parameters all look ok", "32");
        return TRUE;
	}
    
	/**
	* Checks that the parameters argument is not empty and does't begin with a '-'.
	*
	* @param string $parameter is the parameter of the argument that is being checked.
	* @param string $arg is argument that is being checked.
	* @param string $error is an error messaged used when an invalid argument is given.
	* @return FALSE if there was an error, TRUE otherwise.
	*/
    private function checkArg($param, $arg, $error) {
        if(empty($arg)) {
            $this->setError("avhs.php: option '$param' requires an argument\nTry 'avhs.php --help' for more information.");
            return FALSE;
        }
        
        if($arg[0] == "-") {
            $this->setError("avhs.php: $error: '$arg'");
            return FALSE;
        }
        return TRUE;
    }
    
	/**
	* Joins two directory paths together.
	*
	* @param string $vDir is the path of virtual hosts directory.
	* @param string $pDir is the path of projects directory and any nested directories.
	* @return void
	*/
    private function joinPath($vDir, $pDir) {
        if($vDir[strlen($vDir)-1] != "/" && $pDir[0] != "/") {
            // Add a '/' to the end of the virtual directory path.
            $this->setVhostDir("$vDir/");
        }
        else if($vDir[strlen($vDir)-1] == "/" && $pDir[0] == "/") {
            // Remove the '/' form the end of the virtual directory path.
            $this->setVhostDir(substr($vDir, 0, -1));
        }
        
        return($this->vhostDir . $this->projectDir);
    }
    
    // Save the new hosts configurations.
    public function saveConfig($avhsDir) {
        $saveDir = "$avhsDir/saved/";
        $configFile = $saveDir . $this->domainName . ".conf";
        
        // Check if the saved directory exists and create it if not.
        if(!file_exists("$saveDir")) {
            if(!mkdir("$saveDir")) {
                $this->setError("Error creating the saved config directory");
                return FALSE;
            }
        
            // Change the config directory owner to the current user.
            if(!chown("$saveDir", $_SERVER['SUDO_USER'])) {
                $this->setError("Error setting the file owner for '$configFile'");
                return FALSE;
            }
            // Change the config directory group to the current user.
            if(!chgrp("$saveDir", $_SERVER['SUDO_USER'])) {
                $this->setError("Error setting the file group for '$configFile'");
                return FALSE;
            }
        }
        
        // Set the configuration information.
        $content = "-P=" . $this->projectDir .
        "\n-V=" . $this->vhostDir .
        "\n-p=" . $this->projectConFile .
        "\n-d=" . $this->defaultConFile .
        "\n-a=" . $this->apacheConFile .   
        "\n-h=" . $this->hostsFile .
        "\n-D=" . $this->domainName .
        "\n-v=" . $this->vhostIp;
        
        // Add the bootstrap argument if it has been set.
        if(isset($this->bootstrapUrl)) {
            $content .= "\n-B=" . $this->bootstrapUrl;
        }
        
        // Create the edited config file.
        if(file_put_contents($configFile, $content) === FALSE) {
            $this->setError("Error saving the configurations to file '$configFile'");
            return FALSE;
        }
        
        // Change the config file owner to the current user.
        if(!chown($configFile, $_SERVER['SUDO_USER'])) {
            $this->setError("Error setting the file owner for '$configFile'");
            return FALSE;
        }
        // Change the config file group to the current user.
        if(!chgrp($configFile, $_SERVER['SUDO_USER'])) {
            $this->setError("Error setting the file group for '$configFile'");
            return FALSE;
        }
        return TRUE;
    }
    
    // Load the hosts configurations for a saved file.
    public function loadConfig($avhsDir) {
        $savedConFile = "$avhsDir/saved/" . $this->saveFile . ".conf";
        
        // Check if the saved config file exists.
        if(!file_exists($savedConFile)) {
            $this->setError("Error '$savedConFile' does not exist");
            return FALSE;
        }
        
        // Get the saved config file contents.
	    if(!$lines = file($savedConFile, FILE_IGNORE_NEW_LINES)) {
            $this->setError("Error getting the contents of the saved config file '$savedConFile'");
            return FALSE;
	    }
	    
	    // Reset the arguments array.
        $this->unsetArgv();
        // Set a dummy for the first element.
        $this->setArgv("dummy");
	    
	    // Set Parameter properties for each line in the saved config file.
	    foreach($lines as $line) {
	        // Set the parameter and value on each line of the saved file.
	        $param = substr($line, 0, strpos($line, "="));
	        $arg = substr($line, strpos($line, "=")+1);
	        
	        // Add the parameter and argument to the argv array.
	        $this->setArgv($param);
	        $this->setArgv($arg);
	        
        }
        
        // Set the argument count.
        $num = count($this->argv);
        $this->setArgc($num);
        
        // Load the saved configurations.
        if(!$this->setParams()) {
            return FALSE;
        }
        
	    return TRUE;
    }
    
    // Show all the saved config file.
    public function showSavedConFiles($avhsDir) {
        $saveDir = "$avhsDir/saved/";
        $fileList = array_diff(scandir($saveDir), ["..", "."]);
        
        // List the file names without .conf
        foreach($fileList as $file) {
            echo substr($file, 0, strpos($file, ".conf"))."\n";
        }
    }
    
    // Delete the host
    public function deleteHost() {
        
    }
    
    // Display a message with the configuration the user has chosen.
    public function configMsg() {
        displayMsg("A new virtual host will be set up with these configurations:", "97");
        displayMsg("\tVirtual hosts directory: " . $this->getVhostDir(), "97");
        displayMsg("\tProject directory: " . $this->getProjectDir(), "97");
        displayMsg("\tFull project path: " . $this->getFullPathProjectDir(), "97");
        displayMsg("\tProject config file: " . $this->getProjectConFile(), "97");
        displayMsg("\tDefault config file: " . $this->getDefaultConFile(), "97");
        displayMsg("\tApache config file: " . $this->getApacheConFile(), "97");
        displayMsg("\tHosts file: " . $this->getHostsFile(), "97");
        displayMsg("\tDomain name: " . $this->getDomainName(), "97");
        displayMsg("\tVirtual host IP: " . $this->getVhostIp(), "97");
        displayMsg("\tBootstrap URL: " . $this->getBootstrapUrl(), "97");

        // Ask the user if they want to continue with the configurations.
        $ans = "";
        while($ans != "Y") {
            $ans = strtoupper(readline("Do you want to continue with these configurations Y or N?"));
            if($ans == "N") {
                die("\033[91mThe virtual host setup was aborted\033[0m\n");
            }
        }
    }

    // Display the --help message.
    private function helpMsg() {
        displayMsg("Usage: avhs -D DOMAIN [OPTIONS] ARGUMENT --save-config", "97");
        displayMsg("            [--load-config | --delete-config] FILE", "97");
        displayMsg("            [--show-save-files]", "97");
        displayMsg("Creates a new virtual host for Apache.\n", "97");
        displayMsg("The -D option and a valid DOMAIN name is required.", "97");
        displayMsg("If no other options are passed in the virtual host", "97");
        displayMsg("will be set up with the default options.\n", "97");
        
        displayMsg("  -P, --project-directory\tpath of the project driectory and any nested", "97");
        displayMsg("\t\t\t\tdirectories. eg example/public_html/", "97");
        displayMsg("\t\t\t\tdefault is the same as DOMAIN", "97");
        displayMsg("  -V, --vhosts-directory\tfull path of the virtual hosts directory.", "97");
        displayMsg("\t\t\t\teg /home/user/vhosts/.", "97");
        displayMsg("\t\t\t\tdefault is /var/www/", "97");
        displayMsg("  -p, --project-config-file\tfull path of the projects config file", "97");
        displayMsg("\t\t\t\tdefault is", "97");
        displayMsg("\t\t\t\t/etc/apache2/sites-available/DOMAIN.conf", "97");
        displayMsg("  -d, --default-config-file\tfull path of the default config file", "97");
        displayMsg("\t\t\t\tdefault is", "97");
        displayMsg("\t\t\t\t/etc/apache2/sites-available/000-default.conf", "97");
        displayMsg("  -a, --apache-config-file\tfull path of the apache config file", "97");
        displayMsg("\t\t\t\tdefault is /etc/apache2/apache2.conf", "97");
        displayMsg("  -h, --hosts-file\t\tfull path of the hosts file", "97");
        displayMsg("\t\t\t\tdefault is /etc/hosts", "97");
        displayMsg("  -D, --domain-name\t\tthe name of the DOMAIN", "97");
        displayMsg("  -v, --vhost-ip\t\tthe ip address of the virtual host", "97");
        displayMsg("\t\t\t\tdefault is 127.0.0.1", "97");
        displayMsg("  -B, --install-bootstrap\turl of a zip file containing compiled Bootstrap", "97");
        displayMsg("\t\t\t\tfiles.", "97");
        displayMsg("      --save-config\t\tsave the configurations to a file with the name", "97");
        displayMsg("\t\t\t\tDOMAIN.conf", "97");
        displayMsg("      --load-config\t\treload a site using the configurations stored in", "97");
        displayMsg("\t\t\t\tthe saved file", "97");
        displayMsg("      --show-save-files\t\tshow a lists of all the saved config files", "97");
    }
}

?>

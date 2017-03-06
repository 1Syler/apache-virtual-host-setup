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
	* @var int $argc is the the number of arguments.
	* @var array $argv is an array of the arguments.
	* @var string $error is an error message.
	* @var string $vhostDir is the name of the directory used to store the virtual hosts.
	*             This can be set by the user, the default is /var/www/.
	* @var string $createVhostDir is set to TRUE when the virtual host directory doesn't exist but it's parent does exist.
	*             The default is set to FALSE.
	* @var string $projectDir is the path of the project directory to create in $vhostDir, it can create a nested structure.
	*             This can be set by the user the default is $projectName.
	* @var string $projectConFile is the path of the projects .conf file.
	*             This can be set by the user, the default is /etc/apache2/sites-available/[$projectDir].conf.
	* @var string $defaultConFile is the path of the apache default vhost config file used to create the project config file.
    *             This can be set by the user, the default is /etc/apache2/sites-available/000-default.conf.
	* @var string $apacheConFile is the path the apache default config file used to create access for the $vhostDir.
	*             This can be set by the user, the default is /etc/apache2/apache2.conf.
	* @var string $hostsFile is the path of the systems hosts file.
	*             This can be set by the user, the default is /etc/hosts.
	* @var string $domainName is the domain name for the new project, it is also used to set other variable defaults.
	*             This must be set by the user, it will set $projectDir, $projectConFile if no arguments are passed in.
	* @var string $vhostIp is the IP address of the vhost used in the hosts file.
	*             This can be set by the user, the default is 127.0.0.1.
	*
	*/
	private $argc;
	private $argv = [];
	private $error;
	private $vhostDir = "/var/www/";
	private $createVhostDir = FALSE;
	private $projectDir;
	private $fullPathProjectDir;
	private $projectConFile;
	private $defaultConFile = "/etc/apache2/sites-available/000-default.conf";
    private $apacheConFile = "/etc/apache2/apache2.conf";
	private $hostsFile = "/etc/hosts";
	private $domainName;
	private $vhostIp = "127.0.0.1";

	/*
	*
	* Sets $argc and $argv.
	* Check if the help parameter was passed in and display the help message.
	* Check if the required -D parameter was passed in.
	*
	* @param int $argc is the the number of arguments passed in.
	* @param array $argv is an array of the arguments passed in.
	* @return void
	*
	*/
	public function __construct($argc, $argv) {
	    $this->argc = $argc;
	    $this->argv = $argv;

        // Check if the help command has been passed in and display the help message if it has.
        foreach($argv as $arg) {
            if (strpos($arg, "--help") !== FALSE) {
                helpMsg();
                exit;
            }
        }

        // Check if the required -D DOMAIN parameter was passed in and set error if it has not.
        $passed = FALSE;
        foreach($argv as $arg) {
            if (strpos($arg, "-D") !== FALSE) {
                $passed = TRUE;
                break;
            }
        }
        if(!$passed) {
            $this->setError("You must include a domain name using the -D parameter\nTry 'avhs.php --help' for more information.");
            die($this->getError());
        }
	}
	
	public function getArgc() {
  		return $this->argc;
	}
	
	public function getArgv() {
  		return $this->argv;
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
	
	public function getCreateVhostDir() {
  		return $this->createVhostDir;
	}
	
	private function setCreateVhostDir($bool) {
  		$this->createVhostDir = $bool;
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
            
            // Check that an argument exist if not set the arguments to an empty string.
            if(!isset($this->argv[$i+1])) {
                $this->argv[$i+1] = "";
            }
            
            // Set the argument variable.
            $arg = $this->argv[$i+1];
            
            // Check for all the valid parameters.
            if($param == "-p" || $param == "--project-directory") {
                if(!$this->checkArg($param, $arg, "invalid directory")) {
                    return FALSE;
                }
                
                // Set the project directory.
                $this->setProjectDir($arg);
            }
            else if($param == "-P" || $param == "--project-config-file") {
                if(!$this->checkArg($param, $arg, "invalid config file")) {
                    return FALSE;
                }
                
                // Slice the file name off the path.
                $configDir = dirname($arg);
                // Checks if the files config directory exists.
                if(!$this->checkExists($configDir)) {
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
                if(!$this->checkExists($arg)) {
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
                if(!$this->checkExists($arg)) {
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
                if(!$this->checkExists($arg)) {
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
                if(!$this->checkDomain($arg)) {
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
                if(!$this->checkIp($arg)) {
                    return FALSE;
                }
                
                // Set the virtual host IP.
                $this->setVhostIp($arg);
            }
            else if($param == "-V" || $param == "--vhosts-directory") {
                if(!$this->checkArg($param, $arg, "invalid virtual hosts directory")) {
                    return FALSE;
                }
                
                // Slice the virtual hosts directory off the path.
                $dir = dirname($arg);
                // Check if the folder containing the virtual hosts directory exists.
                if(!$this->checkExists($dir)) {
                    return FALSE;
                }
                
                // Check if the virtual hosts directory exists, if not, set $createVhostDir.
                if(!$this->checkExists($arg)) {
                    $this->setCreateVhostDir(TRUE);
                }
                
                // Set the virtual hosts directory.
                $this->setVhostDir($arg);
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
        $this->joinPath($this->vhostDir, $this->projectDir);
        
        // Check if the project directory already exists.
        if($this->checkExists($this->fullPathProjectDir)) {
            $this->setError("Error: '" . $this->fullPathProjectDir . "' already exists!");
            return FALSE;
        }
        
        // Check if the project config file has been set. If not set it to the default.
        if(!isset($this->projectConFile)) {
            $this->setProjectConFile("/etc/apache2/sites-available/" . $this->domainName . ".conf");
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
            $this->setError("avhs.php: $error: '$arg'\n");
            return FALSE;
        }
        return TRUE;
    }
    
	/**
	* Check if the given path exists.
	*
	* @param string $path is a file or directory path
	* @return FALSE if there was an error, TRUE otherwise.
	*/
    private function checkExists($path) {
        if(!file_exists($path)) {
            $this->setError("Error '$path' does not exist");
            return FALSE;
        }
        return TRUE;
    }
    
	/**
	* Check if the given domain name is valid.
	*
	* @param string $domain is the doamin name.
	* @return FALSE if there was an error, TRUE otherwise.
	*/
    private function checkDomain($domain) {
        if(!filter_var("validate@$domain", FILTER_VALIDATE_EMAIL)) {
            $this->setError("Error '$domain' is not a valid domain name");
            return FALSE;
        }
        return TRUE;
    }
    
	/**
	* Check if the given IP address is valid.
	*
	* @param string $ip is the virtual hosts IP address.
	* @return FALSE if there was an error, TRUE otherwise.
	*/
    private function checkIp($ip) {
        if(!filter_var($ip, FILTER_VALIDATE_IP)) {
            $this->setError("Error '$ip' is not a valid IP address");
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
        
        $this->setFullPathProjectDir($this->vhostDir . $this->projectDir);
    }
}

?>

<?php
/**
 * class.vhost.config.php
 *
 * Checks and validates input parameters.
 *
 */

class VhostConfig
{
    /*
    * Private variables
    *
    * @var int    $argc is the the number of arguments.
    * @var array  $argv is an array of the arguments.
    *
    * Protected variables
    *
    * @var string $scriptDir is full path to the calling script avhs.php.
    *
    * Public variables
    *
    * @var string $error is an error message.
    * @var string $vhostDir is the full path of the directory used to store the virtual hosts.
    *             This can be set by the user, the default is /var/www/.
    * @var bool   $vhostDirExists is set to FALSE when the virtual host directory doesn't exist.
    *             The default is set to TRUE.
    * @var string $projectDir is the name of the project directory and any nested directories to create in $vhostDir.
    *             This can be set by the user the default is $domainName.
    * @var string $projectConFile is the full path of the projects config file.
    *             This can be set by the user, the default is /etc/apache2/sites-available/$domainName.conf.
    * @var string $defaultConFile is the full path of the apache default vhost config file used to create the project config file.
    *             This can be set by the user, the default is /etc/apache2/sites-available/000-default.conf.
    * @var string $apacheConFile is the full path the apache default config file used to create access for $vhostDir.
    *             This can be set by the user, the default is /etc/apache2/apache2.conf.
    * @var string $hostsFile is the full path of the systems hosts file.
    *             This can be set by the user, the default is /etc/hosts.
    * @var string $domainName is the domain name for the new project, it is also used to set other variable defaults.
    *             This must be set by the user. It will set the defaults for $projectDir and $projectConFile if no arguments are passed in.
    * @var string $vhostIp is the IP address of the vhost used in the hosts file.
    *             This can be set by the user, the default is 127.0.0.1.
    * @var string $bootstrapUrl is the URL of the Bootstrap zip file to download.
    *             This can be set by the user.
    * @var bool   $save is set to TRUE when the user opts to save the config.
    *             The default is set to FALSE.
    * @var bool   $load is set to TRUE when the user opts to load the config.
    *             The default is set to FALSE.
    * @var bool   $delete is set to TRUE when the user opts to delete the config.
    *             The default is set to FALSE.
    * @var string $saveFile is the name of the saved vhost config file minus the .conf extension.
    */
    private $argc;
    private $argv = [];
    protected $scriptDir;
    public $error;
    public $vhostDir = "/var/www/";
    public $vhostDirExists = TRUE;
    public $projectDir;
    public $fullPathProjectDir;
    public $projectConFile;
    public $defaultConFile = "/etc/apache2/sites-available/000-default.conf";
    public $apacheConFile = "/etc/apache2/apache2.conf";
    public $hostsFile = "/etc/hosts";
    public $domainName;
    public $vhostIp = "127.0.0.1";
    public $bootstrapUrl;
    public $isSave = FALSE;
    public $isLoad = FALSE;
    public $isDelete = FALSE;
    public $saveFile;

    /*
    *
    * Sets $argc, $argv and $scriptDir.
    * Check if the --help parameter was passed.
    * Check if the --show-saved-hosts parameter was passed.
    * Check if the --load-host parameter was passed.
    * Check if the --delete-host parameter was passed.
    * Check if the required -D parameter was passed in.
    *
    * @param int $argc is the the number of arguments passed in.
    * @param array $argv is an array of the arguments passed in.
    * @param array $scriptDir is the directory of the calling script avhs.php.
    * @return void
    *
    */
    public function __construct($argc, $argv, $scriptDir) {
        $this->argc = $argc;
        $this->argv = $argv;
        $this->scriptDir = $scriptDir;

        // Check if specified parameters were passed in.
        $domainSet = FALSE;
        while($param = current($argv)) {
            $arg = next($argv);
            
            if(strpos($param, "--help") !== FALSE) {
                $this->helpMsg();
                exit;
            }
            if(strpos($param, "--show-saved-hosts") !== FALSE) {
                $this->showSavedConFiles();
                exit;
            }
            if(strpos($param, "--load-host") !== FALSE) {
                $this->setIsLoad(TRUE);
                $this->setSaveFile($arg);
            }
            if(strpos($param, "--delete-host") !== FALSE) {
                $this->setIsDelete(TRUE);
                $this->setSaveFile($arg);
            }
            if(strpos($param, "-D") !== FALSE) {
                $domainSet = TRUE;
            }
        }
        
        // Check if domain, show, load or delete were set and exit with a message if not.
        if(!$domainSet && !$this->isLoad && !$this->isDelete) {
            die("\nYou must include a domain name using the -D parameter\nTry 'avhs.php --help' for more information.\n");
        }
    }
    
    private function setArgc($num) {
        $this->argc = $num;
    }
    
    private function setArgv($arg) {
        array_push($this->argv, $arg);
    }
    
    private function unsetArgv() {
        $this->argv = [];
    }
    
    protected function setError($error) {
        $this->error = "\033[91m$error\033[0m\n";
    }
    
    public function displayMsg($message, $color) {
        echo "\033[" . $color . "m$message\033[0m\n";
    }
    
    private function setVhostDir($dir) {
        $this->vhostDir = $dir;
    }
    
    private function setVhostDirExists($bool) {
        $this->vhostDirExists = $bool;
    }
    
    private function setProjectDir($dir) {
        $this->projectDir = $dir;
    }
    
    private function setFullPathProjectDir($dir) {
        $this->fullPathProjectDir = $dir;
    }
    
    private function setProjectConFile($file) {
        $this->projectConFile = $file;
    }
    
    private function setDefaultConFile($file) {
        $this->defaultConFile = $file;
    }
    
    private function setApacheConFile($file) {
        $this->apacheConFile = $file;
    }
    
    private function setHostsFile($file) {
        $this->hostsFile = $file;
    }
    
    private function setDomainName($domain) {
        $this->domainName = $domain;
    }
    
    private function setVhostIp($ip) {
        $this->vhostIp = $ip;
    }
    
    private function setBootstrapUrl($url) {
        $this->bootstrapUrl = $url;
    }
    
    private function setIsSave($bool) {
        $this->isSave = $bool;
    }
    
    private function setIsLoad($bool) {
        $this->isLoad = $bool;
    }
    
    private function setIsDelete($bool) {
        $this->isDelete = $bool;
    }
    
    private function setSaveFile($file) {
        $this->saveFile = $file;
    }
    
    
    /**
    * Check and validate the parameters and arguments that have been passed in and set them. 
    *
    * @return FALSE if there was an error, TRUE otherwise.
    */
    public function setConfig() {
        $this->displayMsg("Checking the input parameters", "93");

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
                // Check if the argument is empty or begins with a '-'.
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
                
                // Check if the folder containing the virtual hosts directory exists.
                $dir = dirname($arg);
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
                if(!$this->checkArg($param, $arg, "invalid project config file")) {
                    return FALSE;
                }
                
                // Check if the project config file exists.
                if(file_exists($arg)) {
                    $this->setError("Error '$arg' already exists");
                    return FALSE;
                }
                
                // Check if the config files directory exists.
                $configDir = dirname($arg);
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
                
                // Checks if the hosts file exists.
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
                
                // Check if the virtual host IP address is valid.
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
                
                // Check if the Bootstrap URL is valid.
                if(!filter_var($arg, FILTER_VALIDATE_URL)) {
                    $this->setError("Error '$arg' is not a valid URL");
                    return FALSE;
                }
                
                // Set the Bootstrap URL.
                $this->setBootstrapUrl($arg);
            }
            else if($param == "--save-host") {
                $this->setIsSave(TRUE);
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
        
        // Check if the domain name is already in hosts file.
        $content = file_get_contents($this->hostsFile);
        $domain = "/\b" . $this->domainName . "\b/i";
        if(preg_match($domain, $content)) {
            $this->setError("Error '" . $this->domainName . "' already exists in the hosts file");
            return FALSE;
        }
        
        $this->displayMsg("Input parameters all look ok", "32");
        return TRUE;
    }
    
    /**
    * Checks that the parameters argument is not empty and does not begin with a '-'.
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
    * Joins the virtual hosts and project directory paths together.
    *
    * @param string $vDir is the path of virtual hosts directory.
    * @param string $pDir is the path of projects directory and any nested directories.
    * @return void
    */
    protected function joinPath($vDir, $pDir) {
        // Check if the virtual hosts directory ends with '/' and add it if not.
        if($vDir[strlen($vDir)-1] != "/") {
            $vDir .= "/";
        }
        
        // Check if the project directory begins with '/' and remove it if so.
        if($pDir[0] == "/") {
            $pDir = substr($pDir, 1, strlen($pDir)-1);
        }
        
        // Check if the project directory ends with '/' and remove it.
        if($pDir[strlen($pDir)-1] == "/") {
            $pDir = substr($pDir, 0, -1);
        }
        $fullPath = $vDir.$pDir;
        $this->setVhostDir($vDir);
        $this->setProjectDir($pDir);
        
        return($fullPath);
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
    * Save the new hosts configurations to a file.
    *
    * @return FALSE if there was an error, TRUE otherwise.
    */
    public function saveConfig() {
        $this->displayMsg("Saving the new hosts configurations", "93");
        $saveDir = $this->scriptDir."/saved/";
        $configFile = $saveDir . $this->domainName . ".conf";
        
        // Check if the saved directory exists and create it if not.
        if(!file_exists($saveDir)) {
            if(!mkdir($saveDir)) {
                $this->setError("Error creating the saved config directory");
                return FALSE;
            }

            // Take Ownership of the saved config directory.
            if(!$this->takeOwnership($saveDir, "directory")) {
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
        
        // Create the saved config file.
        if(file_put_contents($configFile, $content) === FALSE) {
            $this->setError("Error saving the configurations to file '$configFile'");
            return FALSE;
        }
        
        // Take Ownership of the config file.
        if(!$this->takeOwnership($configFile, "file")) {
            return FALSE;
        }
        
        $this->displayMsg("The hosts configurations were successfully saved", "32");
        return TRUE;
    }
    
    // Load the hosts configurations for a saved file.
    public function loadHost() {
        $this->displayMsg("Loading the hosts configurations", "93");
        $savedConFile = $this->scriptDir."/saved/" . $this->saveFile . ".conf";
        
        // Check if the saved config file exists
        if(!file_exists($savedConFile)) {
            $this->setError("Error the saved config file '$savedConFile' does not exist");
            return FALSE;
        }
        
        // Get the contents as an array of lines.
        if(!$lines = file($savedConFile, FILE_IGNORE_NEW_LINES)) {
            $this->setError("Error getting the contents of the saved config file '$savedConFile'");
            return FALSE;
        }
        
        // Reset the arguments array.
        $this->unsetArgv();
        // Set a dummy value for the first element.
        $this->setArgv("--load-host");
        
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
        if(!$this->setConfig()) {
            return FALSE;
        }
        
        $this->displayMsg("The hosts configurations were successfully loaded", "32");
        return TRUE;
    }
    
    /**
    * Show all the saved host files.
    *
    * @return None.
    */
    private function showSavedConFiles() {
        $saveDir = $this->scriptDir."/saved/";
        
        // Check if saved directory exists.
        $this->displayMsg("Saved configuration files:", "97");
        if(!file_exists($saveDir)) {
            $this->displayMsg("No saved config files", "97");
            exit;
        }
        
        $fileList = array_diff(scandir($saveDir), ["..", "."]);
        
        // List the file names without .conf
        foreach($fileList as $file) {
            $this->displayMsg("\t" . substr($file, 0, strpos($file, ".conf")), "97");
        }
    }
    
    /**
    * Display the --help message.
    *
    * @return None.
    */
    private function helpMsg() {
        $this->displayMsg("Usage: avhs.php -D DOMAIN [OPTIONS] ARGUMENT --save-host", "97");
        $this->displayMsg("                [--load-host | --delete-host] FILE", "97");
        $this->displayMsg("                [--show-saved-hosts]", "97");
        $this->displayMsg("Creates a new virtual host for Apache.\n", "97");
        $this->displayMsg("The -D option and a valid DOMAIN name is required when creating", "97");
        $this->displayMsg("a new host. If no other options are passed in the virtual host", "97");
        $this->displayMsg("will be set up with the default options.\n", "97");
        
        $this->displayMsg("  -P, --project-directory\tpath of the project driectory and any nested", "97");
        $this->displayMsg("\t\t\t\tdirectories. eg example.com/public_html", "97");
        $this->displayMsg("\t\t\t\tdefault is the same as DOMAIN", "97");
        $this->displayMsg("  -V, --vhosts-directory\tfull path of the virtual hosts directory.", "97");
        $this->displayMsg("\t\t\t\teg /home/user/vhosts/.", "97");
        $this->displayMsg("\t\t\t\tdefault is /var/www/", "97");
        $this->displayMsg("  -p, --project-config-file\tfull path of the projects config file", "97");
        $this->displayMsg("\t\t\t\tdefault is", "97");
        $this->displayMsg("\t\t\t\t/etc/apache2/sites-available/DOMAIN.conf", "97");
        $this->displayMsg("  -d, --default-config-file\tfull path of the default config file", "97");
        $this->displayMsg("\t\t\t\tdefault is", "97");
        $this->displayMsg("\t\t\t\t/etc/apache2/sites-available/000-default.conf", "97");
        $this->displayMsg("  -a, --apache-config-file\tfull path of the apache config file", "97");
        $this->displayMsg("\t\t\t\tdefault is /etc/apache2/apache2.conf", "97");
        $this->displayMsg("  -h, --hosts-file\t\tfull path of the hosts file", "97");
        $this->displayMsg("\t\t\t\tdefault is /etc/hosts", "97");
        $this->displayMsg("  -D, --domain-name\t\tthe name of the DOMAIN", "97");
        $this->displayMsg("  -v, --vhost-ip\t\tthe ip address of the virtual host", "97");
        $this->displayMsg("\t\t\t\tdefault is 127.0.0.1", "97");
        $this->displayMsg("  -B, --install-bootstrap\turl of a zip file containing compiled Bootstrap", "97");
        $this->displayMsg("\t\t\t\tfiles.", "97");
        $this->displayMsg("      --save-host\t\tsave the hosts configurations to a file with", "97");
        $this->displayMsg("\t\t\t\tthe name DOMAIN.conf", "97");
        $this->displayMsg("      --show-saved-hosts\tshow a lists of all the saved hosts files", "97");
        $this->displayMsg("      --load-host\t\treload a host using the saved config file", "97");
        $this->displayMsg("      --delete-host\t\tdelete a host using the saved config file", "97");
    }
    
    /**
    * Display a message with the configuration the user has chosen and ask for comfimation to proceed.
    *
    * @return None.
    */
    public function configMsg() {
        $this->displayMsg("A new virtual host will be set up with these configurations:", "97");
        $this->displayMsg("\tVirtual hosts directory: " . $this->vhostDir, "97");
        $this->displayMsg("\tProject directory: " . $this->projectDir, "97");
        $this->displayMsg("\tFull project path: " . $this->fullPathProjectDir, "97");
        $this->displayMsg("\tProject config file: " . $this->projectConFile, "97");
        $this->displayMsg("\tDefault config file: " . $this->defaultConFile, "97");
        $this->displayMsg("\tApache config file: " . $this->apacheConFile, "97");
        $this->displayMsg("\tHosts file: " . $this->hostsFile, "97");
        $this->displayMsg("\tDomain name: " . $this->domainName, "97");
        $this->displayMsg("\tVirtual host IP: " . $this->vhostIp, "97");
        $this->displayMsg("\tBootstrap URL: " . $this->bootstrapUrl, "97");

        // Ask the user if they want to continue with the configurations.
        $ans = "";
        while($ans != "Y") {
            $ans = strtoupper(readline("Do you want to continue with these configurations Y or N?"));
            if($ans == "N") {
                die("\033[91mThe virtual host setup was aborted\033[0m\n");
            }
        }
    }
}
?>

#!/usr/bin/php
<?php
// Include the required files.
require("includes/class.parameters.php");
require("includes/class.vhost.php");
require("includes/functions.php");

// Check if the script is running as root.
if (posix_getuid() != 0) {
    die("This script must be running as root\n");
}

// Create a new Parameters object.
$args = new Parameters($argc, $argv);

// Set all the parameter arguments.
if(!$args->setParams()) {
    die($args->getError());
}

// Display message showing the configurations to be used for the virtual host and ask for confirmation.
configMsg($args);

// Create a new Vhost object.
$vhost = new Vhost();

// Create the virtual hosts directory if it doesn't exist.
if(!$args->getVhostDirExists()) {
    // If the setup falis remove anything that has been done an exit.
    if(!$vhost->createVhostsDir($args->getVhostDir())) {
        $vhost->cleanup($args->getFullPathProjectDir(), $args->getVhostDir(), $args->getProjectConFile(), $args->getApacheConFile(), $args->getHostsFile());
        die($vhost->getError());
    }
}

// Create the project folder for the new virtual host.
if(!$vhost->createProjectDir($args->getFullPathProjectDir(), $args->getProjectDir())) {
    $vhost->cleanup($args->getFullPathProjectDir(), $args->getVhostDir(), $args->getProjectConFile(), $args->getApacheConFile(), $args->getHostsFile());
    die($vhost->getError());
}

// Create the virtual hosts config file.
if(!$vhost->createConFile($args->getProjectConFile(), $args->getDefaultConFile(), $args->getFullPathProjectDir(), $args->getDomainName())) {
    $vhost->cleanup($args->getFullPathProjectDir(), $args->getVhostDir(), $args->getProjectConFile(), $args->getApacheConFile(), $args->getHostsFile());
    die($vhost->getError());
}

// Allow access for the new virtual host in the apache config file.
if(!$vhost->allowVhostAccess($args->getApacheConFile(), $args->getVhostDir())) {
    $vhost->cleanup($args->getFullPathProjectDir(), $args->getVhostDir(), $args->getProjectConFile(), $args->getApacheConFile(), $args->getHostsFile());
    die($vhost->getError());
}

// Edit the hosts file to add the new virtual host.
if(!$vhost->editHostsFile($args->getHostsFile(), $args->getDomainName(), $args->getVhostIp())) {
    $vhost->cleanup($args->getFullPathProjectDir(), $args->getVhostDir(), $args->getProjectConFile(), $args->getApacheConFile(), $args->getHostsFile());
    die($vhost->getError());
}

// Download and install Bootstrap if the URL has been set.
if($args->getBootstrapUrl() !== NULL) {
    $vhost->getBootstrap($args->getBootstrapUrl(), $args->getFullPathProjectDir());
    
    // Copy the bootstrap template file to the new host.
    $avhsDir = dirname(__FILE__);
    $projectPath = $args->getFullPathProjectDir() . "/index.html";
    if(!copy("$avhsDir/templates/bootstrap.html", $projectPath)) {
        displayMsg("Error copying Bootstrap template file to the new host", "91");
    }
    else {
        $vhost->takeOwnership($projectPath, "file");
    }
}
// Copy standard template file to the new host.
else {
    $avhsDir = dirname(__FILE__);
    $projectPath = $args->getFullPathProjectDir() . "/index.html";
    if(!copy("$avhsDir/templates/index.html", $projectPath)) {
        displayMsg("Error copying template file to the new host", "91");
    }
    else {
        $vhost->takeOwnership($projectPath, "file");
    }
}

// Enable the new virtual host and restart apache.
displayMsg("Enabling the new virtual host and reloading the server", "93");
exec("a2ensite " . pathinfo($args->getProjectConFile(), PATHINFO_FILENAME));
exec("service apache2 restart");
displayMsg("\nThe new host is enabled to access the site go to http://" . $args->getDomainName(), "93");
exit;
?>

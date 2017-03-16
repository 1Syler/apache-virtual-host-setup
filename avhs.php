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

// Set the directory of avhs.php
$avhsDir = dirname(__FILE__);

// Create a new Parameters object.
$args = new Parameters($argc, $argv);

// Load the configuration form the saved file.
if($args->getLoad()) {
    if(!$args->loadConfig($avhsDir)) {
        die($args->getError());
    }
}
// Delete the host specified in the configuration file.
else if($args->getDelete()) {
    if(!$args->deleteHost($avhsDir)) {
        die($args->getError());
    }
}
// Set all the parameter arguments.
else {
    if(!$args->setParams()) {
        die($args->getError());
    }
}

// Display message showing the configurations to be used for the virtual host and ask for confirmation.
$args->configMsg($args);

// Save the configuration to a file.
if($args->getSave()) {
    if(!$args->saveConfig($avhsDir)) {
        die($args->getError());
    }
}

// Set the config variables
$projectDir = $args->getProjectDir();
$fullPathProjectDir = $args->getFullPathProjectDir();
$vhostDir = $args->getVhostDir();
$projectConFile = $args->getProjectConFile();
$defaultConFile = $args->getDefaultConFile();
$apacheConFile = $args->getApacheConFile();
$hostsFile = $args->getHostsFile();
$domainName = $args->getDomainName();
$vhostIp = $args->getVhostIp();
$bootstrapUrl = $args->getBootstrapUrl();

// Create a new Vhost object.
$vhost = new Vhost();

// Create the virtual hosts directory if it doesn't exist.
if(!$args->getVhostDirExists()) {
    // If the setup falis remove anything that has been done an exit.
    if(!$vhost->createVhostsDir($vhostDir)) {
        $vhost->cleanup($fullPathProjectDir, $vhostDir, $projectConFile, $apacheConFile, $hostsFile);
        die($vhost->getError());
    }
}

// Create the project folder for the new virtual host.
if(!$vhost->createProjectDir($fullPathProjectDir, $projectDir)) {
    $vhost->cleanup($fullPathProjectDir, $vhostDir, $projectConFile, $apacheConFile, $hostsFile);
    die($vhost->getError());
}

// Create the virtual hosts config file.
if(!$vhost->createConFile($projectConFile, $defaultConFile, $fullPathProjectDir, $domainName)) {
    $vhost->cleanup($fullPathProjectDir, $vhostDir, $projectConFile, $apacheConFile, $hostsFile);
    die($vhost->getError());
}

// Allow access for the new virtual host in the apache config file.
if(!$vhost->allowVhostAccess($apacheConFile, $vhostDir)) {
    $vhost->cleanup($fullPathProjectDir, $vhostDir, $projectConFile, $apacheConFile, $hostsFile);
    die($vhost->getError());
}

// Edit the hosts file to add the new virtual host.
if(!$vhost->editHostsFile($hostsFile, $domainName, $vhostIp)) {
    $vhost->cleanup($fullPathProjectDir, $vhostDir, $projectConFile, $apacheConFile, $hostsFile);
    die($vhost->getError());
}

// Download and install Bootstrap if the URL has been set.
if($bootstrapUrl !== NULL) {
    $vhost->getBootstrap($bootstrapUrl, $fullPathProjectDir);
    
    // Copy the bootstrap template file to the new host.
    $projectPath = $fullPathProjectDir . "/index.html";
    if(!copy("$avhsDir/templates/bootstrap.html", $projectPath)) {
        displayMsg("Error copying Bootstrap template file to the new host", "91");
    }
    else {
        $vhost->takeOwnership($projectPath, "file");
    }
}
// Copy standard template file to the new host.
else {
    $projectPath = $fullPathProjectDir . "/index.html";
    if(!copy("$avhsDir/templates/index.html", $projectPath)) {
        displayMsg("Error copying template file to the new host", "91");
    }
    else {
        $vhost->takeOwnership($projectPath, "file");
    }
}

// Enable the new virtual host and restart apache.
displayMsg("Enabling the new virtual host and reloading the server", "93");
exec("a2ensite " . pathinfo($projectConFile, PATHINFO_FILENAME));
exec("service apache2 restart");
displayMsg("\nThe new host is enabled to access the site go to http://" . $domainName, "93");
exit;
?>

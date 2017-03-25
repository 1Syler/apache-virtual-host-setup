#!/usr/bin/php
<?php
// Include the required files.
require("includes/class.vhost.config.php");
require("includes/class.vhost.php");

// Check if the script is running as root.
if (posix_getuid() != 0) {
    die("This script must be running as root\n");
}

// Set the directory of avhs.php script.
$avhsDir = dirname(__FILE__);

// Create a new Vhost object.
$vhost = new Vhost($argc, $argv, $avhsDir);

// Load the configuration from the saved file.
if($vhost->isLoad) {
    if(!$vhost->loadHost()) {
        die($vhost->error);
    }
}
// Delete the host specified in the configuration file and exit.
else if($vhost->isDelete) {
    if(!$vhost->deleteHost()) {
        die($vhost->error);
    }
    exit;
}
// Set all the parameter arguments.
else {
    if(!$vhost->setConfig()) {
        die($vhost->error);
    }
}

// Display message showing the configurations to be used for the virtual host and ask for confirmation.
$vhost->configMsg();

// Save the hosts configurations to a file.
if($vhost->isSave) {
    if(!$vhost->saveConfig()) {
        die($vhost->error);
    }
}

// Create the virtual hosts directory if it doesn't exist.
if(!$vhost->vhostDirExists) {
    // If the setup falis remove anything that has been done an exit.
    if(!$vhost->createVhostsDir()) {
        $vhost->cleanup();
        die($vhost->error);
    }
}

// Create the project folder for the new virtual host.
if(!$vhost->createProjectDir()) {
    $vhost->cleanup();
    die($vhost->error);
}

// Create the virtual hosts config file.
if(!$vhost->createConFile()) {
    $vhost->cleanup();
    die($vhost->error);
}

// Allow access for the new virtual host in the apache config file.
if(!$vhost->allowVhostAccess()) {
    $vhost->cleanup();
    die($vhost->error);
}

// Edit the hosts file to add the new virtual host.
if(!$vhost->editHostsFile()) {
    $vhost->cleanup();
    die($vhost->error);
}

// Set the template file.
$template = "$avhsDir/templates/index.html";

// Download and install Bootstrap if the URL has been set.
if($vhost->bootstrapUrl !== NULL) {
    if(!$vhost->getBootstrap()) {
        echo $vhost->error;
    }
    else {
        $template = "$avhsDir/templates/bootstrap.html";
    }
}

// Copy the template file to the new virtual host.
$projectPath = $vhost->fullPathProjectDir . "/index.html";
if(!copy($template, $projectPath)) {
    $vhost->displayMsg("Error copying template file to the new host", "91");
}
else {
    $vhost->takeOwnership($projectPath, "file");
}

// Enable the new virtual host and restart apache.
$vhost->displayMsg("Enabling the new virtual host and reloading the server", "93");
exec("a2ensite " . pathinfo($vhost->projectConFile, PATHINFO_FILENAME));
exec("service apache2 restart");
$vhost->displayMsg("\nThe new host is enabled to access the site go to http://" . $vhost->domainName, "93");
exit;
?>

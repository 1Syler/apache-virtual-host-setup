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

// Display message showing the configurations to be used for the virtual host.
configMsg($args);

// Ask the user if they want to continue with the configurations.
$ans = "";
while($ans != "Y") {
    $ans = strtoupper(readline("Do you want to continue with these configurations Y or N?"));
    if($ans == "N") {
        die("\033[91mThe virtual host setup was aborted\033[0m\n");
    }
}

// Create a new Vhost object.
$vhost = new Vhost();

// Create the virtual hosts directory if it doesn't exist.
if($args->getCreateVhostDir()) {
    if(!$vhost->createVhostsDir($args->getVhostDir())) {
        die($vhost->getError());
    }
}

// Create the project folder for the new virtual host.
if(!$vhost->createProjectDir($args->getFullPathProjectDir(), $args->getProjectDir())) {
    die($vhost->getError());
}

// Create the virtual hosts config file.
if(!$vhost->createConFile($args->getProjectConFile(), $args->getDefaultConFile(), $args->getFullPathProjectDir(), $args->getDomainName())) {
    die($vhost->getError());
}

// Allow access for the new virtual host in the apache config file.
if(!$vhost->allowVhostAccess($args->getApacheConFile(), $args->getFullPathProjectDir())) {
    die($vhost->getError());
}

// Edit the hosts file to add the new virtual host.
if(!$vhost->editHostsFile($args->getHostsFile(), $args->getDomainName(), $args->getVhostIp())) {
    die($vhost->getError());
}

// Enable the new virtual host and restart apache.
displayMsg("Enabling the new virtual host and reloading the server", "93");
echo exec("a2ensite " . pathinfo($args->getProjectConFile(), PATHINFO_FILENAME));
echo exec("service apache2 restart");
displayMsg("\nThe new host is enabled to access the site go to http://" . $args->getDomainName(), "93");

exit;
?>

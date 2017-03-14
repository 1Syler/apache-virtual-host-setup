<?php

// Display a message with the sepcified colour.
function displayMsg($message, $color) {
    echo "\033[" . $color . "m$message\033[0m\n";
}

// Display a message with the configuration the user has chosen.
function configMsg($args) {
    displayMsg("A new virtual host will be set up with these configurations:", "97");
    displayMsg("\tVirtual hosts directory: " . $args->getVhostDir(), "97");
    displayMsg("\tProject directory: " . $args->getProjectDir(), "97");
    displayMsg("\tFull project path: " . $args->getFullPathProjectDir(), "97");
    displayMsg("\tProject config file: " . $args->getProjectConFile(), "97");
    displayMsg("\tDefault config file: " . $args->getDefaultConFile(), "97");
    displayMsg("\tApache config file: " . $args->getApacheConFile(), "97");
    displayMsg("\tHosts file: " . $args->getHostsFile(), "97");
    displayMsg("\tDomain name: " . $args->getDomainName(), "97");
    displayMsg("\tVirtual host IP: " . $args->getVhostIp(), "97");
    displayMsg("\tBootstrap URL: " . $args->getBootstrapUrl(), "97");

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
function helpMsg() {
    displayMsg("Usage: avhs -D DOMAIN [OPTIONS] COMMAND", "97");
    displayMsg("Creates a new virtual host for Apache.\n", "97");
    displayMsg("The -D option and a valid DOMAIN name is required.", "97");
    displayMsg("If no other options are passed in the virtual host", "97");
    displayMsg("will be set up with the default options.\n", "97");
    
    displayMsg("  -p, --project-directory\tname of the projects driectory.", "97");
    displayMsg("\t\t\t\tdefault is the same as -D", "97");
    displayMsg("  -P, --project-config-file\tfull path of the projects config file", "97");
    displayMsg("\t\t\t\tdefault is", "97");
    displayMsg("\t\t\t\t/etc/apache2/sites-available/[-D].conf", "97");
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
    displayMsg("  -V, --vhosts-directory\tfull path of the virtual hosts directory", "97");
    displayMsg("\t\t\t\tthat contains the project directories.", "97");
    displayMsg("\t\t\t\tdefault is /var/www/", "97");
    displayMsg("  -D, --bootstrap-url\t\tURL of a Bootstrap zip file to download", "97");
    displayMsg("\t\t\t\tand install.", "97");
}

?>

<?php

// Display a message with the sepcified colour.
function displayMsg($message, $color) {
    echo "\033[" . $color . "m$message\033[0m\n";
}

?>

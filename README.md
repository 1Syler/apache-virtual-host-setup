# Apache Virtual Host Setup

A PHP script for creating virtual hosts on Apache server. It has been tested on Ubuntu with Apache 2.4 and that is what the default configurations are set for. There are lots of parameters to change the default configurations.

## Install

Download and unzip.

<pre><code>wget -O $HOME/avhs.zip https://github.com/1Syler/apache-virtual-host-setup/archive/master.zip && unzip $HOME/avhs.zip && rm $HOME/avhs.zip</code></pre>

Create a symlink to the script.

<pre><code>sudo ln -s -T $HOME/apache-virtual-host-setup-master/avhs.php /usr/local/bin/avhs.php</code></pre>

## Run the script


The script only requires one parameter(-D) to create a virtual host. If only -D is specified, it will use the default configurations for the rest of the setup.

Create a virtual host

<pre><code>sudo avhs.php -D example.com</code></pre>

See a message showing it's usage instructions

<pre><code>sudo avhs.php --help</code></pre>

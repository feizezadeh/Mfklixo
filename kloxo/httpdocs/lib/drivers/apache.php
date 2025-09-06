<?php

include_once "lib/webserverdriver.php";

class ApacheDriver extends WebServerDriver
{
    public function install()
    {
        lxshell_return("sh", "/script/changedriver", "web", "apache");
    }

    public function uninstall()
    {
        lxshell_return("sh", "/script/remove-web", "apache");
    }

    public function configure()
    {
        lxshell_return("sh", "/script/fixweb");
    }

    public function configure_php()
    {
        lxshell_return("sh", "/script/fixphp");
    }
}

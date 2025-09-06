<?php

include_once "lib/webserverdriver.php";

class LighttpdDriver extends WebServerDriver
{
    public function install()
    {
        lxshell_return("sh", "/script/changedriver", "web", "lighttpd");
    }

    public function uninstall()
    {
        lxshell_return("sh", "/script/remove-web", "lighttpd");
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

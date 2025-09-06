<?php

abstract class WebServerDriver
{
    abstract public function install();
    abstract public function uninstall();
    abstract public function configure();
    abstract public function configure_php();
}

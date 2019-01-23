<?php

namespace  Bundles\FrameworkBundle\Config;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

class Config {

    private $data = array();

    protected $configFiles=array();

    private $container;

    public function __construct(Container $container) {

        $this->container=$container;

    }

    public function get($key) {
        if($this->container->hasParameter($key)){
            return $this->container->getParameter($key);
        }
        return false;
    }

    public function set($key, $value) {
        $this->container->setParameter($key,$value);
        $this->data[$key] = $value;
    }

    public function has($key) {
        return $this->container->hasParameter($key);
    }

}

<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'routing.route_loader' shared service.

return $this->services['routing.route_loader'] = new \Bundles\FrameworkBundle\Routing\RouteLoader(${($_ = isset($this->services['request']) ? $this->services['request'] : $this->load('getRequestService.php')) && false ?: '_'}, ${($_ = isset($this->services['config']) ? $this->services['config'] : $this->services['config'] = new \Bundles\FrameworkBundle\Config\Config($this)) && false ?: '_'});
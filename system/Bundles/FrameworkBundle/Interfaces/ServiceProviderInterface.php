<?php
/**
 * Created by PhpStorm.
 * User: uid20214
 * Date: 11.03.2017
 * Time: 11:31
 */

namespace Bundles\FrameworkBundle\Interfaces;

use Symfony\Component\DependencyInjection\ContainerBuilder;

interface ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A container instance
     */
    public function register(ContainerBuilder $container);
}
<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 15.07.17
 * Time: 14:54
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\SecurityBundle\Security\Factory;

use Symfony\Component\DependencyInjection\Container;

interface SecurityFactoryInterface{
    public function create(Container $container, $id, $config, $userProvider, $defaultEntryPoint);

    public function getPosition();

    public function getKey();
}
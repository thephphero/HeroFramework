<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 17/02/19
 * Time: 20:50
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace  Bundles\SecurityBundle\Middleware;


use Bundles\FrameworkBundle\Interfaces\BeforeMiddlewareInterface;
use Bundles\FrameworkBundle\Request\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserPermissionMiddleware implements BeforeMiddlewareInterface{

    public function handle(Request $request, ContainerInterface $container)
    {
        echo'hi there';
    }
}
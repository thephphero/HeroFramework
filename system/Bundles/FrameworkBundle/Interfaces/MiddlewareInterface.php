<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 17/02/19
 * Time: 20:46
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle\Interfaces;

use Bundles\FrameworkBundle\Request\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

interface MiddlewareInterface{

    function handle(Request $request , ContainerInterface $container);
}
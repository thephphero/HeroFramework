<?php
/*
* The Hero Framework.
*
* (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
* Date: 29/05/18
* Time: 18:59
* Created by thePHPHero
* 
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Core;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as symfonyKernel;

class Kernel extends symfonyKernel{

    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        // TODO: Implement registerContainerConfiguration() method.
    }

    public function registerBundles()
    {
        // TODO: Implement registerBundles() method.
    }
}
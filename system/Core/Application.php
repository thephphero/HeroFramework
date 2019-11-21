<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 29/05/18
 * Time: 20:30
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Core;

use Bundles\FrameworkBundle\Interfaces\ServiceProviderInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Debug\Debug;
use Bundles\FrameworkBundle\Request\Request;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use ReflectionClass;

class Application extends Kernel {
    protected $timezone;

    public function __construct($basePath = NULL, $env='production',  $debug=true) {

        $debug = ($debug=='false')?false:true;
        $this->rootDir = realpath($basePath);
        if($debug){
            Debug::enable();
            ErrorHandler::register(new ErrorHandler());
            ExceptionHandler::register(true);
        }

        parent::__construct($env,$debug);

        $this->setTimeZone();
    }

    public function getCacheDir()
    {
        return $this->rootDir.'/storage/cache/';
    }

    public function getLogDir()
    {
        return $this->rootDir.'/storage/logs';
    }

    public function getSessionDir(){
        return $this->rootDir.'/storage/sessions';
    }

    protected function setTimeZone() {


        $this->timezone='Europe/Berlin';

        date_default_timezone_set($this->timezone);
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        foreach ($this->bundles as $bundle){
            if(file_exists($bundle->getPath().DIRECTORY_SEPARATOR.'Resources/config/config.yml')){
                //$loader->load($bundle->getPath().DIRECTORY_SEPARATOR.'Resources/config/config.yml');
            }
        }

    }

    public function registerBundles()
    {
        $contents = require $this->rootDir.'/config/app.php';
        foreach ($contents['bundles'] as $class) {
            yield new $class();

        }

    }

    public function run(){

        $request = Request::createFromGlobals();

        $response= $this->handle($request)->send();

        return $this->terminate($request,$response);
    }

}

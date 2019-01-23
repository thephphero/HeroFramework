<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 03/01/19
 * Time: 16:42
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle\Template;

use Twig\Environment;

class Template {

    protected $loader;

    protected $environment;

    private $cacheDir;

    private $auto_reload;

    public function __construct(Environment $environment)
    {

        if(!$this->loader){
            $this->loader=$environment->getLoader();
        }

        $this->environment = $environment;

       /* if(!file_exists($environment->getLoader())){
            if(!mkdir($cache_dir,0755)){
                throw new FileNotWritableException('Template cache dir is not writable!');
            }
        }
       */

    }


    function addTemplateDir($template_dir){
        $this->loader->addPath($template_dir);
    }

    public function getTemplateDirs($namespace='__main__'){
        return $this->getPaths($namespace);
    }

    public function render($name, array $context = array()){
        return $this->environment->render($name,$context);
    }

    function setTemplateDir($template_dir){
        $this->loader->prependPath($template_dir);

    }

    function getLoader(){
        return $this->loader;
    }

}
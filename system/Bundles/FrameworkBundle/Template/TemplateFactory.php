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

use ProxyManager\Exception\FileNotWritableException;
use Twig_Loader_Filesystem;
use Twig_Environment;
class TemplateFactory{

    protected $loader;

    protected $environment;

    private $cacheDir;

    private $auto_reload;

    public function __construct($cache_dir, $debug=false)
    {
        if(!file_exists($cache_dir)){
            /*if(!mkdir($cache_dir,0755)){
                throw new FileNotWritableException('Template cache dir is not writable!');
            }*/
        }
        $this->cacheDir = $cache_dir;

        $this->auto_reload = $debug;
    }

    public function create(){
        if(!$this->environment){
            $this->loader = new Twig_Loader_Filesystem($this->templateDirs);

            $this->environment = new Twig_Environment($this->loader,[
                'cache'=>$this->cacheDir,
                'auto_reload'=>$this->auto_reload
            ]);
            $this->environment->enableAutoReload();
        }
        return $this->environment;
    }

    function addTemplateDir($template_dir){
        $this->loader->addPath($template_dir);
    }

    function getTemplateDirs(){
        return $this->templateDirs;
    }

    function setTemplateDir($template_dir){
        $this->loader->addPath($template_dir);

    }
}
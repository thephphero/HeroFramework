<?php

/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 01.01.2019
 * Time: 14:43
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle\Locale;

use Bundles\FrameworkBundle\Request\Request;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Translator;

class Language{

    protected $defaultLocale;

    private $iso_locale;

    private $paths = array();

    protected $translator;

    private $fallbacks=[];

    public function __construct(Request $request, array $fallbacks , $paths=[])
    {
        $this->fallbacks = $fallbacks;

        if($locale=$request->getLocale()){
            $this->iso_locale = $locale;
        }

        $this->paths = $paths;

    }

    public function load($rootPath) {

        $translator = new Translator($this->iso_locale, new MessageSelector());

        //Add loader
        $translator->addLoader('php',new PhpFileLoader());

        //Set fallback locale
        $translator->setFallbackLocales($this->fallbacks);

        //Default language file
        $defaultFile = $rootPath . DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.strtolower($this->iso_locale.'.php');
        if(empty($this->paths) && file_exists($defaultFile)){

            array_push($this->paths,$defaultFile);
        }

        if(is_array($this->paths)){

            foreach ($this->paths as $dir){

                $file = $dir.DIRECTORY_SEPARATOR.strtolower($this->iso_locale.'.php');

                if(file_exists($file)){

                    $translator->addResource('php', $file, $this->iso_locale);
                }
            }
        }

        $this->translator=$translator;
    }

    public function translate($id, array $parameters = [], $domain = null, $locale = null) {

        if(!$this->translator){
            return;
        }

        return $this->translator->trans($id, $parameters , $domain , $locale );
    }


}
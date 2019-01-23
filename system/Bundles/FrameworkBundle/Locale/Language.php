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

    private $data = array();

    private $iso_locale;

    private $directory = array();

    protected $translator;

    public function __construct(Request $request, $defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;

        if($locale=$request->locale()){
            $this->iso_locale = $locale;
        }

    }

    protected function load($rootPath) {

        $translator = new Translator($this->iso_locale, new MessageSelector());

        $translator->addLoader('php',new PhpFileLoader());

        //Default language file
        $defaultFile = $rootPath . DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.strtolower($this->iso_locale.'.php');
        array_push($this->directory,$defaultFile);

        if(is_array($this->directory)){

            foreach ($this->directory as $dir){

                $file = $dir.DIRECTORY_SEPARATOR.strtolower($this->iso_locale.'.php');

                if(file_exists($file)){

                    $translator->addResource('php', $file, $this->iso_locale);
                }
            }
        }

        $this->translator=$translator;
    }

    public function get($key) {
        return (isset($this->data[$key]) ? $this->data[$key] : $key);
    }


    public function all() {
        return $this->data;
    }
}
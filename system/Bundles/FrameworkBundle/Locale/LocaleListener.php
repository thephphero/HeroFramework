<?php
/**
 * Created by PhpStorm.
 * User: uid20214
 * Date: 02.01.2019
 * Time: 15:31
 */

namespace Bundles\FrameworkBundle\Locale;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Bundles\FrameworkBundle\Request\Request;

class LocaleListener{


    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $browserLocale = $this->getBrowserLanguage($request);

        $locale = $request->attributes->get('_locale',$browserLocale);

        $request->setLocale($locale);
    }

    private function getBrowserLanguage(Request $request){

        $locale_code = $request->headers->get('Accept-Language');
        $browser_language_code = substr($locale_code, 0, 5);
        return $browser_language_code;

    }
}
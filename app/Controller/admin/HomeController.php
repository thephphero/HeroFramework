<?php
/**
 * Created by PhpStorm.
 * User: uid20214
 * Date: 07.09.2018
 * Time: 17:25
 */

namespace app\Controller\admin;

use Bundles\FrameworkBundle\Controller\Controller;
use Bundles\FrameworkBundle\Request\Request;
use Symfony\Component\HttpFoundation\Response;


class HomeController extends Controller{

    public function index(Request $request){

        $translation = $this->container->get('locale.translator')->translate('admin.greetings');

        return $this->get('template')->render('admin/templates/dashboard.html',['name'=>'Celso','greetings'=>$translation]);
      //  return new Response("Hi");
    }
}
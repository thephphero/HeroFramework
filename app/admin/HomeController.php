<?php
/**
 * Created by PhpStorm.
 * User: uid20214
 * Date: 07.09.2018
 * Time: 17:25
 */

namespace App\admin;

use Bundles\FrameworkBundle\Controller\Controller;
use Bundles\FrameworkBundle\Request\Request;
use Symfony\Component\HttpFoundation\Response;


class HomeController extends Controller{

    public function index(Request $request){
        return $this->get('template')->render('admin/templates/dashboard.html',['name'=>'Celso']);
      //  return new Response("Hi");
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: uid20214
 * Date: 07.09.2018
 * Time: 17:25
 */

namespace App;

use Bundles\FrameworkBundle\Controller\Controller;
use Bundles\FrameworkBundle\Request\Request;
use Symfony\Component\HttpFoundation\Response;


class HomeController extends Controller{
    public function index(Request $request){
        echo "Hi";
        return new Response("Hi");
    }
}
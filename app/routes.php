<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 25/12/16
 * Time: 13:43
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */




$router->group(['middleware'=>['admin'],'prefix' => 'admin', 'namespace' => 'admin'],function($router){

    $router->get('home', 'HomeController::index');
});



$router->group(['middleware'=>'web'],function($router){
    $router->get('home', 'HomeController::index');

});







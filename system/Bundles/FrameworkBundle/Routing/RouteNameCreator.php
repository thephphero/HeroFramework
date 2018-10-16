<?php


/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 26/02/18
 * Time: 14:27
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle\Routing;

class RouteNameCreator{

    public static function createRouteName($methods=[],$uri){

        $array=explode('/',trim($uri,'/'));

        if(is_array($methods)){
            sort($methods);
            $array=array_merge($array,$methods);
        }
        else{
            $array[]=$methods;
        }
        return strtolower(implode('-',$array));

    }
}
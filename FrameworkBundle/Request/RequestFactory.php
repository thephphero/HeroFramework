<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 17/09/2017
 * Time: 17:54
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle\Request;

use Bundles\FrameworkBundle\Request\Request;

class RequestFactory{


    public function create(){

        $request=Request::createFromGlobals();

        return $request;

    }

}

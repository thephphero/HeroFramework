<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 03/07/2017
 * Time: 15:19
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\SecurityBundle\Handlers;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class AccessDeniedHandler implements AccessDeniedHandlerInterface{

    public function handle(Request $request, AccessDeniedException $accessDeniedException){

        $url='/login';
        return RedirectResponse::create($url);
    }
}
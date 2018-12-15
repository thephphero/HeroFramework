<?php

/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 24/09/17
 * Time: 05:58
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\FrameworkBundle\Security\Repositories;

use Symfony\Component\Security\Core\User\UserInterface;

class AnonymousUser implements UserInterface{

    protected $roles=['IS_AUTHENTICATED_ANONYMOUSLY'];

    public function id(){
        return false;
    }

    public function getUsername()
    {
        return 'anon.';
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getPassword()
    {
        // TODO: Implement getPassword() method.
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }
}
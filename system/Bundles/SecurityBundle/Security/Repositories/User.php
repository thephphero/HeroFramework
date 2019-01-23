<?php

/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 01/04/17
 * Time: 22:29
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\SecurityBundle\Security\Repositories;

use Bundles\SecurityBundle\Security\UserProviders\UserProvider;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

class User implements UserInterface, EquatableInterface
{
    private $username;

    private $password;

    private $salt;

    private $roles;

    private $lastname;

    private $firstname;

    private $language_id;

    private $id;



    public function __construct($username, $password, $salt, array $roles)
    {
        $this->username = $username;

        $this->password = $password;

        $this->salt = $salt;

        $this->roles = $roles;


    }

    public function setLanguageId($languageId){
        $this->language_id=$languageId;
    }

    public function setLastname($lastname){
        $this->lastname=$lastname;
    }

    public function setFirstname($firstname){
        $this->firstname=$firstname;
    }

    public function setUserId($id){
        $this->id=$id;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getUsername()
    {
        return  $this->username;
    }

    public function eraseCredentials()
    {
    }

    public function getId(){
        return $this->id;
    }

    public function getLanguageId() {
        return $this->language_id;
    }

    public function firstname(){
        return $this->firstname;
    }

    public function lastname(){
        return $this->lastname;
    }

    public function isAdmin(){
        return in_array('ROLE_ADMIN',$this->roles);
    }

    public function isSuperAdmin(){

        return in_array('ROLE_SUPER_ADMIN',$this->roles);
    }

    public function verifyUser($userPassword){

        return password_verify($userPassword,$this->password);

    }

    public function isEqualTo(UserInterface $user)
    {

        if (!$user instanceof User) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }


        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;

    }

}



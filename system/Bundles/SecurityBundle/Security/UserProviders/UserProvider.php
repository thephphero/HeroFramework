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

namespace Bundles\SecurityBundle\Security\UserProviders;

use Bundles\SecurityBundle\Security\Repositories\User;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use PDO;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UserProvider implements UserProviderInterface{
    private $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;

    }

    public function loadUserByUsername($username)
    {


        $statement = $this->db->prepare("SELECT * FROM users WHERE :userLogin IN (user_login) AND user_active=:user_active");

        $statement->bindParam(':userLogin', $username, PDO::PARAM_STR);
        $statement->bindValue(':user_active', 1, PDO::PARAM_INT);
        $statement->execute();

        if ($row = $statement->fetch()) {
            $roles = explode(',', $row['user_roles']);

            $user = new User($row['user_login'], $row['user_password'],$salt='',$roles);

            $user->setFirstname($row['user_firstname']);
            $user->setLastname($row['user_lastname']);
            $user->setLanguageId($row['language_id']);
            $user->setUserId($row['user_id']);

            return $user;
        }


        throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class ===  'Bundles\\SecurityBundle\\Security\\Repositories\\User';
    }

}
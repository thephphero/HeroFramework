<?php

/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 27/11/16
 * Time: 22:31
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\SecurityBundle\Security\UserProviders;


use Bundles\SecurityBundle\Security\Repositories\TokenUser;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;


class TokenUserProvider implements UserProviderInterface{


    public function __construct()
    {


    }

    public function loadUserByUsername($clientId)
    {

        $statement = $this->db->prepare("SELECT * FROM oauth_keys WHERE client_id=:client_id");

        $statement->bindParam(':client_id', $clientId, PDO::PARAM_STR);
        $statement->execute();

        if (!$user = $statement->fetch()) {

            throw new UsernameNotFoundException(sprintf('Secret "%s" does not exist.', $clientId));
        }

        $tokenUser= new TokenUser($user['client_id'], $user['client_secret'], $salt='',explode(',', $user['roles']));

        $tokenUser->setId($user['client_id']);

        return $tokenUser;

    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof TokenUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === '\\Bundles\\SecurityBundle\\Security\\Repositories\\TokenUser';
    }
}
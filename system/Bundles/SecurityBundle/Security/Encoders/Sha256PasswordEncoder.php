<?php
namespace Library\Security\Encoders;

/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 29/09/17
 * Time: 00:44
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\SecurityBundle\Security\Encoders;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class Sha256PasswordEncoder implements PasswordEncoderInterface{

    public function encodePassword($raw, $salt)
    {
        return password_hash(
            base64_encode(
                hash('sha256', $raw, true)
            ),
            PASSWORD_DEFAULT
        );

    }

    public function isPasswordValid($encoded, $raw, $salt)
    {
        return password_verify( base64_encode(
            hash('sha256', $raw, true)
        ),$encoded);
    }
}
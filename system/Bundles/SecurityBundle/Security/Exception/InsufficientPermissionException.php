<?php

/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 03/12/17
 * Time: 16:50
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\SecurityBundle\Security\Exception;

class InsufficientPermissionsException extends \RuntimeException {

    public function __construct($message = 'Access Denied.', \Exception $previous = null)
    {
        parent::__construct($message, 403, $previous);
    }
}
<?php

/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 12/11/2017
 * Time: 20:30
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Bundles\FrameworkBundle\Log;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Log extends Logger {

    public function __construct($filename) {

        parent::__construct($filename);

        $this->pushHandler(new StreamHandler($filename),Logger::DEBUG);

    }

    public function write($message,$level='info') {

        $this->log($level,$message);
    }

}

<?php
/**
 * Created by PhpStorm.
 * User: celsoluiz81
 * Date: 05/01/18
 * Time: 23:56
 */

use Core\Application;

return new Application(__DIR__.'/../',getenv('ENVIRONMENT'),getenv('DEBUG'));
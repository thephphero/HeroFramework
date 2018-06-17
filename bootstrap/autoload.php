<?php
/**
 * Created by PhpStorm.
 * User: celsoluiz81
 * Date: 05/01/18
 * Time: 23:46
 */

require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
try {
    $dotenv = new Dotenv();
    $dotenv->load(__DIR__.'/../.env');
} catch (\Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException $e) {
    echo'Could not load .env file';
}
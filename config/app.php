<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 29/05/18
 * Time: 20:30
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


return [

    /*
   |--------------------------------------------------------------------------
   | Autoloaded Bundles
   |--------------------------------------------------------------------------
   |
   | The bundles listed here will be automatically loaded on the
   | request to your application. Feel free to add your own bundles to
   | this array to grant expanded functionality to the application.
   |
   */
    'bundles'=>[
        Bundles\FrameworkBundle\FrameworkBundle::class,
        Bundles\SecurityBundle\SecurityBundle::class,
        Bundles\TemplateBundle\TemplateBundle::class,

    ],
    /*
   |--------------------------------------------------------------------------
   | Security Middleware
   |--------------------------------------------------------------------------
   |
   | The bundles listed here will be automatically loaded on the
   | request to your application. Feel free to add your own middleware classes
   | to this array to grant or deny permissions.
   |
   */
    'middleware'=>[
        'admin' => \Bundles\SecurityBundle\Middleware\UserPermissionMiddleware::class
    ]
];
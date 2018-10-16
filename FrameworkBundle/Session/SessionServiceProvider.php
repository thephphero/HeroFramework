<?php

/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 25/12/16
 * Time: 13:43
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\FrameworkBundle\Session;


use Bundles\FrameworkBundle\Interfaces\ServiceProviderInterface;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\EventListener\SaveSessionListener;

class SessionServiceProvider implements ServiceProviderInterface {

    protected $options=[
        'cookie_lifetime'=>2592000,//1 month
        'gc_probability'=>1,
        'gc_divisor'=>1000,
        'gc_maxlifetime'=>2592000
    ];

    public function register(ContainerBuilder $container){

        //Session Handler (PDO)
        $pdoSessionHandlerDefinition=new Definition(PdoSessionHandler::class,[
            new Reference('db')
        ]);
        $container->setDefinition('session.pdo_session_handler',$pdoSessionHandlerDefinition);

        //Session Handler (File)
        $fileSessionHandlerDefinition=new Definition(NativeFileSessionHandler::class,[
            '%session_dir%'
        ]);
        $container->setDefinition('session.file_session_handler',$fileSessionHandlerDefinition);

        //Storage Handler
        $nativeSessionStorageDefinition=new Definition(NativeSessionStorage::class,[
            $this->options,
            new Reference('session.session_handler')
        ]);
        $container->setDefinition('session.session_storage',$nativeSessionStorageDefinition);

        //Session
        $sessionDefinition = new Definition(Session::class,[
            new Reference('session.session_storage')
        ]);
        $container->setDefinition('session',$sessionDefinition);

        //Session Listener
        $sessionListenerDefinition = new Definition(SessionListener::class,[
            new Reference('service_container')
        ]);
        $sessionListenerDefinition->addTag('kernel.event_subscriber');
        $container->setDefinition('session.session_listener',$sessionListenerDefinition);

        //Save Session Listener
        $saveSessionListenerDefinition=new Definition(SaveSessionListener::class);
        $saveSessionListenerDefinition->addTag('kernel.event_subscriber');

        $container->setDefinition('session.save_session_listener',$saveSessionListenerDefinition);

    }

}
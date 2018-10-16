<?php

namespace Library\Exception;

use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Debug\Exception\FlattenException;
use Bundles\FrameworkBundle\Request\Request;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExceptionSubscriber implements EventSubscriberInterface
{

    private $logger;

    private $controller;

    private $environment;

    public function __construct($controller,$environment,LoggerInterface $log)
    {

        $this->controller = $controller;
        $this->logger=$log;
        $this->environment=$environment;

    }

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return array(
            KernelEvents::EXCEPTION => array(
                array('processException', -15),
                array('logException', -11),
                array('notifyException', -10),
            )
        );
    }

    /**
     * processException
     * =================
     * Extracts the exception from the kernel event and processes it, creating a duplicate request to send to the kernel
     * causing the user to be redirected to the Error controller.
     * @param GetResponseForExceptionEvent $event
     * @throws \Exception
     */
    public function processException(GetResponseForExceptionEvent $event)
    {
        //Get the Exception from the event sent by the kernel
        $exception = $event->getException();
        //Duplicate the current request, redirecting to the Error page
        $request = $this->duplicateRequest($exception, $event->getRequest());
        //Try to get the response from the kernel using the duplicate request
        try {
            $response = $event->getKernel()->handle($request, HttpKernelInterface::SUB_REQUEST, false);
        }
        catch (\Exception $e) {

            //There was an exception when handling the original exception
            $this->logException($event, sprintf('Exception thrown when handling an exception (%s: %s at %s line %s)', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()));
            //Cycle through the previous exceptions and throw if its of the same kind
            $wrapper = $e;
            while ($prev = $wrapper->getPrevious()) {
                if ($exception === $wrapper = $prev) {
                    throw $e;
                }
            }
            // Add Exception to the wrapper exception if it's of a different kind
            $prev = new \ReflectionProperty('Exception', 'previous');
            $prev->setAccessible(true);
            $prev->setValue($wrapper, $exception);

            throw $e;
        }
        //Set the response to redirect to the error page
        $event->setResponse($response);
    }

    /**
     * logException
     * ===============
     * Logs the exception to the log file. If the status code is 500 or greater,
     * it will be logged as critical, otherwise as error. Its possible to change the
     * implementation to log only certain levels or add to the implementation to set
     * message as INFO or Debug. Take a look at /Library/Log/Log.php and its parent
     * class.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function logException(GetResponseForExceptionEvent $event, $message='')
    {
        $exception = $event->getException();

        if(!$message){
            $message= sprintf('Uncaught PHP Exception %s: "%s" at %s line %s', get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine());

        }

        if (null !== $this->logger) {
            if (!$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500) {
                $this->logger->critical($message, array('exception' => $exception));
            } else {
                $this->logger->error($message, array('exception' => $exception));
            }
        }
    }

    /**
     * notifyException
     * =================
     * Implement something here to notify the admin about this exception
     * @param GetResponseForExceptionEvent $event
     */
    public function notifyException(GetResponseForExceptionEvent $event)
    {

        //Only send email in production environment
        if($this->environment =='production'){
            $exception = $event->getException();
            /*TODO: Implement mail sending*/
        }
    }

    /**
     * duplicateRequest
     * ===================
     * Creates a duplicate of the request that originally triggered the exception an redirects to the error
     * page. The controller is set in $attributes['_controller'] and is provided in the constructor above.
     * To change this setting, simply go to Library/Exception/ExceptionServiceProvider.php and change the
     * first parameter of the array of parameters used to instantiate this class.
     * @param \Exception $exception
     * @param Request $request
     * @return Request|static
     */
    protected function duplicateRequest(\Exception $exception, Request $request)
    {
        $attributes = array(
            '_controller' => $this->controller,
            'exception' => FlattenException::create($exception),
            'logger' => $this->logger instanceof DebugLoggerInterface ? $this->logger : null,
            'format' => $request->getRequestFormat(),
        );
        $request = $request->duplicate(null, null, $attributes);
        $request->setMethod('GET');

        return $request;
    }
}
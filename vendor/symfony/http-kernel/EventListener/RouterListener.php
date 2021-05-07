<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ECSPrefix20210507\Symfony\Component\HttpKernel\EventListener;

use ECSPrefix20210507\Psr\Log\LoggerInterface;
use ECSPrefix20210507\Symfony\Component\EventDispatcher\EventSubscriberInterface;
use ECSPrefix20210507\Symfony\Component\HttpFoundation\Request;
use ECSPrefix20210507\Symfony\Component\HttpFoundation\RequestStack;
use ECSPrefix20210507\Symfony\Component\HttpFoundation\Response;
use ECSPrefix20210507\Symfony\Component\HttpKernel\Event\ExceptionEvent;
use ECSPrefix20210507\Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use ECSPrefix20210507\Symfony\Component\HttpKernel\Event\RequestEvent;
use ECSPrefix20210507\Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use ECSPrefix20210507\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use ECSPrefix20210507\Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use ECSPrefix20210507\Symfony\Component\HttpKernel\Kernel;
use ECSPrefix20210507\Symfony\Component\HttpKernel\KernelEvents;
use ECSPrefix20210507\Symfony\Component\Routing\Exception\MethodNotAllowedException;
use ECSPrefix20210507\Symfony\Component\Routing\Exception\NoConfigurationException;
use ECSPrefix20210507\Symfony\Component\Routing\Exception\ResourceNotFoundException;
use ECSPrefix20210507\Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use ECSPrefix20210507\Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use ECSPrefix20210507\Symfony\Component\Routing\RequestContext;
use ECSPrefix20210507\Symfony\Component\Routing\RequestContextAwareInterface;
/**
 * Initializes the context from the request and sets request attributes based on a matching route.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 *
 * @final
 */
class RouterListener implements \ECSPrefix20210507\Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    private $matcher;
    private $context;
    private $logger;
    private $requestStack;
    private $projectDir;
    private $debug;
    /**
     * @param UrlMatcherInterface|RequestMatcherInterface $matcher    The Url or Request matcher
     * @param \Symfony\Component\Routing\RequestContext $context The RequestContext (can be null when $matcher implements RequestContextAwareInterface)
     * @param string                                      $projectDir
     *
     * @throws \InvalidArgumentException
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Psr\Log\LoggerInterface $logger
     * @param bool $debug
     */
    public function __construct($matcher, $requestStack, $context = null, $logger = null, $projectDir = null, $debug = \true)
    {
        if (!$matcher instanceof \ECSPrefix20210507\Symfony\Component\Routing\Matcher\UrlMatcherInterface && !$matcher instanceof \ECSPrefix20210507\Symfony\Component\Routing\Matcher\RequestMatcherInterface) {
            throw new \InvalidArgumentException('Matcher must either implement UrlMatcherInterface or RequestMatcherInterface.');
        }
        if (null === $context && !$matcher instanceof \ECSPrefix20210507\Symfony\Component\Routing\RequestContextAwareInterface) {
            throw new \InvalidArgumentException('You must either pass a RequestContext or the matcher must implement RequestContextAwareInterface.');
        }
        $this->matcher = $matcher;
        $this->context = $context ?: $matcher->getContext();
        $this->requestStack = $requestStack;
        $this->logger = $logger;
        $this->projectDir = $projectDir;
        $this->debug = $debug;
    }
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    private function setCurrentRequest($request = null)
    {
        if (null !== $request) {
            try {
                $this->context->fromRequest($request);
            } catch (\UnexpectedValueException $e) {
                throw new \ECSPrefix20210507\Symfony\Component\HttpKernel\Exception\BadRequestHttpException($e->getMessage(), $e, $e->getCode());
            }
        }
    }
    /**
     * After a sub-request is done, we need to reset the routing context to the parent request so that the URL generator
     * operates on the correct context again.
     * @param \Symfony\Component\HttpKernel\Event\FinishRequestEvent $event
     */
    public function onKernelFinishRequest($event)
    {
        $this->setCurrentRequest($this->requestStack->getParentRequest());
    }
    /**
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
     */
    public function onKernelRequest($event)
    {
        $request = $event->getRequest();
        $this->setCurrentRequest($request);
        if ($request->attributes->has('_controller')) {
            // routing is already done
            return;
        }
        // add attributes based on the request (routing)
        try {
            // matching a request is more powerful than matching a URL path + context, so try that first
            if ($this->matcher instanceof \ECSPrefix20210507\Symfony\Component\Routing\Matcher\RequestMatcherInterface) {
                $parameters = $this->matcher->matchRequest($request);
            } else {
                $parameters = $this->matcher->match($request->getPathInfo());
            }
            if (null !== $this->logger) {
                $this->logger->info('Matched route "{route}".', ['route' => isset($parameters['_route']) ? $parameters['_route'] : 'n/a', 'route_parameters' => $parameters, 'request_uri' => $request->getUri(), 'method' => $request->getMethod()]);
            }
            $request->attributes->add($parameters);
            unset($parameters['_route'], $parameters['_controller']);
            $request->attributes->set('_route_params', $parameters);
        } catch (\ECSPrefix20210507\Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
            $message = \sprintf('No route found for "%s %s"', $request->getMethod(), $request->getPathInfo());
            if ($referer = $request->headers->get('referer')) {
                $message .= \sprintf(' (from "%s")', $referer);
            }
            throw new \ECSPrefix20210507\Symfony\Component\HttpKernel\Exception\NotFoundHttpException($message, $e);
        } catch (\ECSPrefix20210507\Symfony\Component\Routing\Exception\MethodNotAllowedException $e) {
            $message = \sprintf('No route found for "%s %s": Method Not Allowed (Allow: %s)', $request->getMethod(), $request->getPathInfo(), \implode(', ', $e->getAllowedMethods()));
            throw new \ECSPrefix20210507\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException($e->getAllowedMethods(), $message, $e);
        }
    }
    /**
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     */
    public function onKernelException($event)
    {
        if (!$this->debug || !($e = $event->getThrowable()) instanceof \ECSPrefix20210507\Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return;
        }
        if ($e->getPrevious() instanceof \ECSPrefix20210507\Symfony\Component\Routing\Exception\NoConfigurationException) {
            $event->setResponse($this->createWelcomeResponse());
        }
    }
    /**
     * @return mixed[]
     */
    public static function getSubscribedEvents()
    {
        return [\ECSPrefix20210507\Symfony\Component\HttpKernel\KernelEvents::REQUEST => [['onKernelRequest', 32]], \ECSPrefix20210507\Symfony\Component\HttpKernel\KernelEvents::FINISH_REQUEST => [['onKernelFinishRequest', 0]], \ECSPrefix20210507\Symfony\Component\HttpKernel\KernelEvents::EXCEPTION => ['onKernelException', -64]];
    }
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function createWelcomeResponse()
    {
        $version = \ECSPrefix20210507\Symfony\Component\HttpKernel\Kernel::VERSION;
        $projectDir = \realpath($this->projectDir) . \DIRECTORY_SEPARATOR;
        $docVersion = \substr(\ECSPrefix20210507\Symfony\Component\HttpKernel\Kernel::VERSION, 0, 3);
        \ob_start();
        include \dirname(__DIR__) . '/Resources/welcome.html.php';
        return new \ECSPrefix20210507\Symfony\Component\HttpFoundation\Response(\ob_get_clean(), \ECSPrefix20210507\Symfony\Component\HttpFoundation\Response::HTTP_NOT_FOUND);
    }
}
<?php

namespace CS\InstallerBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("request.check")
 */
class RequestListener
{
	/**
	 * @DI\Inject("router")
	 */
	public $router;

	/**
	 * @DI\Inject("database_connection")
	 */
	public $db;
	
	/**
     * @DI\Observe("kernel.request", priority = 10)
     */	
	public function onKernelRequest(GetResponseEvent $event)
	{		
		//$route = $event->getRequest()->get('_route');
		$route = $event->getRequest()->getRequestUri();

		if(strpos($route, 'installer') === false)
		{
			try {
				$this->db->connect();
			} catch(\Exception $e)
			{
				$response = new RedirectResponse($this->router->generate('_installer'));
			
				$event->setResponse($response);
			}
		}
	}
}

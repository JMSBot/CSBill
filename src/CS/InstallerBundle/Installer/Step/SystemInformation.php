<?php

namespace CS\InstallerBundle\Installer\Step;

use Symfony\Component\Process\Process;

use CS\InstallerBundle\Installer\Step;
use CS\UserBundle\Entity\User;

class SystemInformation extends Step
{
    /**
     * The view to render for this installation step
     *
     * @var string $view;
     */
    public $view = 'CSInstallerBundle:Install:system_information.html.twig';

    /**
     * The title to display when this installation step is active
     *
     * @var string $title
     */
    public $title = 'System Information';
    
    /**
     * Array containing all the parameters for the system and user information
     * 
     * @var array $params
     */
    public $params = array(	'email_address' => '',
							'password'		=> '');

    /**
     * Validate user and company info
     *
     * @param  array   $request
     * @return boolean
     */
    public function validate($request = array())
    {
		if(empty($request['email_address']))
		{
			$this->addError('Please enter an email address');
		}

		if(empty($request['password']))
		{
			$this->addError('Please enter a password');
		}
		
		$this->params = $request;

        return count($this->getErrors()) === 0;
    }

    /**
     * Save system and user configuration values
     * 
     * @param array $request
     */
    public function process($request = array())
    {
		$user = new User;
		
		$encoder = $this->get('security.encoder_factory')->getEncoder($user);
		
		$password = $encoder->encodePassword($request['password'], $user->getSalt());
		
		$user->setUsername('admin')
			 ->setEmail($request['email_address'])
			 ->setPassword($password);
		
		$em = $this->get('doctrine.orm.entity_manager');
		
		$role = $em->getRepository('CSUserBundle:Role')->findOneByName('super_admin');
		
		$user->addRole($role);
		
		$em->persist($user);
		$em->flush();
	}

    /**
     * @return void
     */
    public function start()
    {
    }
}

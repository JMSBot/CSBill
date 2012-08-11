<?php

namespace CS\InstallerBundle\Installer\Step;

use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Exception\ParseException;

use CS\InstallerBundle\Installer\Step;

class DatabaseConfig extends Step
{
    /**
     * The view to render for this installation step
     *
     * @param string $view;
     */
    public $view = 'CSInstallerBundle:Install:database_config.html.twig';

    /**
     * The title to display when this installation step is active
     *
     * @var string $title
     */
    public $title = 'Database Configuration';
    
    /**
     * Array containing all the PDO drivers that is installed
     * 
     * @var array $drivers
     */
    public $drivers;

    /**
     * Array containing all the parameters for the database config
     * 
     * @var array $params
     */
    public $params = array(	'database_driver' 	=> '',
							'database_host' 	=> '',
							'database_user' 	=> '',
							'database_password' => '',
							'database_port' 	=> 3306,
							'database_name' 	=> '');
    
    /**
     * Validates that the databse exists, and we are able to connect to it
     *
     * @param  array   $request
     * @return boolean
     */
    public function validate($request = array())
    {
		if(empty($request['database_driver']))
		{
			$this->addError('Please Choose a Database Driver');
		}

		if(empty($request['database_host']))
		{
			$this->addError('Please enter the hostname of the database');
		}

		if(empty($request['database_user']))
		{
			$this->addError('Please enter the user name of the database');
		}

		/*if(empty($request['database_password']))
		{
			$this->addError('Please enter the password of the database');
		}*/

		if(empty($request['database_name']))
		{
			$this->addError('Please enter the name of the database. Note: The database should already exist');
		}
		
		if(count($this->getErrors()) === 0)
		{
			$connectionFactory = $this->container->get('doctrine.dbal.connection_factory');
			$connection = $connectionFactory->createConnection(array(
				'driver' => $request['database_driver'],
				'user' => $request['database_user'],
				'password' => $request['database_password'],
				'host' => $request['database_host'],
				'dbname' => $request['database_name'],
			));
			
			try {
				$connection->connect();
			} catch(\PDOException $e)
			{
				$this->addError($e->getMessage());
			}
		}
		
		$this->params = $request;

        return count($this->getErrors()) === 0;
    }

    /**
     * Writes the database configuration to the parameters.yml file, and runs all the database migrations and fixtures
     * 
     * @param array $request
     */
    public function process($request = array()){
		
		$this->writeConfigFile($request);
		
		$this->executeMigrations();
		$this->executeFixtures();
	}

    /**
     * Checks the system to make sure it meets the minimum requirements
     *
     * @return void
     */
    public function start()
    {
		$this->drivers = \PDO::getAvailableDrivers();
    }
    
    /**
     * Writes all the configuration values to the paramaters file
     * 
     * @param array $params
     * @throws ParseException
     * @return void;
     */
    public function writeConfigFile($params = array())
    {
		$config = $this->get('kernel')->getRootDir().'/config/parameters.yml'; 

		$yaml = new Parser();
		
		try {
			$value = $yaml->parse(file_get_contents($config));
		} catch (ParseException $e) {
			throw new \RuntimeException("Unable to parse the YAML string: %s. Your installation might be corrupt.", $e->getMessage());
			exit;
		}
		
		foreach($params as $key => $param)
		{
			// sets the database details
			$value['parameters'][$key] = $param;
		}
		
		// sets a unique value for the secret token
		// We do this when writing the database configuration, as this is the only time (for now) that we modify the parameters.yml file
		// We still need to add an extra step so we can write smtp settings
		$value['secret'] = md5(uniqid(php_uname('n'), true));

		$dumper = new Dumper();

		$yaml = $dumper->dump($value);

		file_put_contents($config, $yaml);
	}
	
	/**
	 * Executes all doctrine migrations to create database structure
	 * 
	 * @return void
	 */
	public function executeMigrations()
	{
		$this->_runProcess('php ../app/console doctrine:migrations:migrate --no-interaction');
	}
	
	/**
	 * Load all fictures
	 * 
	 * @return void
	 */
	public function executeFixtures()
	{
		$this->_runProcess('php ../app/console doctrine:fixtures:load');
	}
	
	/**
	 * Runs a specific command with the Process Component
	 * 
	 * @param string $command The command that needs to be run
	 * @return string The output of the processed command
	 */
	private function _runProcess($command = '')
	{
		$process = new Process($command);
		$process->setTimeout(3600);
		$process->run();
		if (!$process->isSuccessful()) {			
			throw new \RuntimeException($process->getErrorOutput());
		}
		
		return $process->getOutput();
	}
}

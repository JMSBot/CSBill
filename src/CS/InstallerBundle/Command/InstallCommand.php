<?php
namespace CS\InstallerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    protected $invalid_options = array('help', 'quiet', 'verbose', 'version', 'ansi', 'no-ansi', 'no-interaction', 'shell', 'process-isolation', 'env', 'no-debug');

    protected function configure()
    {
        $this
            ->setName('app:install')
            ->setDescription('Install the application')
            ->addOption('accept', null, InputOption::VALUE_NONE, 'Do you accept the terms and conditions? (y/n) ')
            ->addOption('database_user', null, InputOption::VALUE_REQUIRED, 'What is your database username? ')
            ->addOption('database_host', null, InputOption::VALUE_REQUIRED, 'What is your database host? [localhost] ', 'localhost')
            ->addOption('database_name', null, InputOption::VALUE_REQUIRED, 'What is the name of the database you want to use? [csbill]', 'csbill')
            ->addOption('database_password', null, InputOption::VALUE_REQUIRED, 'What is your database password? ', '')
            ->addOption('database_port', null, InputOption::VALUE_REQUIRED, 'What is the port your database runs on? [3306]', 3306)
            ->addOption('email_address', null, InputOption::VALUE_REQUIRED, 'What is the email address of the administrator? ')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Please enter a password for the administrator ')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $container = $this->getApplication()->getKernel()->getContainer();

        try {

            if($container->get('database_connection')->connect())
            {
                try {
                    $repository = $container->get('doctrine.orm.entity_manager')->getRepository('CSUserBundle:User');

                    if(count($repository->findAll()) > 0)
                    {
                        $output->writeln('<error>ERROR: The application is already installed.</error>');
                        return;
                    }
                } catch(\Exception $e){}
            }
        } catch(\PDOException $e)
        {
            // if we get an exception here, the application isn't installed yet
        }


        $dialog = $this->getHelperSet()->get('dialog');

        $arguments = $this->getDefinition()->getOptions();

        $options = array();

        foreach($arguments as $argument)
        {
            $name = $argument->getName();

            if(in_array($name, $this->invalid_options))
            {
                continue;
            }

            if(!$argument->acceptValue())
            {
                if($input->hasParameterOption('--'.$name))
                {
                    $options[$name] = 'y';
                } else {
                    do {
                        $value = $dialog->ask($output, '<question>'.$argument->getDescription().'</question>', $input->getParameterOption('--'.$name));

                        if($value === 'n')
                        {
                            return;
                        } else if($value !== 'y')
                        {
                            $output->writeln("<comment>Please only enter 'y' or 'n'</comment>");
                        }
                    } while($value !== 'y');

                    $options[$name] = $value;
                }
            } else {

                if(!$input->getParameterOption('--'.$name))
                {

                    if($input->hasParameterOption('--'.$name.'='))
                    {
                       $value = '';
                    } else {

                        $value = $argument->getDefault();

                        do {
                            $value = $dialog->ask($output, '<question>'.$argument->getDescription().'</question>', $value);
                        } while($value === null);
                    }

                } else {
                    $value = $input->getOption($name);
                }

                $options[$name] = $value;
            }
        }

        // only current supported driver is mysql
        $options['database_driver'] = 'pdo_mysql';

        $installer = $container->get('installer');

        do {

            $step = $installer->getStep();

            $response = $installer->validateStep($options);

            $output->writeln(sprintf('Installation: %s', $step->title));

        } while($response !== false && stripos($response->getTargetUrl(), 'success') === false);

        if(!$response)
        {
            $errors = $step->getErrors();
            $output->writeln('<error>'.implode("\n", $errors).'</error>');
        } else {
            $output->writeln('<info>Your applicaiton has been successfully installed</info>');
        }
    }
}


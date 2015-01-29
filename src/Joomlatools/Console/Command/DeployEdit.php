<?php
/**
 * @copyright	Copyright (C) 2007 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		Mozilla Public License, version 2.0
 * @link		http://github.com/joomlatools/joomla-console for the canonical source repository
 */

namespace Joomlatools\Console\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Dumper;

class DeployEdit extends DeployAbstract
{

    protected $standard_configs = array('repository', 'app', 'deploy_to', 'backup', 'branch', 'remote_cache', 'scm', 'releases', 'revision');

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('deploy:edit')
            ->setDescription('Edit your environment configuration details')
            ->addOption(
                'environment',
                null,
                InputOption::VALUE_REQUIRED,
                "Which deploy environment would you like to use",
                'development'
            )
            ->addOption(
                'repository',
                 null,
                InputOption::VALUE_OPTIONAL,
                "What is the location of your SCM repository",
                null
            )
            ->addOption(
                'deploy_to',
                null,
                InputOption::VALUE_OPTIONAL,
                "Where would you like the files to be deployed to on the server",
                null
            )
            ->addOption(
                'app',
                null,
                InputOption::VALUE_OPTIONAL,
                "What is the your server IP address",
                null
            )
            ->addOption(
                'user',
                null,
                InputOption::VALUE_OPTIONAL,
                "Server credentials in the form of user:password",
                null
            )
            ->addOption(
                'database',
                null,
                InputOption::VALUE_OPTIONAL,
                "Mysql credentials in the form of ip:database:user:password",
                null
            )
            ->addOption(
                'backup',
                null,
                InputOption::VALUE_OPTIONAL,
                "Do you wish to backup on each deploy true / false",
                null
            )
            ->addOption(
                'branch',
                null,
                InputOption::VALUE_OPTIONAL,
                "Which SCM branch do you want to use?",
                null
            )
            ->addOption(
                'remote_cache',
                null,
                InputOption::VALUE_OPTIONAL,
                "Do you wish to use a remote cache true / false",
                null
            )
            ->addOption(
                'scm',
                null,
                InputOption::VALUE_OPTIONAL,
                "Which SCM do you use",
                null
            )
            ->addOption(
                'releases',
                null,
                InputOption::VALUE_OPTIONAL,
                "Do you want to use SCM a release",
                null
            )
            ->addOption(
                'revision',
                null,
                InputOption::VALUE_OPTIONAL,
                "Do you want to use a SCM revision",
                null
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $new_configuration = $this->configuration;

        $user = $input->getOption('user');
        if(strlen($user) && strpos($user, ":"))
        {
            $array = explode(":", $user);
            $new_configuration['user'] = $array[0];
            $new_configuration['password'] = $array[1];
        }

        $db = $input->getOption('database');
        if(strlen($db) && strpos($db, ":"))
        {
            $array = explode(":", $db);
            $new_configuration['db'] = $array[0];
            $new_configuration['database']['name'] = $array[1];
            $new_configuration['database']['user'] = $array[2];
            $new_configuration['database']['password'] = $array[3];
        }

        foreach($this->standard_configs as $config)
        {
            if(strlen($input->getOption($config))){
                $new_configuration[$config] = $input->getOption($config);
             }
        }

        $this->saveConfiguration($input, $output, $new_configuration);
    }

    public function saveConfiguration(InputInterface $input, OutputInterface $output, $configuration)
    {
        $dumper = new Dumper();

        $yaml = $dumper->dump($configuration, 2);

        file_put_contents($this->target_dir . '/deploy/' . $this->environment . '.yml', $yaml);
    }
}

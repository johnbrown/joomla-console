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
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $old_configuration = $this->configuration;
        $new_configuration = $this->configuration;

        $user = $input->getOption('user');
        if(strlen($user) && strpos($input->getOption('user'), ":"))
        {
            $array = explode(":", $user);
            $new_configuration['user'] = $array[0];
            $new_configuration['password'] = $array[1];
        }

        $repository = $input->getOption('repository');
        if(strlen($repository)){
            $new_configuration['repository'] = $repository;
        }

        $app = $input->getOption('app');
        if(strlen($app)){
            $new_configuration['app'] = $repository;
        }

        $db = $input->getOption('database');
        if(strlen($db))
        {
            $array = explode(":", $db);
            $new_configuration['db'] = $array[0];
            $new_configuration['user'] = $array[1];
            $new_configuration['password'] = $array[2];
        }

        if(strlen($input->getOption('deploy_to'))){
            $new_configuration['deploy_to'] = $input->getOption('deploy_to');
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

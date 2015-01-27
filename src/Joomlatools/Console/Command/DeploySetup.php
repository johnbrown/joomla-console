<?php
/**
 * @copyright	Copyright (C) 2007 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		Mozilla Public License, version 2.0
 * @link		http://github.com/joomlatools/joomla-console for the canonical source repository
 */

namespace Joomlatools\Console\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeploySetup extends DeployAbstract
{
    protected $environment;

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('deploy:setup')
            ->setDescription('Set up deploy on your server')
            ->addArgument(
                'environment',
                InputArgument::OPTIONAL,
                'Please state the deploy environment... will default to development',
                'development'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->environment = $input->getArgument('environment');

        //benchmark the default development environment to create future ones
        if(!file_exists($this->target_dir . '/deploy/' . $this->environment . '.php')){
            $output->writeln("<info>Sorry environment file not found</info>");
            return;
        }

        $result = shell_exec('pom ' . $this->environment .' deploy:setup');
        $output->writeln($result);

    }
}

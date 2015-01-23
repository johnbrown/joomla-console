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

class DeployEnvironment extends DeployAbstract
{
    protected $environment;

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('deploy:environment')
            ->setDescription('Amend deployment configuration for your server')
            ->addArgument(
                'environment',
                InputArgument::REQUIRED,
                'Please state the deploy environment... will default to development',
                null
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->environment = $input->getArgument('environment');

        if(!file_exists( $this->target_dir . '/deploy')){
            $output->writeln('<warning>Must be valid /deploy folder... run deploy:init first</warning>');
            return;
        }

        $env_file = $this->target_dir . '/deploy/' . $this->environment .'.php';

        if(!file_exists($env_file)){

            `cp $this->target_dir/deploy/template.php $this->target_dir/deploy/$this->environment.php`;

            $output->writeln('<info>new environment ' . $this->environment . ' created at:</info>');
            $output->writeln($this->target_dir . '/deploy/' . $this->environment . '.php');
        }

        return;
    }
}

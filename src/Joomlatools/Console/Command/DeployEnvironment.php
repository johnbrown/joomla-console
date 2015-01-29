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
    /**
     * File cache
     *
     * @var string
     */
    protected static $files;

    protected $environment;

    protected function configure()
    {
        parent::configure();

        if (!self::$files) {
            self::$files = realpath(__DIR__.'/../../../../bin/.files');
        }

        $this
            ->setName('deploy:environment')
            ->setDescription('Amend deployment configuration for your server')
            ->addArgument(
                'environment',
                InputArgument::REQUIRED,
                'Please state the name of the new deploy environment',
                null
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->environment = $input->getArgument('environment');

        if(!file_exists( $this->target_dir . '/deploy'))
        {
            $output->writeln('<warning>Must be valid /deploy folder... run deploy:init first</warning>');
            return;
        }

        if(!file_exists($this->target_dir . '/deploy/' . $this->environment .'.yml'))
        {
            $template_path = self::$files . '/configuration.yml';

            `cp $template_path $this->target_dir/deploy/$this->environment.yml`;

            $output->writeln('<info>new environment ' . $this->environment . ' created at:</info>');
            $output->writeln($this->target_dir . '/deploy/' . $this->environment . '.yml');
        }
    }
}

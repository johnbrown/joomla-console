<?php
/**
 * @copyright	Copyright (C) 2007 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		Mozilla Public License, version 2.0
 * @link		http://github.com/joomlatools/joomla-console for the canonical source repository
 */

namespace Joomlatools\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

abstract class DeployAbstract extends Command
{
    protected $environment;

    protected $configuration;

    protected function configure()
    {
        $this->addOption(
            'environment',
            null,
            InputOption::VALUE_REQUIRED,
            "Which deploy environment would you like to use",
            'development'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = preg_match('/\\/var\\/www\\/([^\\/]+)/i', getCwd(), $matches);

        $this->environment  = $input->getOption('environment');

        if(count($matches))
        {
            $this->site = $matches[1];
            $this->target_db = 'sites_' . $this->site;
            $this->target_dir = $matches[0];
        }

        if(getcwd() != $this->target_dir)
        {
            $output->write('<comment>You must be in the project root directory to proceed</comment>');
            exit();
        }

        $this->getConfiguration();

        if($this->configuration['app'] == "")
        {
            $output->writeln('<comment>Sorry you must first provide the ip address of the sever in your config file before proceeding</comment>');
            $output->writeln($this->target_dir . '/deploy/' . $this->environment . '.yml');
            exit();
        }


        if($this->configuration['repository'] == "")
        {
            $output->writeln("<comment>You haven't specified a repository, please edit your configuration</comment>");
            $output->writeln($this->target_dir . '/deploy/' . $this->environment . '.yml');
            exit();
        }
    }

    public function getConfiguration()
    {
        if(file_exists($this->target_dir . '/deploy/' . $this->environment . '.yml'))
        {
            $yaml = new Parser;
            $this->configuration = $yaml->parse(file_get_contents($this->target_dir . '/deploy/' . $this->environment . '.yml'));
        }

        return $this->configuration;
    }

    public function saveConfiguration($configuration)
    {
        $dumper = new Dumper();

        $yaml = $dumper->dump($configuration, 2);

        file_put_contents($this->target_dir . '/deploy/' . $this->environment . '.yml', $yaml);

        $this->configuration = $configuration;
    }
}
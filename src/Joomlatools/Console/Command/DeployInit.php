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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Dumper;

class DeployInit extends DeployAbstract
{

    /**
     * File cache
     *
     * @var string
     */
    protected static $files;

    protected function configure()
    {
        parent::configure();

        if (!self::$files) {
            self::$files = realpath(__DIR__.'/../../../../bin/.files');
        }

        $this
            ->setName('deploy:init')
            ->setDescription('Generate vendor deploy and deploy folder');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        //need to see if pom has been initiated
        if(!strpos(exec('pom init'), "http://ripeworks.com/pomander")){
            `composer global require pomander/pomander:@stable`;

            $result = exec('composer install');
            $output->write($result);
        }

        if(!file_exists($this->target_dir . '/deploy')){
            `pom init`;
        }

        //switch default php env configuration for yaml
        if(file_exists($this->target_dir . '/deploy/development.php'))
        {
            $template_path = self::$files . '/configuration.yml';

            `cp $template_path $this->target_dir/deploy/development.yml`;

            unlink($this->target_dir . '/deploy/development.php');

            $configuration = $this->getconfiguration();

            $configuration['deploy_to'] = $this->target_dir;
            $configuration['database']['name'] = $this->target_db;

            $this->saveConfiguration($configuration);
        }

        $output->writeln('<info>pom initiated</info>');
    }
}

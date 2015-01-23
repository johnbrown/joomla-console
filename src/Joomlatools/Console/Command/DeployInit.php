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

class DeployInit extends DeployAbstract
{
    protected function configure()
    {
        parent::configure();

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

        //benchmark the default development environment to create future ones
        if(!file_exists($this->target_dir . '/deploy/template.php')){
            `cp /var/www/helloworld/deploy/development.php /var/www/helloworld/deploy/template.php`;
        }

        $output->writeln('<info>pom initiated</info>');
    }
}

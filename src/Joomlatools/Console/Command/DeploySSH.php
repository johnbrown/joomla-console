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
use Symfony\Component\Console\Tester\CommandTester;

class DeploySSH extends DeployAbstract
{
    protected $user;

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('deploy:ssh')
            ->setDescription('Set up deploy on your server')
            ->addArgument(
                'user',
                InputArgument::REQUIRED,
                'Please state the user you want to create ssh access for',
                null
            )
            ->addArgument(
                'app',
                InputArgument::REQUIRED,
                'Please ip address of the server',
                null
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->user = $input->getArgument('user');
        $this->app = $input->getArgument('app');

        //first up we need to create a rsa key in a location that can be read
        if(!file_exists($this->target_dir . '/deploy/id_rsa')){
            $result = shell_exec('ssh-keygen -t rsa -f '. $this->target_dir . '/deploy/id_rsa');
        }

        //next up we want to get this new public key up to the server
        //@todo is there a way of checking whether this public key already exists on the server
        $result = exec('cat '. $this->target_dir . '/deploy/id_rsa.pub | ssh ' . $this->user . '@' . $this->app . ' "mkdir -p ~/.ssh && cat >>  ~/.ssh/authorized_keys"');
        $output->writeln($result);

        $output->writeln('<info>Be sure to add the following line to your environment file</info>');
        $output->writeln('->key_path("' . $this->target_dir . '/deploy/id_rsa")');
    }
}

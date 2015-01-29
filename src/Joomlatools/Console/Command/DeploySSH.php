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
use Symfony\Component\Yaml\Dumper;

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
                'Please specify the server user',
                null
            )
            ->addArgument(
                'app',
                InputArgument::REQUIRED,
                'Please specify the IP address of your server',
                null
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        //want to overload defaults set by the configuration file
        $this->configuration['user'] = $input->getArgument('user');
        $this->configuration['app'] = $input->getArgument('app');

        //first up we need to create a rsa key in a location that can be read
        if(!file_exists($this->target_dir . '/deploy/id_rsa')){
            shell_exec('ssh-keygen -t rsa -f '. $this->target_dir . '/deploy/id_rsa');
        }

        if(!$this->configuration['key_path_deployed'])
        {
            $result = exec('cat '. $this->target_dir . '/deploy/id_rsa.pub | ssh ' . $this->configuration['user'] . '@' . $this->configuration['app'] . ' "mkdir -p ~/.ssh && cat >>  ~/.ssh/authorized_keys"');
            $output->writeln($result);

            $this->configuration['key_path_deployed'] = true;
            $this->configuration['key_path'] = $this->target_dir . '/deploy/id_rsa';

            $this->saveConfiguration($input, $output);

            $output->writeln('<info>SSH keys have been added to your server, and your deploy configurations updated</info>');
            return;
        }

        $output->writeln('<comment>It appears as though your SSH key has already been deployed</comment>');
        $output->writeln('set `key_path_deployed` to false here if you want to resend:');
        $output->writeln($this->target_dir . '/deploy');
    }

    public function saveConfiguration(InputInterface $input, OutputInterface $output)
    {
        $dumper = new Dumper();

        $yaml = $dumper->dump($this->configuration, 2);

        file_put_contents($this->target_dir . '/deploy/' . $this->environment . '.yml', $yaml);
    }
}

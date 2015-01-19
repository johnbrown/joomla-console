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

class DeployConfig extends DeployAbstract
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('deploy:config')
            ->setDescription('Amend deployment configuration for your server')
            ->addArgument(
                'repository-url',
                InputArgument::REQUIRED,
                'You must provide a repository url'
            )
            ->addArgument(
                'app',
                InputArgument::REQUIRED,
                'Provide the IP of the server'
            )
            ->addArgument(
                'environment',
                InputArgument::OPTIONAL,
                'Please state the deploy environment... will default to development',
                'development'
            )
            ->addOption(
                'user',
                null,
                InputOption::VALUE_OPTIONAL,
                "Provide the user to deploy stuff to the server",
                'deploy'
            )
            ->addOption(
                'branch',
                null,
                InputOption::VALUE_OPTIONAL,
                "Please specify which branch to use --default is develop",
                "develop"
            )
            ->addOption(
                'password',
                null,
                InputOption::VALUE_REQUIRED,
                "Provide the password to connect to the server"
            )
            ->addOption(
                'deploy_to',
                null,
                InputOption::VALUE_OPTIONAL,
                "Location on server to deploy files",
                "/var/www/html/import/"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $output->writeln($input->getArgument('environment'));

        if(!file_exists( $this->target_dir . '/deploy')){
            $output->writeln('<warning>Must be valid /deploy folder... run deploy:init first</warning>');
            return;
        }

        $environment = $this->target_dir . '/deploy/' . $input->getArgument('environment') .'.php';
        $repositoryUrl = $input->getArgument('repository-url');
        $app = $input->getArgument('app');
        $password = $input->getOption('password');
        $user = $input->getOption('user');
        $branch = $input->getOption('branch');
        $deploy_to = $input->getOption('deploy_to');

        $output->writeln(var_dump($input->getOption('deploy_to')));
        $output->writeln(var_dump($deploy_to));

        $output->writeln(var_dump($environment));

        if(!file_exists($environment)){
            `touch $environment`;

            $output->writeln('new environment created');
        }

        $handle = fopen($environment, 'w');

        $contents = <<<EOT
<?php

\$env->repository("$repositoryUrl")
    ->app("$app")
    ->deploy_to("$deploy_to")
    ->branch("$branch")
    ->url("$app")
    ->user("$user")
    ->password("$password")
;
EOT;

        fwrite($handle,$contents);
        fclose($handle);

        //very first thing to do with pomander is to set up

        $deploy = $input->getArgument('environment');

        `pom $deply deploy:setup`;

        return;
    }
}

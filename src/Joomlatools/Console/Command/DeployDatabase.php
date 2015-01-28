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

class DeployDatabase extends DeployAbstract
{
    protected $user;

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('deploy:database')
            ->setDescription('Migrate your database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        //first up we need to create a db export locally
        exec("mysqldump -u root -p --password='root' --host=127.0.0.1 sites_" . $this->site . " --lock-tables=FALSE --skip-add-drop-table | sed -e 's|INSERT INTO|REPLACE INTO|' -e 's|CREATE TABLE|CREATE TABLE IF NOT EXISTS|' > " . $this->target_dir . "/tmpdump.sql");

        //now over to pom to push this local db up
        $result = shell_exec('pom db:merge');

        $output->writeln($result);
    }
}

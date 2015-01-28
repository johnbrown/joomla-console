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
            ->setDescription('Set up deploy on your server');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->createPom($input, $output);
        $this->pushConfiguration($input, $output);
        $this->createDatabase($input, $output);
    }

    public function createPom(InputInterface $input, OutputInterface $output)
    {
        if(!file_exists($this->target_dir . '/deploy/' . $this->environment . '.yml')){
            $output->writeln("<info>Sorry environment file not found</info>");
            return;
        }

        $result = shell_exec('pom ' . $this->environment .' deploy:setup');
    }

    public function pushConfiguration(InputInterface $input, OutputInterface $output)
    {
        if(!file_exists($this->target_dir . '/configuration.php')){
            $output->writeln('<error>There is no configuration file locally</error>');
            return;
        }

        $result = exec('cp ' . $this->target_dir . '/configuration.php configurationLIVE.php');

        $source   = $this->target_dir.'/configuration.php';
        $target   = $this->target_dir.'/configurationLIVE.php';

        $contents = file_get_contents($source);
        $replace  = function($name, $value, &$contents) {
            $pattern = sprintf("#%s = '.*?'#", $name);
            $match   = preg_match($pattern, $contents);

            if(!$match)
            {
                $pattern 	 = "/^\s?(\})\s?$/m";
                $replacement = sprintf("\tpublic \$%s = '%s';\n}", $name, $value);
            }
            else $replacement = sprintf("%s = '%s'", $name, $value);

            $contents = preg_replace($pattern, $replacement, $contents);
        };

        $replacements = array(
            'user'      => $this->database['user'],
            'password'  => $this->database['password'],
        );

        foreach($replacements as $key => $value) {
            $replace($key, $value, $contents);
        }

        file_put_contents($target, $contents);
        chmod($target, 0644);

        //now we need to get this up to the server
        exec("scp " . $this->target_dir ."/configurationLIVE.php " . $this->user . "@" . $this->app . ":" . $this->target_dir . "/configuration.php");

        unlink($target);
    }

    public function createDatabase(InputInterface $input, OutputInterface $output)
    {
        $result = shell_exec('pom db:create');

        $output->writeln($result);

        //first up we need to create a db export locally
        exec("mysqldump -u root -p --password='root' --host=127.0.0.1 sites_" . $this->site . " --lock-tables=FALSE --skip-add-drop-table | sed -e 's|INSERT INTO|REPLACE INTO|' -e 's|CREATE TABLE|CREATE TABLE IF NOT EXISTS|' > /var/www/" . $this->site . "/tmpdump.sql");

        //now over to pom to push this local db up
        $result = shell_exec('pom db:merge');
        $output->writeln($result);
    }
}

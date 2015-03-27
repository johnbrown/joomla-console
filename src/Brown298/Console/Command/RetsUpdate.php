<?php
namespace Brown298\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Joomlatools\Console\Command\SiteAbstract;
use Joomlatools\Console\Joomla\Bootstrapper;


/**
 * Class RetsUpdate
 *
 * processes updates from the rets system
 */
class RetsUpdate extends SiteAbstract
{

    /**
     * @var string
     */
    protected $type;

    /**
     * @var null|string date to update from
     */
    protected $updateDate = null;

    /**
     * configure
     *
     */
    protected function configure()
    {
        $this
            ->setName('rets:update')
            ->setDescription('Runs an update against the MLS System')
            ->addOption(
                'server_root',
                null,
                InputOption::VALUE_REQUIRED,
                "Web server root",
                realpath(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'
                    . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'
                    . DIRECTORY_SEPARATOR . '..'
            )
            ->addOption('update_date', null, InputOption::VALUE_REQUIRED, "Date to update from", null)
            ->addArgument(
                'type',
                InputArgument::OPTIONAL,
                'type of update to perform: columns, trim, load, all'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->target_dir      = $input->getOption('server_root');
        $this->updateDate      = $input->getOption('update_date');
        $this->type            = ($input->getArgument('type') != null) ? $input->getArgument('type') : 'all';
        require_once($this->target_dir . DIRECTORY_SEPARATOR . 'configuration.php');
        $config          = new \JConfig();
        $this->target_db = $config->db;
        $this->mysql     = (object) array('user' => $config->user, 'password'=> $config->password);

        $this->check($input, $output);
        $this->process($input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function check(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists($this->target_dir)) {
            throw new \RuntimeException(sprintf('Site not found: %s', $this->site));
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    public function process(InputInterface $input, OutputInterface $output)
    {
        $app = Bootstrapper::getApplication($this->target_dir);

        require_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_joomanager"
            . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "helper.php");

        // Output buffer is used as a guard against Joomla including ._ files when searching for adapters
        // See: http://kadin.sdf-us.org/weblog/technology/software/deleting-dot-underscore-files.html
//        ob_start();
        switch ($this->type) {
            case 'columns':
                \JoomanagerHelpers::loadRetsColumns();
                break;
            case 'trim':
                \JoomanagerHelpers::trimRetsData();
                break;
            case 'load':
                \JoomanagerHelpers::saverets($this->updateDate);
                break;
            case 'all':
                \JoomanagerHelpers::loadRetsColumns();
//                $this->readOutput($output);
                \JoomanagerHelpers::trimRetsData();
//                $this->readOutput($output);
                \JoomanagerHelpers::saverets($this->updateDate);
                break;
        }
//        $this->readOutput($output);
//        ob_end_flush();
    }

    /**
     * @param OutputInterface $output
     */
    private function readOutput( OutputInterface $output)
    {
        $cmdOutput = ob_get_clean();

        $breaks    = array("<br />","<br>","<br/>");
        $cmdOutput = str_ireplace($breaks, "\r\n", $cmdOutput);

        $output->write($cmdOutput);
    }

}
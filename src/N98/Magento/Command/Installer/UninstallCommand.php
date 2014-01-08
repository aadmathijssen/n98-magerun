<?php

namespace N98\Magento\Command\Installer;

use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Filesystem;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UninstallCommand
 *
 * @codeCoverageIgnore
 * @package N98\Magento\Command\Installer
 */
class UninstallCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('uninstall')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force')
            ->setDescription('Uninstall magento (drops database and empties current folder')
        ;

        $help = <<<HELP
**Please be careful: This removes all data from your installation.**
HELP;
        $this->setHelp($help);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        $this->getApplication()->setAutoExit(false);
        $dialog = $this->getHelperSet()->get('dialog');
        /* @var $dialog \Symfony\Component\Console\Helper\DialogHelper */

        $shouldUninstall = $input->getOption('force');
        if (!$shouldUninstall) {
            $shouldUninstall = $dialog->askConfirmation($output, '<question>Really uninstall ?</question> <comment>[n]</comment>: ', false);
        }

        if ($shouldUninstall) {
            $input = new StringInput('db:drop --force');
            $this->getApplication()->run($input, $output);
            $fileSystem = new Filesystem();
            $output->writeln('<info>Remove directory </info><comment>' . $this->_magentoRootFolder . '</comment>');
            try {
                $fileSystem->recursiveRemoveDirectory($this->_magentoRootFolder);
            } catch (\Exception $e) {
                $output->writeln('<error>' . $e->getMessage() . '</error>');
            }
            $output->writeln('<info>Done</info>');
        }
    }
}
<?php

/**
 * @package     Jdocmanual
 * @subpackage  Administrator
 *
 * @copyright   Copyright (C) 2023 - 2026 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://jdocmanual.org/
 */

namespace Cefjdemos\Component\Jdocmanual\Administrator\Cli;

use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Cefjdemos\Component\Jdocmanual\Administrator\Cli\Buildarticles;
use Cefjdemos\Component\Jdocmanual\Administrator\Cli\Buildmenus;
use Cefjdemos\Component\Jdocmanual\Administrator\Cli\Buildproxy;
use Cefjdemos\Component\Jdocmanual\Administrator\Cli\Checkhistory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class JdocmanualCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var     string
     * @since   4.0
     */
    protected static $defaultName = 'jdocmanual:action';

    /**
     * @var     InputInterface
     * @since   4.0
     */
    private $cliInput;

    /**
     * SymfonyStyle Object
     *
     * @var     SymfonyStyle
     * @since   4.0
     */
    private $ioStyle;

    /**
     * Configures the IO
     *
     * @param   InputInterface   $input   Console Input
     * @param   OutputInterface  $output  Console Output
     * @since   4.0
     *
     * @return  void
     */
    private function configureIO(InputInterface $input, OutputInterface $output)
    {
        $this->cliInput = $input;
        $this->ioStyle = new SymfonyStyle($input, $output);
    }

    /**
     * Initialise the command.
     *
     * @return  void
     *
     * @since   4.0
     */
    protected function configure(): void
    {
        $this->addArgument(
            'action',
            InputArgument::REQUIRED,
            'name of action'
        );
        $help = "<info>%command.name%</info> Runs Jdocmanual action jobs
				\nUsage: <info>php %command.full_name% action (one of buildarticles buildmenus, buildproxy or checkhistory)</info>";
        $this->addArgument(
            'manual',
            InputArgument::REQUIRED,
            'one of the installed manuals, for example developer, docs, help or user'
        );
        $this->addArgument(
            'language',
            InputArgument::REQUIRED,
            'language code, for example example: de'
        );
        $this->addArgument(
            'time_back',
            InputArgument::OPTIONAL,
            'integer number of minutes to look back for article changes (0 if omitted forces full rebuild)'
        );

        $this->setDescription('Called on command line or by cron to run Jdocmanual action jobs.');
        $this->setHelp($help);
    }

    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     *
     * @since   4.0
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $this->configureIO($input, $output);

        $action = $this->cliInput->getArgument('action');
        $manual = $this->cliInput->getArgument('manual');
        $language = $this->cliInput->getArgument('language');
        $force = $this->cliInput->getArgument('force');

        switch ($action) {
            case 'buildarticles':
                $buildarticles = new Buildarticles();
                $result = $buildarticles->go($manual, $language, $force);
                break;
            case 'buildmenus':
                $buildmenus = new Buildmenus();
                $result = $buildmenus->go($manual, $language);
                break;
            case 'buildproxy':
                $buildproxy = new Buildproxy();
                $result = $buildproxy->go($manual, $language);
                break;
            case 'checkhistory':
                $checkhistory = new Checkhistory();
                $result = $checkhistory->go($manual, $language);
                break;
            default:
                $this->ioStyle->error("Unknwon action: {$action}");
                return 0;
        }
        $this->ioStyle->success("That seemed to work: {$action}");
        echo $result;

        return 1;
    }
}

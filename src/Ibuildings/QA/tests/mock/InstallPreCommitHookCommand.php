<?php

/**
 * This file is part of Ibuildings QA-Tools.
 *
 * (c) Ibuildings
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ibuildings\QA\tests\mock;

use Ibuildings\QA\Tools\Common\CommandExistenceChecker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InstallPreCommitHookCommand
 *
 * @package Ibuildings\QA\tests\mock
 */
class InstallPreCommitHookCommand extends \Ibuildings\QA\Tools\Common\Console\InstallPreCommitHookCommand
{
    /**
     * @var CommandExistenceChecker
     */
    protected $checker;

    /**
     * used to save rendered precommithook instead of saving it to file
     *
     * @var string
     */
    public $precommitHookContent;

    /**
     * Overwrite to be able to catch rendered output to a variable
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function writePreCommitHook(InputInterface $input, OutputInterface $output)
    {
        $this->precommitHookContent = $this->twig->render(
            'pre-commit.dist',
            $this->settings->getArrayCopy()
        );

        $output->writeln("\n<info>Commit hook written</info>");
    }

    /**
     * @param \Ibuildings\QA\Tools\Common\CommandExistenceChecker $checker
     */
    public function setChecker($checker)
    {
        $this->checker = $checker;
    }

    /**
     * Overwrite to be able to use a mock commandExistence checker
     *
     * @return CommandExistenceChecker
     */
    protected function getCommandExistenceChecker()
    {
        if (isset($this->checker)) {
            return $this->checker;
        }

        return new CommandExistenceChecker();
    }
}

<?php

/**
 * This file is part of Ibuildings QA-Tools.
 *
 * (c) Ibuildings
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ibuildings\QA\Tools\Javascript\Configurator;

use Ibuildings\QA\Tools\Common\Configurator\ConfigurationWriterInterface;
use Ibuildings\QA\Tools\Common\Settings;
use Ibuildings\QA\Tools\Javascript\Console\InstallJsHintCommand;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class JsHintConfigurator
 * @package Ibuildings\QA\Tools\Javascript\Configurator
 */
class JsHintConfigurator implements ConfigurationWriterInterface
{
    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * @var \Symfony\Component\Console\Helper\DialogHelper
     */
    protected $dialog;

    /**
     * @var \Ibuildings\QA\Tools\Common\Settings
     */
    protected $settings;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var \Ibuildings\QA\Tools\Javascript\Console\InstallJsHintCommand
     */
    protected $installJsHintCommand;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param DialogHelper $dialog
     * @param Settings $settings
     * @param \Twig_Environment $twig,
     * @param InstallJsHintCommand $installJsHintCommand
     */
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        DialogHelper $dialog,
        Settings $settings,
        \Twig_Environment $twig,
        InstallJsHintCommand $installJsHintCommand
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->dialog = $dialog;
        $this->settings = $settings;
        $this->twig = $twig;
        $this->installJsHintCommand = $installJsHintCommand;
    }

    public function configure()
    {
        if (!$this->settings['enableJsTools']) {
            $this->settings['enableJsHint'] = false;
            return;
        }

        $default = $this->settings->getDefaultValueFor('enableJsHint', true);
        $this->settings['enableJsHint'] = $this->dialog->askConfirmation(
            $this->output,
            "Do you want to enable JSHint?",
            $default
        );

        if ($this->settings['enableJsHint'] === false) {
            return;
        }

        $statusCode = $this->installJsHintCommand->run($this->input, $this->output);
        if ($statusCode) {
            $this->settings['enableJsHint'] = false;
        }
    }

    /**
     * Writes config file

     * @codeCoverageIgnore
     */
    public function writeConfig()
    {
        $filesystem = new Filesystem();

        try {
            $filesystem->dumpFile(
                $this->settings->getBaseDir() . '/.jshintrc',
                $this->twig->render('.jshintrc.dist', $this->settings->getArrayCopy())
            );
        } catch (IOException $e) {
            $this->output->writeln(sprintf(
                '<error>Could not write .jshintrc, error: "%s"</error>',
                $e->getMessage()
            ));
            return;
        }

        $this->output->writeln("\n<info>Config file for JSHint written</info>");
    }

    /**
     * @inheritdoc
     */
    public function shouldWrite()
    {
        return $this->settings['enableJsHint'] === true;
    }
}

<?php

namespace terra\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use terra\Command;
use terra\Factory;
use terra\Terra;


/**
 * Class Application.
 *
 * @package terra\Console
 */
class Application extends BaseApplication {

  /**
   * @var Terra
   */
  protected $terra;

  /**
   * @var Process
   * Process
   */
  protected $process = NULL;

  /**
   * {@inheritdoc}
   */
  public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN') {
    parent::__construct('Terra', Terra::VERSION);
  }

  /**
   * {@inheritdoc}
   */
  public function doRun(InputInterface $input, OutputInterface $output) {
    // Check if docker exists if not throw an error.
    $process = $this->getProcess('docker --version');
    $process->run();
    if (!$process->isSuccessful()) {
      // If you do not have docker we do nothing.
      throw new \RuntimeException($process->getErrorOutput());
    }
    return parent::doRun($input, $output);
  }

  /**
   * Allow terra to overwrite the process command.
   *
   * @param $process
   */
  public function setProcess($process) {
    $this->process = $process;
  }

  /**
   * Used instead of Symfony\Component\Process\Process so we can easily mock it.
   *
   * This returns either an instantiated Symfony\Component\Process\Process or a mock object.
   * @param $commandline
   * @param null $cwd
   * @param array $env
   * @param null $input
   * @param int $timeout
   * @param array $options
   * @return Process
   *
   * @see Symfony\Component\Process\Process
   */
  public function getProcess($commandline, $cwd = null, array $env = null, $input = null, $timeout = 60, array $options = array()) {
    if ($this->process === NULL) {
      // @codeCoverageIgnoreStart
      // We ignore this since we mock it.
      return new Process($commandline, $cwd, $env, $input, $timeout, $options);
      // @codeCoverageIgnoreEnd
    }

    return $this->process;
  }

  /**
   * Initializes all the flo commands.
   */
  protected function getDefaultCommands() {
    $commands = parent::getDefaultCommands();
    $commands[] = new Command\App\AppAdd();
    return $commands;
  }

  /**
   * Get a configured Terra object.
   *
   * @return Terra
   *   A configured Terra object.
   */
  public function getTerra() {
    if (NULL === $this->terra) {
      $this->terra = Factory::create();
    }
    return $this->terra;
  }

}
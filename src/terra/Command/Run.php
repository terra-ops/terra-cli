<?php
/**
 * Created by PhpStorm.
 * User: fcarey
 * Date: 9/18/15
 * Time: 11:09 PM
 */

namespace terra\Command;

use terra\Factory\EnvironmentFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;



class Run extends Command {
  protected function configure() {
    $this
      ->setName('environment:run')
      ->setDescription("Run a command on one of an environment's containers.")
      ->addArgument(
        'app_name',
        InputArgument::REQUIRED,
        'The name the app'
      )
      ->addArgument(
        'environment_name',
        InputArgument::REQUIRED,
        'The name the environment'
      )
      ->addArgument(
        'service',
        InputArgument::REQUIRED,
        'The name the service to run command on.'
      )
      ->addArgument(
        'commands',
        InputArgument::IS_ARRAY,
        'The command to run and its arguments'
      );
  }

  protected function execute(InputInterface $input, OutputInterface $output) {

    // Ask for an app and environment.
    $this->getApp($input, $output);
    $this->getEnvironment($input, $output);

    $environment_factory = $this->getEnvironmentFactory();
    $service = $input->getArgument('service');
    $commands = implode(" ",$input->getArgument('commands'));

    // Use docker-compose to run the command on the specified "service"
    $process = new Process("docker-compose -f {$environment_factory->getDockerComposePath()}/docker-compose.yml run $service $commands");
    echo $process->getCommandLine();

    $process->setTty(true)->run();
    // executes after the command finishes
    if (!$process->isSuccessful()) {
      throw new \RuntimeException($process->getErrorOutput());
    }

    echo $process->getOutput();
    return;
  }
}

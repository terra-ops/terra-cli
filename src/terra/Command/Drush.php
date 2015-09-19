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



class Drush extends Command {
  protected function configure() {
    $this
      ->setName('drush')
      ->setDescription('run something on a container')
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
        'commands',
        InputArgument::IS_ARRAY,
        'The command to run and its arguments'
      );
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    // If there are no apps, return
    if (count($this->getApplication()
        ->getTerra()
        ->getConfig()
        ->get('apps')) == 0
    ) {
      $output->writeln('<comment>There are no apps!</comment>');
      $output->writeln('Use the command <info>terra app:add</info> to add your first app.');

      return;
    }

    $app_name = $input->getArgument('app_name');
    $environment_name = $input->getArgument('environment_name');
    $commands = implode(" ",$input->getArgument('commands'));

    $app = $this->getApplication()
      ->getTerra()
      ->getConfig()
      ->get('apps', $app_name);

    // If no environments:
    if (count(($app['environments'])) == 0) {
      $output->writeln('<comment>There are no environments!</comment>');
      $output->writeln('Use the command <info>terra environment:add</info> to add your first environment.');

      return;
    }

    // If no environment by that name...
    if (!isset($app['environments'][$environment_name])) {
      $output->writeln("<error>There is no environment named {$environment_name} in the app {$app_name}</error>");

      return;
    }

    $environment = $app['environments'][$environment_name];
    $environment_factory = new EnvironmentFactory($environment, $app);

    // Get current scale of app service

    $process = new Process("docker-compose -f {$environment_factory
      ->getDockerComposePath()}/docker-compose.yml run drush drush --root=/var/www/html $commands");
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

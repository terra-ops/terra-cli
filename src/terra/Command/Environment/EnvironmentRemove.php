<?php

namespace terra\Command\Environment;

use terra\Command\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\ChoiceQuestion;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use terra\Factory\EnvironmentFactory;

class EnvironmentRemove extends Command
{
  protected function configure()
  {
    $this
      ->setName('environment:remove')
      ->setDescription('Removes an environment.')
      ->addArgument(
        'app_name',
        InputArgument::OPTIONAL,
        'The name the app to remove.'
      )
      ->addArgument(
        'environment_name',
        InputArgument::OPTIONAL,
        'The name the environment to remove.'
      )
    ;
  }
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // If there are no apps, return
    if (count($this->getApplication()->getTerra()->getConfig()->get('apps')) == 0) {
      $output->writeln("<comment>There are no apps to remove!</comment>");
      $output->writeln("Use the command <info>terra app:add</info> to add your first app.");
      return;
    }

    $helper = $this->getHelper('question');
    $app_name = $input->getArgument('app_name');
    $environment_name = $input->getArgument('environment_name');

    // If no name specified provide options
    if (empty($app_name)) {
      $question = new ChoiceQuestion(
        'Which app? ',
        array_keys($this->getApplication()->getTerra()->getConfig()->get('apps')),
        NULL
      );
      $app_name = $helper->ask($input, $output, $question);
    }

    $app = $this->getApplication()->getTerra()->getConfig()->get('apps', $app_name);

    // If no environments:
    if (count(($app['environments'])) == 0) {
      $output->writeln("<comment>There are no environments for the app $app_name!</comment>");
      $output->writeln("Use the command <info>terra environment:add</info> to add your first environment.");
      return;
    }

    // If no environment name specified provide options
    if (empty($environment_name)) {
      $question = new ChoiceQuestion(
        'Which environment? ',
        array_keys($app['environments']),
        NULL
      );
      $environment_name = $helper->ask($input, $output, $question);
    }

    $environment = $app['environments'][$environment_name];

    // Confirm removal of the app.
    $question = new ConfirmationQuestion("Are you sure you would like to remove the environment <question>$app_name:$environment_name</question>?  All files at {$environment['path']} will be deleted, and all containers will be killed. [y/N] ", false);
    if (!$helper->ask($input, $output, $question)) {
      $output->writeln('<error>Cancelled</error>');
      return;
    }
    else {

      // Remove the environment from config registry.
      // @TODO: Move this to EnvironmentFactory class

      // Remove files
      $fs = new Filesystem();

      try {
        $fs->remove(array(
          $environment['path']
        ));
        $output->writeln("<info>Files for environment $app_name:$environment_name has been deleted.</info>");
      } catch (IOExceptionInterface $e) {
        $output->writeln("<error>Unable to remove ".$e->getPath() . "</error>");
      }

      // Destroy the environment
      $environmentFactory = new EnvironmentFactory($environment, $app);
      $environmentFactory->destroy();

      unset($app['environments'][$environment_name]);
      $this->getApplication()->getTerra()->getConfig()->add('apps', $app_name, $app);
      $this->getApplication()->getTerra()->getConfig()->save();

      $output->writeln("<info>Environment $app_name:$environment_name has been removed.</info>");

    }
  }
}

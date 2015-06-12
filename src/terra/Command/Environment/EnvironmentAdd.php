<?php

namespace terra\Command\Environment;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;

use terra\Factory\EnvironmentFactory;

// ...

class EnvironmentAdd extends Command
{
  protected function configure()
  {
    $this
      ->setName('environment:add')
      ->setDescription('Adds a new environment.')
      ->addArgument(
        'app',
        InputArgument::OPTIONAL,
        'The app you would like to add an environment for.'
      )
      ->addArgument(
        'name',
        InputArgument::OPTIONAL,
        'The name of the environment.'
      )
      ->addArgument(
        'path',
        InputArgument::OPTIONAL,
        'The path to the environment.'
      )
      ->addOption(
        'init-environment',
        '',
        InputArgument::OPTIONAL,
        'Clone and initiate this environment.'
      )
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // App
    $helper = $this->getHelper('question');
    $app_name = $input->getArgument('app');

    if (empty($app_name)) {
      $question = new ChoiceQuestion(
        'For which app? ',
        array_keys($this->getApplication()->getTerra()->getConfig()->get('apps')),
        0
      );
      $app_name = $helper->ask($input, $output, $question);
    }
    $app = $this->getApplication()->getTerra()->getConfig()->get('apps', $app_name);

    // Environment Name
    $environment_name = $input->getArgument('name');
    if (empty($environment_name)) {
      $question = new Question('Environment Name: ', '');
      $environment_name = $helper->ask($input, $output, $question);
    }

    // Path
    $path = $input->getArgument('path');
    if (empty($path)) {
      $default_path = realpath('.') . '/' . $app_name . '/' . $environment_name;
      $question = new Question("Path: ($default_path)", '');
      $path = $helper->ask($input, $output, $question);
      if (empty($path)) {
        $path = $default_path;
      }
    }

    // Check for path
    $fs = new Filesystem();
    if (!$fs->isAbsolutePath($path)) {
      $path = getcwd() . '/' . $path;
    }

    // Environment object
    $environment = array(
      'name' => $environment_name,
      'path' => $path,
    );

    // Save environment to config.
    $this->getApplication()->getTerra()->getConfig()->add('apps', array($app_name, 'environments', $environment_name), $environment);
    $this->getApplication()->getTerra()->getConfig()->save();

    $output->writeln('<info>Environment saved to registry.</info>');

    // Prepare the environment factory.
    $environment['app'] = $this->getApplication()->getTerra()->getConfig()->get('apps', $app_name);
    unset($environment['app']['environments']);

    // Clone the apps source code to the desired path.
    $environmentFactory = new EnvironmentFactory($environment, $app_name);
    $environmentFactory->init($path);

  }
}
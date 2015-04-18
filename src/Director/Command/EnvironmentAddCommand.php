<?php

namespace Director\Command;

use Director\DirectorApplication;
use Director\Factory\EnvironmentFactory;
use Director\Model\Environment;
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
// ...

class EnvironmentAddCommand extends Command
{
  public $director;

  function __construct(DirectorApplication $director) {
    parent::__construct();
    $this->director = $director;
  }

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
        array_keys($this->director->config['apps']),
        0
      );
      $app_name = $helper->ask($input, $output, $question);
    }
    $app = $this->director->getApp($app_name);

    // Environment Name
    $question = new Question('Environment Name: ', '');
    $name = $helper->ask($input, $output, $question);

    // Path
    $question = new Question('Path: ', '');
    $path = $helper->ask($input, $output, $question);

    // Check for path
    $fs = new Filesystem();
    if (!$fs->isAbsolutePath($path)) {
      $path = getcwd() . '/' . $path;
    }

    $environment = new Environment($name, $path, $app->getSourceUrl());
    $this->director->config['apps'][$app_name]['environments'][$name] = (array) $environment;

    $this->director->saveData();
    $output->writeln("OK Saving environment $name");

    $question = new ConfirmationQuestion("Clone {$app->source_url} to {$path}? ", false);
    if (!$helper->ask($input, $output, $question)) {
      return;
    }

    // Clone the apps source code to the desired path.
    $environmentFactory = new EnvironmentFactory($environment, $app_name, $this->director);
    $environmentFactory->init($path);

    // Look for .director.yml and save to environment.
    $this->director->config['apps'][$app_name]['environments'][$name]['config'] = $environmentFactory->getConfig();
    $this->director->saveData();
    $output->writeln("OK Saving environment $name");

    // Run the build hooks
    chdir($environmentFactory->getSourcePath());
    $process = new Process($environmentFactory->config['hooks']['build']);
    $process->run(function ($type, $buffer) {
      if (Process::ERR === $type) {
        echo $buffer;
      } else {
        echo $buffer;
      }
    });

    // Assign Servers!
    // for each environment->config->services,
    //    lookup all servers that have the required service available.
    //    ask user which server to use for each service.
    //    save the environment's service stack.

    // Save data

    // Prompt user to run director direct to deploy the services.

  }
}
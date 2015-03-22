<?php

namespace DevShop\Command;

use DevShop\DevShopApplication;
use DevShop\Model\Environment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
// ...

class EnvironmentAddCommand extends Command
{
  public $app;

  function __construct(DevShopApplication $app) {
    parent::__construct();
    $this->app = $app;
  }

  protected function configure()
  {
    $this
      ->setName('environment:add')
      ->setDescription('Adds a new environment.')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $helper = $this->getHelper('question');

    // App
    $helper = $this->getHelper('question');
    $question = new ChoiceQuestion(
      'For which app? ',
        array_keys($this->app->data['apps']),
      0
    );
    $app = $helper->ask($input, $output, $question);

    // Environment Name
    $question = new Question('Environment Name: ', '');
    $name = $helper->ask($input, $output, $question);

    // Server
    $helper = $this->getHelper('question');
    $question = new ChoiceQuestion(
      'Server? ',
      array_keys($this->app->data['servers']),
      0
    );
    $server = $helper->ask($input, $output, $question);

    $environment = new Environment($app, $name, $server);
    $this->app->data['apps'][$app]['environments'][$name] = (array) $environment;

    $output->writeln("OK Saving environment $name");
    $this->app->saveData();
  }
}
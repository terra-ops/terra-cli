<?php

namespace DevShop\Command;

use DevShop\DevShopApplication;
use DevShop\Model\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;


class AppAddCommand extends Command
{
  public $app;

  function __construct(DevShopApplication $app) {
    parent::__construct();
    $this->app = $app;
  }

  protected function configure()
  {
    $this
      ->setName('app:add')
      ->setDescription('Adds a new app.')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $helper = $this->getHelper('question');

    // App Name
    $question = new Question('System name of your project? ', '');
    $name = $helper->ask($input, $output, $question);

    // App Description
    $question = new Question('Description? ', '');
    $description = $helper->ask($input, $output, $question);

    // App Source
    $question = new Question('Source code repository URL? ', '');
    $repo = $helper->ask($input, $output, $question);

    $app = new App($name, $repo, $description);
    $this->app->data['apps'][$name] = (array) $app;

    $output->writeln("OK Saving app $name");

    $this->app->saveData();
  }
}
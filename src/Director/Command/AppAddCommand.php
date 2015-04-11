<?php

namespace Director\Command;

use Director\DirectorApplication;
use Director\Model\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;


class AppAddCommand extends Command
{
  public $director;

  function __construct(DirectorApplication $director) {
    parent::__construct();
    $this->director = $director;
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
    $this->director->config['apps'][$name] = (array) $app;

    $output->writeln("OK Saving app $name");

    $this->director->saveData();

    // Confirmation
    $question = new ConfirmationQuestion("Create an environment? ", false);
    if (!$helper->ask($input, $output, $question)) {
      return;
    }

    // Run environment:add command.
    $command = $this->getApplication()->find('environment:add');

    $arguments = array(
//      'command' => 'demo:greet',
      'app' => $name,
    );

    $input = new ArrayInput($arguments);
    $command->run($input, $output);

  }
}
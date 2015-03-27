<?php

namespace Director\Command;

use Director\DirectorApplication;
use Director\Model\App;
use Director\Service\AppService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;


class AppInitCommand extends Command
{
  /**
   * @var \Director\DirectorApplication
   * The director application.
   */
  public $director;

  function __construct(DirectorApplication $director) {
    parent::__construct();
    $this->director = $director;
  }

  protected function configure()
  {
    $this
      ->setName('app:init')
      ->setDescription('Initiate an instance of your app.')
      ->addArgument(
        'name',
        InputArgument::REQUIRED,
        'The app you would like to initiate.'
      )
      ->addArgument(
        'path',
        InputArgument::OPTIONAL,
        'The path to clone the source code of your app.'
      )
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // Get our App
    $app = $this->director->apps[$input->getArgument('name')];

    // Determine what path to init in.
    // This command acts like git clone.  $path defaults to $name.
    $path = $input->getArgument('path');
    if (empty($path)) {
      $path = $input->getArgument('name');
    }

    $full_path = getcwd() . '/' . $path;

    // Confirmation
    $helper = $this->getHelper('question');

    $question = new ConfirmationQuestion("Clone {$app->app->source_url} to {$full_path}? ", false);
    if (!$helper->ask($input, $output, $question)) {
      return;
    }

    // Initiate the app.
    $app->init($full_path);

  }
}
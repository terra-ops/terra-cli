<?php

namespace Director\Command;

use Director\DirectorApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use TQ\Git\Repository\Repository;

class EnvironmentStatusCommand extends Command
{
  public $director;

  function __construct(DirectorApplication $director) {
    parent::__construct();
    $this->director = $director;
  }

  protected function configure()
  {
    $this
      ->setName('environment:status')
      ->setDescription('Display the current status of an environment.')
      ->addArgument(
        'app',
        InputArgument::REQUIRED,
        'The app to lookup.'
      )
      ->addArgument(
        'environment',
        InputArgument::REQUIRED,
        'The environment to lookup.'
      )
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $app = $this->director->getApp($input->getArgument('app'));

    $environment = $app->getEnvironment($input->getArgument('environment'));

    $output->writeln($environment->getRepo()->getCurrentBranch());
  }
}
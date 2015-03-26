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


class AppInitCommand extends Command
{
  public $app;

  function __construct(DevShopApplication $app) {
    parent::__construct();
    $this->app = $app;
  }

  protected function configure()
  {
    $this
      ->setName('app:init')
      ->setDescription('Initiate an instance of your app.')
      ->addArgument(
        'name',
        InputArgument::OPTIONAL,
        'Which app would you like to initiate? '
      )
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $app = $input->getArgument('name');
    $output->writeLn("Loading $app...");
  }
}
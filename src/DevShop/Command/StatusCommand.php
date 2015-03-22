<?php

namespace DevShop\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends Command
{
  protected function configure()
  {
    $this
      ->setName('status')
      ->setDescription('Display the current status.')
      ->addArgument(
        'server',
        InputArgument::OPTIONAL,
        'Which server?'
      )
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $name = $input->getArgument('server');
    if (!$name) {
      $name = 'localhost';
    }
    $output->writeln("Hello $name...");
  }
}
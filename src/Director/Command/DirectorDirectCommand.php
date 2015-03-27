<?php

namespace Director\Command;

use Director\DirectorApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;


class DirectorDirectCommand extends Command {
  public $director;

  function __construct(DirectorApplication $director) {
    parent::__construct();
    $this->director = $director;
  }

  protected function configure() {
    $this
      ->setName('direct')
      ->setDescription('Runs ansible on our entire inventory.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {

    $cmd = 'ansible-playbook .playbook.yml -i .inventory';
    system($cmd);

  }
}
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

use Symfony\Component\Process\Process;

class DirectorDirectCommand extends Command {
  public $director;

  function __construct(DirectorApplication $director) {
    parent::__construct();
    $this->director = $director;
  }

  protected function configure() {
    $this
      ->setName('direct')
      ->setDescription('Runs ansible on our entire inventory.')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {

    $playbook = $this->director->configPath . '/playbook.yml';
    $inventory = $this->director->configPath . '/inventory';

    chdir($this->director->configPath);
    $cmd = " ANSIBLE_FORCE_COLOR=true ansible-playbook $playbook -i $inventory";

    // If localhost is our only server, run locally.
    if (count($this->director->servers) == 1 && $this->director->servers['localhost']) {
      $cmd .= ' --connection=local --sudo --ask-sudo-pass';
    }
    echo "\n RUNNING $cmd \n";
    $process = new Process($cmd);
    $process->start();
    while ($process->isRunning()) {
      echo $process->getIncrementalOutput();
    }
  }
}
<?php

namespace Director\Command;

use Director\DirectorApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

use TQ\Git\Repository\Repository;
use GitWrapper\GitWrapper;
use GitWrapper\GitWorkingCopy;

class EnvironmentDeployCommand extends Command
{
  public $director;

  function __construct(DirectorApplication $director) {
    parent::__construct();
    $this->director = $director;
  }

  protected function configure()
  {
    $this
      ->setName('environment:deploy')
      ->setDescription('Checkout a new git ref for an environment and run deploy hooks.')
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
      ->addArgument(
        'git_ref',
        InputArgument::REQUIRED,
        'The git ref to checkout.'
      )
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $app = $this->director->getApp($input->getArgument('app'));
    $environment = $app->getEnvironment($input->getArgument('environment'));
    $git_ref = $input->getArgument('git_ref');

    // Checkout the branch
    $wrapper = new GitWrapper();
    $git = new GitWorkingCopy($wrapper, $environment->getSourcePath());
    $git->checkout($git_ref);


    // Run the deploy hooks
    chdir($environment->getSourcePath());
    $process = new Process($environment->config['hooks']['deploy']);
    $process->run(function ($type, $buffer) {
      if (Process::ERR === $type) {
        echo 'ERR > '.$buffer;
      } else {
        echo 'OK  > '.$buffer;
      }
    });

    // Save new branch to yml
    $this->director->config['apps'][$input->getArgument('app')]['environments'][$input->getArgument('environment')]['git_ref'] =
      $environment->getRepo()->getCurrentBranch();
    $this->director->saveData();

    $output->writeln("Saved environment details.");
  }
}
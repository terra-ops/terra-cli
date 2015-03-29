<?php

namespace Director\Command;

use Director\DirectorApplication;
use Director\Model\Environment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;

class ServerRoleCommand extends Command
{
  public $director;

  function __construct(DirectorApplication $director) {
    parent::__construct();
    $this->director = $director;
  }

  protected function configure()
  {
    $this
      ->setName('server:role')
      ->setDescription('Add or remove roles from servers.')
      ->addArgument(
        'server',
        InputArgument::REQUIRED,
        'The server to add the role to.'
      )
      ->addArgument(
        'service',
        InputArgument::REQUIRED,
        'The service to add to the server.'
      )
      ->addOption(
        'remove',
        null,
        InputOption::VALUE_OPTIONAL,
        'If set, the role will be removed.'
      )
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $server_name = $input->getArgument('server');
    $service_name = $input->getArgument('service');

    $server = $this->director->getServer($server_name);
    $service = $this->director->getService($service_name);

    // Verify server and role is available.
    if (!$server) {
      throw new \Exception("Server '{$server_name}' not found");
    }
    if (!$service) {
      throw new \Exception("Service '{$service_name}' not found");
    }

    $this->director->config['servers'][$server_name]['services'][] = $service_name;
    $this->director->saveData();
  }
}
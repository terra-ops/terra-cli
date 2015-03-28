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
        'role',
        InputArgument::REQUIRED,
        'The role to add to the server.'
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
    $role_name = $input->getArgument('role');

    $server = $this->director->getServer($server_name);
    $role = $this->director->getRole($role_name);

    // Verify server and role is available.
    if (!$server) {
      throw new \Exception("Server '{$server_name}' not found");
    }
    if (!$role) {
      throw new \Exception("Role '{$role_name}' not found");
    }
  }
}
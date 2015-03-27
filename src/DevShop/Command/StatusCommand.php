<?php

namespace DevShop\Command;

use DevShop\DevShopApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends Command
{
  public $app;

  function __construct(DevShopApplication $app) {
    parent::__construct();
    $this->app = $app;
  }

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
    $output->writeln("Hello World!");
    $output->writeln("Server: " . $this->app->raw_data['server']);

    // SERVERS table.
    $table = $this->getHelper('table');
    $table->setHeaders(array('SERVERS', 'Provider'));

    $rows = array();
    foreach ($this->app->raw_data['servers'] as $server) {
      $server = (object) $server;
      $row = array(
        $server->hostname,
        $server->provider,
      );
      $rows[] = $row;
    }
    $table->setRows($rows);
    $table->render($output);


    // APPS table.
    $table = $this->getHelper('table');
    $table->setHeaders(array('APPS', 'Description', 'Repo', 'environments'));

    $rows = array();
    foreach ($this->app->raw_data['apps'] as $app) {
      $app = (object) $app;
      $environments_list = !empty($this->raw_data->data['apps'][$app->name]['environments'])?
        implode(', ', array_keys($this->raw_data->data['apps'][$app->name]['environments'])):
        '';
      $row = array(
        $app->name,
        $app->description,
        $app->source_url,
        $environments_list,
      );
      $rows[] = $row;
    }
    $table->setRows($rows);
    $table->render($output);


  }
}
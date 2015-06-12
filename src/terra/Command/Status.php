<?php

namespace terra\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Status extends Command
{
  protected function configure()
  {
    $this
      ->setName('status')
      ->setDescription('Display the current status of the system.')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $output->writeln("Hello Terra!");

    // APPS table.
    $table = $this->getHelper('table');
    $table->setHeaders(array(
      'APPS',
      'Description',
      'Repo',
      'Environments',
    ));

    $rows = array();
    foreach ($this->getApplication()->getTerra()->getConfig()->get('apps') as $app) {

      $row = array(
        $app['name'],
        $app['description'],
        $app['repo'],
        is_array($app['environments'])? implode(', ', array_keys($app['environments'])): 'None',
      );
      $rows[] = $row;
    }
    $table->setRows($rows);
    $table->render($output);
  }
}
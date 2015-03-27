<?php

namespace DevShop\Command;

use DevShop\DevShopApplication;
use DevShop\Model\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
// ...

class ServerAddCommand extends Command
{
  public $app;

  function __construct(DevShopApplication $app) {
    parent::__construct();
    $this->app = $app;
  }

  protected function configure()
  {
    $this
      ->setName('server:add')
      ->setDescription('Adds a new server.')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $helper = $this->getHelper('question');

    // Server Hostname
    $question = new Question('Hostname of your server? It should already resolve to your servers IP: ', '');
    $hostname = $helper->ask($input, $output, $question);

    // Provider
    // @TODO: Make Provider's extensible.
    $helper = $this->getHelper('question');
    $question = new ChoiceQuestion(
      'Server Provider? ',
      array(
        'vagrant',
      ),
      0
    );

    $provider = $helper->ask($input, $output, $question);

    $server = new Server($hostname,  $provider);
    $this->app->config['servers'][$hostname] = (array) $server;

    $output->writeln("OK Saving server $hostname");
    $this->app->saveData();
  }
}
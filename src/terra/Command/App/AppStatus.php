<?php

namespace terra\Command\App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use terra\Factory\EnvironmentFactory;

class AppStatus extends Command
{
  protected function configure()
  {
    $this
      ->setName('app:status')
      ->setDescription('Display the current status of an app and it\'s environments.')
      ->addArgument(
        'app_name',
        InputArgument::OPTIONAL,
        'The name the app to check.'
      )
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $output->writeln("Hello Terra!");

    // If there are no apps, return
    if (count($this->getApplication()->getTerra()->getConfig()->get('apps')) == 0) {
      $output->writeln("<comment>There are no apps!</comment>");
      $output->writeln("Use the command <info>terra app:add</info> to add your first app.");
      return 1;
    }

    $helper = $this->getHelper('question');
    $app_name = $input->getArgument('app_name');

    // If no name specified provide options
    if (empty($app_name)) {
      $question = new ChoiceQuestion(
        'Which app? ',
        array_keys($this->getApplication()->getTerra()->getConfig()->get('apps')),
        NULL
      );
      $app_name = $helper->ask($input, $output, $question);
    }

    $app = $this->getApplication()->getTerra()->getConfig()->get('apps', $app_name);

    if (empty($app)) {
      $output->writeln("<error>No app with that name! </error>");
      return 1;
    }

    // If no environments:
    if (count(($app['environments'])) == 0) {
      $output->writeln("<comment>There are no environments!</comment>");
      $output->writeln("Use the command <info>terra environment:add</info> to add your first environment.");
      return;
    }

    $table = $this->getHelper('table');
    $table->setHeaders(array(
      'Name',
      'Code Path',
      'URL',
      'Version',
    ));

    $rows = array();

    foreach ($app['environments'] as $environment) {
      // @TODO: Detect if URL proxy is online
      $environment_factory = new EnvironmentFactory($environment, $app);
      $environment['url'] .= PHP_EOL .  'http://' . $environment_factory->getUrl();
      $rows[] = $environment;
    }

    $table->setRows($rows);
    $table->render($output);
  }
}
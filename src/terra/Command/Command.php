<?php

namespace terra\Command;
use Symfony\Component\Process\Process;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class Command
 * @package terra\Command
 */
class Command extends \Symfony\Component\Console\Command\Command {

  private $app;

  /**
   * Helper to ask a question only if a default argument is not present.
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   *
   * @param Question $question
   *   A Question object
   *
   * @param $argument_name
   *   Name of the argument or option to default to.
   *
   * @param string $type
   *   Either "argument" (default) or "option"
   *
   * @return mixed
   *   The value derived from either the argument/option or the value.
   */
  public function getAnswer(InputInterface $input, OutputInterface $output, Question $question, $argument_name, $type = 'argument') {
    $helper = $this->getHelper('question');

    if ($type == 'argument') {
      $value = $input->getArgument($argument_name);
    }
    elseif ($type == 'option') {
      $value = $input->getOption($argument_name);
    }

    if (empty($value)){
      $value = $helper->ask($input, $output, $question);
    }

    return $value;
  }
}
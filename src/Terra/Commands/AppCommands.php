<?php

namespace Terra\Commands;

use Robo\Tasks;

class AppCommands extends Tasks {


  /**
   * This is the my:cat command
   *
   * This command will concatenate two parameters. If the --flip flag
   * is provided, then the result is the concatenation of two and one.
   *
   * @command my:cat
   */
  function appHello() {
    $this->say('wtf');
  }
}


$discovery = new \Consolidation\AnnotatedCommand\CommandFileDiscovery();
$discovery->setSearchPattern('*Commands.php');
$commandClasses = $discovery->discover('src/Terra/Commands', "\Terra\Commands\\");
<?php
namespace DevShop;

use Symfony\Component\Console\Application as BaseApplication;
use DevShop\Console\Command\StatusCommand;

class Application extends BaseApplication
{
  const NAME = 'DevShop';
  const VERSION = '2.0';

  public function __construct()
  {
    parent::__construct(static::NAME, static::VERSION);

    // Add our commands to the application.
    $this->add(new StatusCommand());
  }
}
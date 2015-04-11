<?php

namespace Director\Model;

class Environment {
  /**
   * @var string
   * Must be unique within an app.
   */
  public $name;

  /**
   * @var string
   * The app this environment belongs to.
   */
  public $app;

  /**
   * @var string
   * Path to the source code of this environment.
   */
  public $source_path;

  /**
   * @var string
   * The system URL of the environment.
   */
  public $url;

  /**
   * @var string
   * The current branch or tag deployed to the environment
   */
  public $git_ref;

  /**
   * Initiate the project
   */
  public function __construct($name, $app, $source_path, $git_ref = '') {
    $this->name = $name;
    $this->app = $app;
    $this->source_path = $source_path;
    $this->git_ref = $git_ref;
  }
}
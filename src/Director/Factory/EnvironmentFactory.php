<?php
namespace Director\Factory;
use Director\DirectorApplication;
use Director\Model\Environment;
use GitWrapper\GitWrapper;

/**
 * Service for an App.
 */
class EnvironmentFactory {

  public $environment;
  public $app;

  public function __construct(Environment $environment, DirectorApplication $director) {
    $this->environment = $environment;
    $this->app = $director->getApp($environment->app);
  }

  /**
   * Clones the source code for this project.
   */
  public function init($path){
    $wrapper = new GitWrapper();
    $wrapper->streamOutput();
    $wrapper->clone($this->app->source_url, $path);
    chdir($path);
    $wrapper->git('branch');
  }
}
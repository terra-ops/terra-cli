<?php
namespace Director\Factory;
use Director\DirectorApplication;
use Director\Model\Environment;
use GitWrapper\GitWrapper;
use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;
use TQ\Git\Repository\Repository;

/**
 * Service for an App.
 */
class EnvironmentFactory {

  public $environment;
  public $app;
  public $repo;

  public function __construct($environment, DirectorApplication $director) {
    $this->environment = (object) $environment;
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
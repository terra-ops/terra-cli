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
  public $repo;

  public function __construct($environment, DirectorApplication $director) {
    $this->environment = (object) $environment;
  }

  /**
   * Clones the source code for this project.
   */
  public function init($path){
    $wrapper = new GitWrapper();
    $wrapper->streamOutput();
    $wrapper->clone($this->environment->source_url, $path);
    chdir($path);
    $wrapper->git('branch');
  }

  /**
   * Get the path to this environments source code.
   * @return string
   */
  public function getSourcePath() {
    return $this->environment->source_path;
  }
}
<?php
namespace Director\Factory;
use Director\DirectorApplication;
use GitWrapper\GitWrapper;
use TQ\Git\Repository\Repository;

/**
 * Service for an App.
 */
class EnvironmentFactory {

  public $environment;
  public $repo;

  /**
   * @var string
   */
  public $name;

  public function __construct($environment, DirectorApplication $director) {
    $this->environment = (object) $environment;
    $this->director = $director;
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

  /**
   * Get a Repository class for this environment.
   * @return \TQ\Git\Repository\Repository
   */
  public function getRepo() {
    return Repository::open($this->getSourcePath());
  }
}
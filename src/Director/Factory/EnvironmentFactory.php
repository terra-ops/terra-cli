<?php
namespace Director\Factory;
use Director\DirectorApplication;
use Drupal\Core\Render\Element\File;
use GitWrapper\GitWrapper;
use TQ\Git\Repository\Repository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Service for an App.
 */
class EnvironmentFactory {

  public $environment;
  public $repo;
  public $config;

  /**
   * @var string
   */
  public $name;

  public function __construct($environment, DirectorApplication $director) {
    $this->environment = (object) $environment;
    $this->director = $director;

    $this->loadConfig();
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
    $this->loadConfig();
  }

  /**
   * Loads app config from environment source code into $this->config
   */
  private function loadConfig() {
    // Look for .director.yml
    $fs = new FileSystem;
    if ($fs->exists($this->getSourcePath() . '/.director.yml')){
      $this->config = Yaml::parse(file_get_contents($this->getSourcePath() . '/.director.yml'));
    }
    else {
      $this->config = NULL;
    }
  }

  /**
   * Returns the environments config.
   */
  public function getConfig() {
    if (empty($this->config)) {
      $this->loadConfig();
    }
    return $this->config;
  }

  /**
   * Get the path to this environments source code.
   * @return string
   */
  public function getSourcePath() {
    if (isset($this->environment->source_path)) {
      return $this->environment->source_path;
    }
  }

  /**
   * Get a Repository class for this environment.
   * @return \TQ\Git\Repository\Repository
   */
  public function getRepo() {
    return Repository::open($this->getSourcePath());
  }
}
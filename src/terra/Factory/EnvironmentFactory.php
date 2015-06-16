<?php

namespace terra\Factory;

use GitWrapper\GitWrapper;
use GitWrapper\GitWorkingCopy;
use TQ\Git\Repository\Repository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Process\Process;

/**
 * Service for an App.
 */
class EnvironmentFactory {

  public $environment;
  public $repo;
  public $config;

  /**
   * The name of the app for this environment.
   * @var string
   */
  public $app;

  /**
   * The name of this environment.
   * @var string
   */
  public $name;

  /**
   * @param $environment
   * @param $app
   */
  public function __construct($environment,$app) {
    $this->environment = (object) $environment;
    $this->app = (object) $app;
    $this->name = $this->environment->name;

//    $this->loadConfig();
  }

  /**
   * Clones the source code for this project.
   */
  public function init($path = NULL){
    $path = is_null($path)? $this->environment->path: $path;

    try {
      $wrapper = new GitWrapper();
      $wrapper->streamOutput();
      $wrapper->clone($this->app->repo, $path);
    }
    catch (\GitWrapper\GitException $e) {
      return FALSE;
    }

    chdir($path);
    $wrapper->git('branch');
    $wrapper->git('status');
    $this->loadConfig();

    // Run the build hooks
    if (!empty($this->config['hooks']['build'])) {
      chdir($this->getSourcePath());
      $process = new Process($this->config['hooks']['build']);
      $process->run(function ($type, $buffer) {
        if (Process::ERR === $type) {
          echo $buffer;
        } else {
          echo $buffer;
        }
      });
    }

    // Create the app/environment folder
    $fs = new FileSystem;
    try {
      $fs->mkdir($this->getDockerComposePath());
    }
    catch (IOExceptionInterface $e) {
      return FALSE;
    }

    // Create the environments docker-compose file.
    $dumper = new Dumper();
    try {
      $fs->dumpFile($this->getDockerComposePath() . '/docker-compose.yml', $dumper->dump($this->getDockerComposeArray(), 10));
      return TRUE;
    } catch (IOExceptionInterface $e) {
      return FALSE;
    }

    return TRUE;
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
   * Reloads config from .director.yml file.
   */
  public function reloadConfig() {
    $this->loadConfig();
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
    if (isset($this->environment->path)) {
      return $this->environment->path;
    }
  }

  /**
   * Get a Repository class for this environment.
   * @return \TQ\Git\Repository\Repository
   */
  public function getRepo() {
    return Repository::open($this->getSourcePath());
  }

  /**
   * Deploy a version to an environment.
   *
   * @param $version
   *   A git branch, tag, or sha.
   */
  public function deploy($version) {

    // Checkout the branch
    $wrapper = new GitWrapper();
    $wrapper->streamOutput();
    $git = new GitWorkingCopy($wrapper, $this->getSourcePath());
    $git->checkout($version);
    $git->pull();

    // Reload config so any changes get picked up.
    $this->reloadConfig();

    // Run the deploy hooks
    chdir($this->getSourcePath());
    $process = new Process($this->config['hooks']['deploy']);
    $process->run(function ($type, $buffer) {
      if (Process::ERR === $type) {
        // Error
        echo $buffer;
      } else {
        // OK
        echo $buffer;
      }
    });

    // Save new branch to yml
    $this->director->config['apps'][$this->app]['environments'][$this->name]['git_ref'] =
      $this->getRepo()->getCurrentBranch();
    $this->director->saveData();


  }

  public function getDockerComposePath() {
    return getenv("HOME") . '/.terra/environments/' . $this->app->name . '/' . $this->app->name . '-' . $this->environment->name;
  }

  public function getDockerComposeArray() {
    $path = $this->environment->path;

    $compose = array();
    $compose['app'] = array(
      'image' => 'terra/drupal',
      'tty' => TRUE,
      'stdin_open' => TRUE,
      'links' => array(
        'database',
      ),
      'volumes' => array(
        "$path:/usr/share/nginx/html"
      ),
      'ports' => array(
        ":80/tcp",
      ),
    );
    $compose['database'] = array(
      'image' => 'mariadb',
      'tty' => TRUE,
      'stdin_open' => TRUE,
      'environment' => array(
        'MYSQL_ROOT_PASSWORD' => 'RANDOMIZEPLEASE',
        'MYSQL_DATABASE' => 'drupal',
        'MYSQL_USER' => 'drupal',
        'MYSQL_PASSWORD' => 'drupal',
      ),
    );
    $compose['drush'] = array(
      'image' => 'terra/drush',
      'tty' => TRUE,
      'stdin_open' => TRUE,
      'links' => array(
        'database',
      ),
      'volumes' => array(
        "$path:/usr/share/nginx/html"
      ),
    );

    return $compose;

  }

  public function enable() {

    $process = new Process('docker-compose up -d', $this->getDockerComposePath());
    $process->run();
    if (!$process->isSuccessful()) {
      return FALSE;
    }
    else {
      return $process->getOutput();
    }
  }

  public function getPort() {

    $process = new Process('docker-compose port app 80', $this->getDockerComposePath());
    $process->run();
    if (!$process->isSuccessful()) {
      return FALSE;
    }
    else {
      return trim($process->getOutput());
    }
  }
}
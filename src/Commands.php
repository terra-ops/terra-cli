<?php

namespace Terra;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class Commands extends \Robo\Tasks {
  
  /**
   * Currently active app.
   * @var string
   */
  protected $app;
  
  /**
   * Currently active environment.
   * @var string
   */
  protected $environment;
  
  /**
   * Holds Talos configuration settings.
   *
   * @var Config
   */
  private $config;
  
  function __construct()
  {
    // Load config
    $this->loadConfig();
    
    // Detect currently active app.
    $this->determineActiveApp();
  }
  
  
  /**
   * Loads the app and environment from the current directory.
   */
  private function determineActiveApp() {
    $cwd = getcwd();
    foreach ($this->config->get('apps') as $app) {
      if (!isset($app['environments'])) {
        $app['environments'] = array();
      }
      foreach ($app['environments'] as $environment) {
        if (strpos($cwd, $environment['path']) === 0) {
          $this->app = $app['name'];
          $this->environment = $environment['name'];
          $this->say("Using environment <comment>{$this->app} {$this->environment}</comment>");
        }
      }
    }
  }
  
  /**
   * Load config from YML or environment variable.
   */
  private function loadConfig()
  {
  
    // Get config from env variables or files.
    if ($config_env = getenv('TERRA')) {
      $config_env = Yaml::parse($config_env);
      $config = new Config($config_env);
    } else {
      $fs = new Filesystem();
    
      $user_config = array();
      $user_config_file = getenv('HOME').'/.terra/terra.yml';
      if ($fs->exists($user_config_file)) {
        $user_config = Yaml::parse($user_config_file);
      }
    
//      $project_config = array();
//      $process = new Process('git rev-parse --show-toplevel');
//      $process->run();
//      if ($process->isSuccessful()) {
//        $project_config_file = trim($process->getOutput()).'/terra.yml';
//        if ($fs->exists($project_config_file)) {
//          $project_config = Yaml::parse($project_config_file);
//        }
//      }
//      $config = new Config($user_config, $project_config);
      
      $config = new Config($user_config);
    }
    $this->config = $config;
  }
  
  public function getConfig()
  {
    return $this->config;
  }
  
  public function getAnswer(&$variable, $question) {
    if (!$variable) {
      $variable = $this->ask($question);
    }
  }
}
<?php
namespace Director;

use Symfony\Component\Console\Application as BaseApplication;

use Director\Command\AppUpdateCommand;
use Director\Command\DirectorDirectCommand;
use Director\Command\ServerAddCommand;
use Director\Command\ServerRoleCommand;
use Director\Command\StatusCommand;
use Director\Command\AppAddCommand;
use Director\Command\AppInitCommand;
use Director\Command\EnvironmentAddCommand;
use Director\Command\RoleAddCommand;

use Director\Model\App;
use Director\Model\Server;
use Director\Model\Service;
use Director\Service\AppService;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\DelegatingLoader;

class DirectorApplication extends BaseApplication
{
  const NAME = 'Director';
  const VERSION = '0.1';

  /**
   * @var array
   * The Director registry.
   */
  public $apps = array();
  public $servers = array();
  public $services = array();

  /**
   * @var array
   * Raw data loaded from director.yml
   */
  public $config = array();
  public $configPath = '';

  public function __construct() {
    parent::__construct(static::NAME, static::VERSION);

    // Add available commands to this director.
    $this->add(new DirectorDirectCommand($this));
    $this->add(new StatusCommand($this));
    $this->add(new AppAddCommand($this));
    $this->add(new AppInitCommand($this));
    $this->add(new AppUpdateCommand($this));
    $this->add(new ServerAddCommand($this));
    $this->add(new ServerRoleCommand($this));
    $this->add(new EnvironmentAddCommand($this));
    $this->add(new RoleAddCommand($this));

    // Load Data
    $this->loadData();
  }


  /**
   * Loads data from dataDirectories.
   *
   * If no data is found, write a data file.
   */
  private function loadData() {

    $default_config_path = realpath(__DIR__ . '/../../config');

    // Use $_SERVER['director_config_path'] if present.
    if (isset($GLOBALS['_SERVER']['director_config_path'])) {
      $data_directories = array(
        $GLOBALS['_SERVER']['director_config_path'],
        $default_config_path,
      );
    }
    else {
      $data_directories = array(
        $default_config_path,
      );
    }

    // Attempt to locate data file
    $locator = new FileLocator($data_directories);
    try {
      $this->configPath = dirname(realpath($locator->locate('director.yml')));
    }
    // If there's an exception, show a message.
    catch (\InvalidArgumentException $e) {
      $dirs = implode(', ', $data_directories);
      throw new \Exception("The `director.yml` file was not found in of the directories $dirs.");
    }

    // YML Loader
    $loaderResolver = new LoaderResolver(array(new DirectorConfigLoader($locator)));
    $loader = new DelegatingLoader($loaderResolver);

    // Load core director config.
    $this->config = $loader->load($this->configPath . '/director.yml');
    $this->config['apps'] = $loader->load($this->configPath . '/apps.yml');
    $this->config['servers'] = $loader->load($this->configPath . '/servers.yml');
    $this->config['services'] = $loader->load($this->configPath . '/services.yml');

    // Load each available App
    foreach ($this->config['apps'] as $name => $data) {
      $this->apps[$name] = new AppService($name, $data, $this);
    }

    // Load each available Server
    if (is_array($this->config['servers'])){
      foreach ($this->config['servers'] as $name => $data) {
        $this->servers[$name] = new Server($data['hostname'], $data['provider'], $data['ip_addresses']);
      }
    }
    // Load each available Server
    if (is_array($this->config['services'])){
      foreach ($this->config['services'] as $name => $data) {
        $this->services[$name] = new Service($name, $data['galaxy_role'], $data['description']);
      }
    }
  }

  /**
   * Saves data to file.
   */
  public function saveData() {
    $dumper = new Dumper();
    $output = $dumper->dump($this->config, 4);
    file_put_contents($this->dataPath, $output);
  }

  /**
   * Get an App
   */
  public function getApp($name){
    $data = $this->config['apps'][$name];
    return new App($data['name'], $data['source_url'], $data['description']);
  }

  /**
   * Get a Server.
   */
  public function getServer($name){
    return isset($this->config['servers'][$name])? $this->config['servers'][$name]: NULL;
  }

  /**
   * Get a Role.
   */
  public function getRole($name){
    return isset($this->config['roles'][$name])? $this->config['roles'][$name]: NULL;
  }
}


/**
 * Class DevShopConfigLoader
 * @package DevShop
 */
class DirectorConfigLoader extends FileLoader
{
  public function load($resource, $type = null)
  {
    $configValues = Yaml::parse(file_get_contents($resource));
    return $configValues;
  }

  public function supports($resource, $type = null)
  {
    return is_string($resource) && 'yml' === pathinfo(
      $resource,
      PATHINFO_EXTENSION
    );
  }
}
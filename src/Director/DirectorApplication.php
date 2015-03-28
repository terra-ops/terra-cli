<?php
namespace Director;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Application as BaseApplication;

use Director\Command\AppUpdateCommand;
use Director\Command\DirectorDirectCommand;
use Director\Command\ServerAddCommand;
use Director\Command\ServerRemoveCommand;
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
    $this->add(new ServerRemoveCommand($this));
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

    // Use $_SERVER['director_config_path'] if present.
    $data_directories = array();
    if (isset($GLOBALS['_SERVER']['director_config_path'])) {
      $data_directories[] = $GLOBALS['_SERVER']['director_config_path'];
    }
    // Default to the config folder within this application.
    // @TODO: Not sure how to properly deal with this yet.
    $data_directories[] = __DIR__ . '/../../config';

    // Attempt to locate data file
    $locator = new FileLocator($data_directories);
    try {
      $this->configPath = dirname(realpath($locator->locate('director.yml')));
    }
    // If there's an exception, create a config directory.
    catch (\InvalidArgumentException $e) {
      // Copy default config template to default config directory.
      $default_path = realpath(__DIR__ . '/../../config-default');
      $cmd = "cp -r {$default_path} {$data_directories[0]}";
      system($cmd);

      // Save the configPath.  If it doesn't exist, we have a problem.
      $this->configPath = realpath($data_directories[0]);
      if (empty($this->configPath) || !file_exists($this->configPath . '/director.yml')) {
        throw new \Exception("Unable to find or create a config folder!");
      }
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

    // Save Apps
    file_put_contents($this->configPath . '/apps.yml', $dumper->dump($this->config['apps'], 4));

    // Save Servers
    file_put_contents($this->configPath . '/servers.yml', $dumper->dump($this->config['servers'], 4));

    // Save Services
    file_put_contents($this->configPath . '/services.yml', $dumper->dump($this->config['services'], 4));

  }

  /**
   * Get an App
   */
  public function getApp($name){
    return  isset($this->apps['apps'][$name])?
      $this->config['apps'][$name]:
      NULL;
  }

  /**
   * Get a Server.
   */
  public function getServer($name){
    return isset($this->servers[$name])?
      $this->servers[$name]:
      NULL;
  }

  /**
   * Get a Service.
   */
  public function getService($name){
    return isset($this->services[$name])?
      $this->services[$name]:
      NULL;
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
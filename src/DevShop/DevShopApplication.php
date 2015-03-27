<?php
namespace DevShop;

use DevShop\Command\AppUpdateCommand;
use DevShop\Command\ServerAddCommand;
use Symfony\Component\Console\Application as BaseApplication;
use DevShop\Command\StatusCommand;
use DevShop\Command\AppAddCommand;
use DevShop\Command\AppInitCommand;
use DevShop\Command\EnvironmentAddCommand;
use DevShop\Model\App;
use DevShop\Service\AppService;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\DelegatingLoader;

class DevShopApplication extends BaseApplication
{
  const NAME = 'DevShop';
  const VERSION = '2.0';

  /**
   * @var array
   * An array of AppService for each App that is tracked.
   */
  public $apps = array();

  /**
   * @var array
   * Raw data loaded from devshop.yml
   */
  public $config = array();
  private $dataPath = '';

  public function __construct() {
    parent::__construct(static::NAME, static::VERSION);

    // Add available commands to this devshop.
    $this->add(new StatusCommand($this));
    $this->add(new AppAddCommand($this));
    $this->add(new AppInitCommand($this));
    $this->add(new AppUpdateCommand($this));
    $this->add(new ServerAddCommand($this));
    $this->add(new EnvironmentAddCommand($this));

    // Load Data
    $this->loadData();
  }


  /**
   * Loads data from dataDirectories.
   *
   * If no data is found, write a data file.
   */
  private function loadData() {
    $data_directories = array(
      $GLOBALS['_SERVER']['HOME'],
      $GLOBALS['_SERVER']['PWD'],
    );

    $locator = new FileLocator($data_directories);

    // Attempt to locate data file
    try {
      $this->dataPath = $locator->locate('.devshop.yml');
    }
    // If there's an exception, write a default config.
    catch (\InvalidArgumentException $e) {
      $path = $GLOBALS['_SERVER']['HOME'] . '/.devshop.yml';
      $data = <<<YML
---
server: localhost
YML;
      file_put_contents($path, $data);
      $this->dataPath = $locator->locate('.devshop.yml');
    }

    $loaderResolver = new LoaderResolver(array(new DevShopConfigLoader($locator)));
    $delegatingLoader = new DelegatingLoader($loaderResolver);

    // Load raw data about this devshop.
    $this->config = $delegatingLoader->load($this->dataPath);

    // Load each available App
    foreach ($this->config['apps'] as $name => $data) {
      $this->apps[$name] = new AppService($name, $data, $this);
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
}


/**
 * Class DevShopConfigLoader
 * @package DevShop
 */
class DevShopConfigLoader extends FileLoader
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
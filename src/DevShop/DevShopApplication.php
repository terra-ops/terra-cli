<?php
namespace DevShop;

use Symfony\Component\Console\Application as BaseApplication;
use DevShop\Command\StatusCommand;
use DevShop\Command\AppAddCommand;

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

  public function __construct() {
    parent::__construct(static::NAME, static::VERSION);

    // Add available commands to this devshop.
    $this->add(new StatusCommand($this));
    $this->add(new AppAddCommand($this));

    // Load Data
    $this->loadData();
  }

  public $data = array();
  private $dataPath = '';

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
    $this->data = $delegatingLoader->load($this->dataPath);
  }

  /**
   * Saves data to file.
   */
  public function saveData() {
    $dumper = new Dumper();
    $output = $dumper->dump($this->data, 4);
    file_put_contents($this->dataPath, $output);
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
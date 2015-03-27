<?php
namespace DevShop\Service;
use DevShop\DevShopApplication;
use GitWrapper\GitWrapper;
/**
 * Service for an App.
 */
class AppService {

  public $name;
  public $app;
  public $devshop;

  public function __construct($name, $data, DevShopApplication $devshop) {
    $this->name = $name;
    $this->devshop = $devshop;
    $this->app = (object) $data;
  }

  /**
   * Clones the source code for this project.
   */
  public function init($path){

    $wrapper = new GitWrapper();
    $wrapper->streamOutput();
    $working_copy = $wrapper->clone($this->app->source_url, $path);

    chdir($path);
    $wrapper->git('status');
  }
}
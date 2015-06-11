<?php

namespace terra;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;


/**
 * Class Config.
 *
 * @package terra
 */
class Config implements ConfigurationInterface {

  /**
   * Configuration values array.
   *
   * @var array
   */
  private $config = array();

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    try {
      $processor = new Processor();
      $configs = func_get_args();
      $this->config = $processor->processConfiguration($this, $configs);
    }
    catch (\Exception $e) {
      throw new \Exception("There is an error with your configuration: " . $e->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigTreeBuilder() {
    $tree_builder = new TreeBuilder();
    $root_node = $tree_builder->root('project');
    $root_node
      ->children()
      ->scalarNode('git')
      ->defaultValue('/usr/bin/git')
      ->end();
    return $tree_builder;
  }

  /**
   * Check if config param is present.
   *
   * @param string $key
   *   Key of the param to check.
   *
   * @return bool
   *   TRUE if key exists.
   */
  public function has($key) {
    return array_key_exists($key, $this->config);
  }

  /**
   * Get a config param value.
   *
   * @param string $key
   *   Key of the param to get.
   *
   * @return mixed|null
   *   Value of the config param, or NULL if not present.
   */
  public function get($key) {
    return $this->has($key) ? $this->config[$key] : NULL;
  }

  /**
   * Set a config param value.
   *
   * @param string $key
   *   Key of the param to get.
   *
   * @param mixed $val
   *   Value of the param to set.
   *
   * @return bool
   */
  public function set($key, $val) {
    return $this->config[$key] = $val;
  }

  /**
   * Get all config values.
   *
   * @return array
   *   All config galues.
   */
  public function all() {
    return $this->config;
  }

}
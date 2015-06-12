<?php

namespace terra;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Dumper;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

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
        ->end()
        ->arrayNode('apps')
          ->prototype('array')
          ->children()
            ->scalarNode('name')
            ->isRequired(true)
            ->end()
            ->scalarNode('description')
            ->isRequired(false)
            ->end()
            ->scalarNode('repo')
            ->isRequired(true)
            ->end()
            ->arrayNode('environments')
            ->prototype('array')
            ->isRequired(false)
            ->children()
              ->scalarNode('name')
              ->isRequired(true)
              ->end()
              ->scalarNode('path')
              ->isRequired(true)
              ->end()
              ->scalarNode('version')
              ->isRequired(true)
              ->end()
              ->scalarNode('url')
              ->isRequired(false)
              ->end()
    ;
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
  public function get($key, $name = NULL) {
    if ($name) {
      return array_key_exists($name, $this->config[$key]) ? $this->config[$key][$name] : NULL;
    }
    else {
      return $this->has($key) ? $this->config[$key] : NULL;
    }
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

  /**
   * Add a config param value to a config array.
   *
   * @param string $key
   *   Key of the group to set to.
   *
   * @param string|array $names
   *   Name of the new object to set.
   *
   * @param mixed $val
   *   Value of the new object to set.
   *
   * @return bool
   */
  public function add($key, $names, $val) {

    if (is_array($names)) {
      $array_piece = &$this->config[$key];
      foreach ($names as $name_key) {
        $array_piece = &$array_piece[$name_key];
      }
      return $array_piece = $val;
    }
    else {
      return $this->config[$key][$names] = $val;
    }
  }

  /**
   * Remove a config param from a config array.
   *
   * @param $key
   * @param $name
   * @return bool
   */
  public function remove($key, $name) {
    if (isset($this->config[$key][$name])) {
      unset($this->config[$key][$name]);
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  public function save() {

    // Create config folder if it does not exist.
    $fs = new Filesystem();
    $dumper = new Dumper();


    if (!$fs->exists(getenv("HOME") . '/.terra')) {
      try {
        $fs->mkdir(getenv("HOME") . '/.terra/apps');
      } catch (IOExceptionInterface $e) {
        return FALSE;
      }
    }

    try {
      $fs->dumpFile(getenv("HOME") . '/.terra/terra', $dumper->dump($this->config, 10));
      return TRUE;
    } catch (IOExceptionInterface $e) {
      return FALSE;
    }
  }
}
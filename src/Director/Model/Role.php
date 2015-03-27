<?php
namespace Director\Model;

/**
 * A server role.
 */
class Role {

  /**
   * @var string
   * The role's machine name.  Must be unique.
   */
  public $name;

  /**
   * @var string
   * A description of this role
   */
  public $description;

  /**
   * Initiate the Role object.
   */
  public function __construct($name, $description = NULL) {
    $this->name = $name;
    $this->description = $description;
  }
}
<?php

namespace Compredict\API\Users;

use \Exception as Exception;

/**
 * Singleton patter in php
 **/
trait SingletonTrait {
 protected static $inst = null;

  /**
   * call this method to get instance
   **/
  public static function getInstance($adminKey=null){
    if (static::$inst === null)
      static::$inst = new static($adminKey);
    return static::$inst;
  }

    /**
     * Make clone magic method protected, so nobody can clone instance.
     */
    protected function __clone() {}

    /**
     * Make sleep magic method protected, so nobody can serialize instance.
     */
    protected function __sleep() {}

    /**
     * Make wakeup magic method protected, so nobody can unserialize instance.
     */
    protected function __wakeup() {}
  }
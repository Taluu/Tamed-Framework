<?php
/**
 * Starter
 *
 * This is a part of the Entry Point ; this file is called once (and only once !)
 * It starts all the necessary things to make it all work.
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @package twk
 * @copyright Copyleft (c) 2010+, Baptiste Clavié, Talus' Works
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 * @link http://www.talus-works.net Talus' Works
 * @license http://creativecommons.org/licenses/by-sa/3.0/ CC-BY-SA 3.0+
 * @version $Id$
 */

if (!defined('SAFE')) exit;
if (defined('ALREADY_STARTED')) exit;
define('ALREADY_STARTED', true);

/**
 * @todo Make a better handling of this loader
 */
spl_autoload_register(function ($class) {
  $file = explode('\\', $class);  array_unshift($file, __DIR__);
  $file = implode(DIRECTORY_SEPARATOR, $file) . '.' . PHP_EXT;

  if (!is_file($file)) {
    return false;
  }

  require_once $file;
  return true;
 });
 
/**
 * Contains all the global objects needed for the project.
 */
abstract class Obj {
  /**
   * @var \Config
   */
  static public $config = null;

  /**
   * @var \ORM
   * @todo Develop the ORM...
   */
  static public $orm = null;

  /**
   * @var \Controller\Front
   */
  static public $controller = null;

  /**
   * @var \Router
   */
  static public $router = null;
}

abstract class Sys {}

Obj::$config = new \Config;
Obj::$router = new \Router;

// @todo Load all the routes


/*
 * EOF
 */

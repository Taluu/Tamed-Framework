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
 * @package Talus' Works
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
  $file = explode('\\', $class);
  $file = implode(DIRECTORY_SEPARATOR, $file) . '.' . PHP_EXT;

  if (is_file(__DIR__ . DIRECTORY_SEPARATOR . $file)) {
    require __DIR__ . DIRECTORY_SEPARATOR . $file;
  } elseif (is_file(__DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . $file)) {
    require __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . $file;
  } else {
    return false;
  }

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
   * @var \Router
   */
  static public $router = null;
}

abstract class Sys {}

Obj::$router = new \Router;
Obj::$config = new \Config;

/*
 * EOF
 */

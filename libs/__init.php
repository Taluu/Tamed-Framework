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
 * @license http://www.gnu.org/licenses/lgpl.html LGNU Public License 2+
 */

if (!defined('SAFE')) exit;
if (defined('ALREADY_STARTED')) exit;
define('ALREADY_STARTED', true);

spl_autoload_register(function ($class) {
  $file = explode('\\', $class); array_unshift($file, __DIR__);
  $file = implode(DIRECTORY_SEPARATOR, $file) . '.' . PHP_EXT;

  if (!is_file($file)) {
    return false;
  }

  require $file;
  return true;
 });
 
/**
 * Contains all the global objects needed for the project.
 */
abstract class Obj {
  static public $config = null;

  /**
   * @var Controller
   */
  static public $controller = null;

  /**
   * @var Talus_TPL\Main
   */
  static public $tpl = null;

  /**
   * @var Router
   */
  static public $router = null;

  /**
   * @var Http\Request
   */
  static public $httpRequest = null;

  /**
   * @var Http\Response
   */
  static public $httpResponse = null;
}

abstract class Sys {}

Obj::$tpl = new Talus_TPL\Main(__DIR__ . '/../views/templates/', __DIR__ . '/../views/cache/', array(), false);
Obj::$router = new Router;
Obj::$httpRequest = new Http\Request;
Obj::$httpResponse = new Http\Response;

/*
 * EOF
 */

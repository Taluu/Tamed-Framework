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

$start = microtime(true);

spl_autoload_register(function ($_class) {
  static $loaded = array();

  if (isset($loaded[$_class])) {
    return $loaded[$_class];
  }

  $file = explode('\\', $_class);  array_unshift($file, __DIR__);
  $file = implode(DIRECTORY_SEPARATOR, $file) . '.' . PHP_EXT;

  if (!is_file($file)) {
    $loaded[$_class] = false;
  } else {
    require $file;
    $loaded[$_class] = true;
  }

  return $loaded[$_class];
 });

Debug::$start = $start;

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
  switch ($errno) {
    case E_RECOVERABLE_ERROR:
    case E_COMPILE_ERROR:
    case E_CORE_ERROR:
    case E_USER_ERROR:
    case E_ERROR:
    case E_PARSE:
      $err = \Debug::LEVEL_FATAL;
      break;

    case E_COMPILE_WARNING:
    case E_CORE_WARNING:
    case E_USER_WARNING:
    case E_WARNING:
      $err = \Debug::LEVEL_WARNING;
      break;

    case E_USER_DEPRECATED:
    case E_USER_NOTICE:
    case E_DEPRECATED:
    case E_NOTICE:
    case E_STRICT:
    default:
      $err = \Debug::LEVEL_INFO;
      break;
  }

  $e = new ErrorException($errstr, 0, $errno, $errfile, $errline);

  \Debug::log('An error occurred (' . $e->__toString() . ')', $err);
  throw $e;
 });


/**
 * Contains all the global objects needed for the project.
 */
abstract class Obj {
  /**
   * @var \Configuration\Loader
   */
  static public $config = null;

  /**
   * @var \Controller\Front
   */
  static public $controller = null;

  /**
   * @var \Routing\Router
   */
  static public $router = null;
}

abstract class Sys {}

Obj::$config = new \Configuration\Loader(__DIR__ . '/../conf/');
Obj::$router = new \Routing\Router;

$routes = Obj::$config->get('routes', function ($v) {
  return new Routing\Route($v['controller'], $v['action'], $v['pattern']);
 });

foreach ($routes as $name => &$route) {
  Debug::info('Adding route %s', (string) $name);
  Obj::$router->addRoute($name, $route);
}


/*
 * EOF
 */

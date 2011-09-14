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
 * @license http://www.opensource.org/licenses/BSD-3-Clause Modified BSD License
 * @version $Id$
 */

namespace Tamed;

use Tamed\Configuration\Loader as Configuration_Loader;
use Tamed\Routing\Router;
use Tamed\Routing\Route;
use Tamed\Debug;

if (!defined('SAFE')) exit;
if (defined('ALREADY_STARTED')) exit;
define('ALREADY_STARTED', true);

$start = microtime(true);

/**
 * @todo Render this autoloader compliant with PSR-0
 *
 * @link http://groups.google.com/group/php-standards/web/psr-0-final-proposal
 */
spl_autoload_register(function ($_class) {
  static $loaded = array();

  if (isset($loaded[$_class])) {
    return $loaded[$_class];
  }

  $file = explode('\\', $_class);

  if ($file[0] !== __NAMESPACE__) {
    return false;
  }


  array_shift($file); array_unshift($file, __DIR__, '..', 'libs');
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
      $err = Debug::LEVEL_FATAL;
      break;

    case E_COMPILE_WARNING:
    case E_CORE_WARNING:
    case E_USER_WARNING:
    case E_WARNING:
      $err = Debug::LEVEL_WARNING;
      break;

    case E_USER_DEPRECATED:
    case E_USER_NOTICE:
    case E_DEPRECATED:
    case E_NOTICE:
    case E_STRICT:
    default:
      $err = Debug::LEVEL_INFO;
      break;
  }

  $e = new \ErrorException($errstr, 0, $errno, $errfile, $errline);

  Debug::log('An error occurred (' . $e->__toString() . ')', $err);
  throw $e;
 });

Obj::$config = new Configuration_Loader(__DIR__ . '/../conf/');
Obj::$router = new Router;

$routes = Obj::$config->get('routes', function (&$v, $k) {
  $v = new Route($v['app'], $v['action'], $v['pattern']);
 });

foreach ($routes as $name => &$route) {
  Debug::info('Adding route %s', (string) $name);
  Obj::$router->addRoute($name, $route);
}


/*
 * EOF
 */

<?php
/**
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @copyright ©Talus, Talus' Works 2010+
 * @link http://www.talus-works.net Talus' Works
 * @license http://www.opensource.org/licenses/BSD-3-Clause Modified BSD License
 * @version $Id$
 */

namespace Tamed;

if (!defined('SAFE')) exit;

/**
 * Definition of the Debug Class
 *
 * Handles every debug information sent from the application
 *
 * @package twk
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 *
 */
class Debug {
  const
    LEVEL_INFO = 1,
    LEVEL_WARNING = 2,
    LEVEL_FATAL = 3;

  public static
    $start = 0,
    $stack = array();

  /**
   * Write a message into the access log
   *
   * @param string $msg to be written
   * @param int $level error's level
   * @return Returns TRUE on success or FALSE on failure.
   */
  public static function log($msg, $level = self::LEVEL_INFO) {
    static $lvlStr = array(
        self::LEVEL_FATAL => 'Fatal Error',
        self::LEVEL_WARNING => 'Warning',
        self::LEVEL_INFO => 'Information'
      );

    $command = 'null';

    if (Obj::$router->hasStarted()) {
      $command = Obj::$router->get('command') ?: '/';
      $command .= ' controller:' . Obj::$router->get('controller');
      $command .= ' action:' . Obj::$router->get('action');
    }

    // -- $db[0] is this function... And we have no interest in it, do we ?
    foreach (debug_backtrace(true) as $debug) {
      if (isset($debug['class']) && $debug['class'] != __CLASS__) {
        break;
      }
    }

    $date = new \DateTime('now', new \DateTimeZone('UTC'));
    $obj = isset($debug['type']) ? $debug['class'] . $debug['type'] : '';
    $chrono = microtime(true) - self::$start;

    $log = sprintf('%7$s {UTC} - %1$s : %2$s - %3$s() (line %4$d) - command %5$s [[ %6$fs ]]',
                   $lvlStr[$level], $msg, $obj . $debug['function'],
                   $debug['line'], $command, $chrono, $date->format('D, d M Y H:i:s'));

    self::$stack[] = $log;
    return error_log($log . "\n", 3, __DIR__ . '/../logs/access.log');
  }

  /**
   * Shortcut for Debug::log($msg, self::LEVEL_INFO)
   *
   * @param string $msg message to be written
   * @return Returns TRUE on success or FALSE on failure.
   * @see Debug::log();
   */
  public static function info($msg) {
    if (func_num_args() > 1) {
      $args = func_get_args();
      $msg = array_shift($args);
      $msg = vsprintf($msg, $args);
    }

    return self::log($msg, self::LEVEL_INFO);
  }

  /**
   * Shortcut for Debug::log($msg, self::LEVEL_WARNING)
   *
   * @param string $msg message to be written
   * @return Returns TRUE on success or FALSE on failure.
   * @see Debug::log();
   */
  public static function warning($msg) {
    if (func_num_args() > 1) {
      $args = func_get_args();
      $msg = array_shift($args);
      $msg = vsprintf($msg, $args);
    }


    return self::log($msg, self::LEVEL_WARNING);
  }

  /**
   * Shortcut for Debug::log($msg, self::LEVEL_FATAL)
   *
   * @param string $msg message to be written
   * @return Returns TRUE on success or FALSE on failure.
   * @see Debug::log();
   */
  public static function fatal($msg) {
    if (func_num_args() > 1) {
      $args = func_get_args();
      $msg = array_shift($args);
      $msg = vsprintf($msg, $args);
    }


    return self::log($msg, self::LEVEL_FATAL);
  }
}

/*
 * EOF
 */

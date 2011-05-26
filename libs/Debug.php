<?php
/**
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @copyright ©Talus, Talus' Works 2010+
 * @link http://www.talus-works.net Talus' Works
 * @license http://creativecommons.org/licenses/by-sa/3.0/ CC-BY-SA 3.0+
 * @version $Id$
 */

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

  public static function log($msg, $level = self::LEVEL_INFO) {
    static $lvlStr = array(
        self::LEVEL_FATAL => 'Fatal Error',
        self::LEVEL_WARNING => 'Warning',
        self::LEVEL_INFO => 'Information'
      );

    $command = \Obj::$router->hasStarted() ? \Obj::$router->get('command') : '(not set yet)';

    // -- $db[0] is this function... And we have no interest in it, do we ?
    foreach (debug_backtrace() as $debug) {
      if ($debug['class'] != 'Debug') {
        break;
      }
    }

    $date = new DateTime('now', new DateTimeZone('UTC'));

    $obj = $debug['type'] != '' ? $debug['class'] . $debug['type'] : '';
    $chrono = microtime(true) - self::$start;

    $log = sprintf('%7$s {UTC} - %1$s : %2$s - %3$s() (line %4$d) - command %5$s [[ %6$fs ]]',
                   $lvlStr[$level], $msg, $obj . $debug['function'],
                   $debug['line'], $command, $chrono, $date->format('D, d M Y H:i:s'));

    self::$stack[] = $log;
    return error_log($log . "\n", 3, __DIR__ . '/../logs/access.log');
  }

  public static function info($msg) {
    return self::log($msg, self::LEVEL_INFO);
  }

  public static function warning($msg) {
    return self::log($msg, self::LEVEL_WARNING);
  }

  public static function fatal($msg) {
    return self::log($msg, self::LEVEL_FATAL);
  }
}

/*
 * EOF
 */

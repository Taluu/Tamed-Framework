<?php
/**
 * Definition of the HttpRequest class
 *
 * Handles eveything sent by the client.
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @package Talus' Works
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 * @copyright ©Talus, Talus' Works 2010+
 * @link http://www.talus-works.net Talus' Works
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 3+
 */

namespace Http;

/**
 * @todo Handle sessions ?
 */
class Request {
  const 
      GET = 1,
      POST = 2,
      COOKIE = 4,

      ALL = 7;

  /**
   * Gets a GET parameter
   *
   * @param string $key Key name
   * @return mixed (null if the parameter was not found)
   */
  public function get($key) {
    return $_GET[$key] ?: null;
  }

  /**
   * Gets a POST parameter
   *
   * @param string $key Key name
   * @return mixed (null if the parameter was not found)
   */
  public function post($key) {
    return $_POST[$key] ?: null;
  }

  /**
   * Gets a COOKIE
   *
   * @param string $key Cookie's name
   * @return mixed
   */
  public function cookie($key) {
    return $_COOKIE[$key] ?: null;
  }

  /**
   * Fetch a Param (POST > GET > COOKIE)
   *
   * @param string $key Parameter to retrieve
   * @param integer $filter Which method to use
   * @return mixed
   */
  public function getParam($key, $filter = self::ALL) {
    $methods = array();

    foreach (array('post', 'get', 'cookie') as $method) {
      if ($filter & \constant('self::' . $method)) {
        $methods[] = $method;
      }
    }

    foreach ($methods as &$method) {
      $val = \call_user_func(array($this, $method), $key);
      
      if ($val !== null) {
        return $val;
      }
    }

    return null;
  }
}

/*
 * EOF
 */

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

namespace Http;

/**
 * Definition of the HttpRequest class
 *
 * Handles eveything sent by the client.
 *
 * @package twk.http
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 *
 * @todo Redo this class
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
    return isset($_GET[$key]) ? $_GET[$key] : null;
  }

  /**
   * Gets a POST parameter
   *
   * @param string $key Key name
   * @return mixed (null if the parameter was not found)
   */
  public function post($key) {
    return isset($_POST[$key]) ? $_POST[$key] : null;
  }

  /**
   * Gets a COOKIE
   *
   * @param string $key Cookie's name
   * @return mixed null (null if the cookie was not found)
   */
  public function cookie($key) {
    return isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
  }

  /**
   * Gets a FILE parameter
   *
   * @param string $key File's name
   * @return mixed array if the file is found, null otherwise
   */
  public function file($key) {
    return isset($_FILES[$key]) ? $_FILES[$key] : null;
  }

  /**
   * Fetch a Param (POST > GET > COOKIE)
   *
   * @param string $key Parameter to retrieve
   * @param integer $filter Which method to use
   * @return mixed
   */
  public function getParam($key, $filter = self::ALL) {
    foreach (array('post', 'get', 'cookie') as $method) {
      if ($filter & \constant('self::' . \strtoupper($method))) {
        $val = \call_user_func(array($this, $method), $key);

        if ($val !== null) {
          return $val;
        }
      }
    }

    return null;
  }

  /**
   * Gets the requested url
   *
   * @return array Returns an array with the real requested url, stripped of its
   *               query string and of its root part, and its query string.
   *
   */
  public function requestUri() {
    $requestURI = strstr(trim($_SERVER['REQUEST_URI']), '?', true);
    $requestURI = $requestURI ?: trim($_SERVER['REQUEST_URI']);

    $return = array(
      'URI' => $requestURI,
      'query_string' => $_SERVER['QUERY_STRING']
     );

    if (isset($_SERVER['REDIRECT_URL'])) {
      $return['URI'] = $_SERVER['REDIRECT_URL'];
    }

    // -- stripping the root part
    $phpSelf = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/') + 1);

    if (strpos($return['URI'], $phpSelf) === 0) {
      $return['URI'] = substr($return['URI'], strlen($phpSelf));
    }

    $return['URI'] = preg_replace('`/{2,}`', '/', '/' . $return['URI']);

    return $return;
  }
}

/*
 * EOF
 */

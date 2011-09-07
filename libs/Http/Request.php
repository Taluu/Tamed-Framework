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

  private
    $_requestURI = null,
    $_queryString = null,
    $_root = null;

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
   * @todo if a htaccess (or equivalent) can not be cast, use the query string to
   *       analyse the url (?/part/to/somehting?a=b&c=d)
   */
  public function requestUri() {
    if ($this->_requestURI === null) {
      $requestURI = strstr(trim($_SERVER['REQUEST_URI']), '?', true);
      $requestURI = $requestURI ?: trim($_SERVER['REQUEST_URI']);

      $this->_requestURI = $requestURI;

      if (isset($_SERVER['REDIRECT_URL'])) {
        $this->_requestURI = $_SERVER['REDIRECT_URL'];
      }

      // -- stripping the root part
      if (strpos($this->_requestURI, $this->getRoot()) === 0) {
        $this->_requestURI = substr($this->_requestURI, strlen($this->getRoot()));
      }

      $this->_requestURI = preg_replace('`/{2,}`', '/', '/' . $this->_requestURI);
    }

    return $this->_requestURI;
  }

  /**
   * Gets the query string of the current script
   *
   * @return string the query string (?a=b&c=d)
   * @todo if a htaccess (or equivalent) can not be cast, use the query string to
   *       analyse the url (?/part/to/somehting?a=b&c=d)
   */
  public function getQueryString() {
    if ($this->_queryString === null) {
      $this->_queryString = $_SERVER['QUERY_STRING'];
    }

    return $this->_queryString;
  }

  /**
   * Gets the root of the current script
   *
   * Example : if you're running on / (as it is recommanded...), you'll just have
   * '/', but if you're running on /whatever/, you'll get '/whatever/'.
   *
   * @return string the root part
   */
  public function getRoot() {
    if ($this->_root === null) {
      $this->_root = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/') + 1);
    }

    return $this->_root;
  }
}

/*
 * EOF
 */

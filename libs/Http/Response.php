<?php
/**
 * Definition of the HttpResponse class
 *
 * Handles eveything sent by the server.
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

class Response {
  /**
   * Headers
   */
  const
    CONTINU = 100, SWITCHING_PROTOCOL = 101,
    OK = 200, CREATED = 201, NO_CONTENT = 204, PARTIAL_CONTENT = 206,
    MULTIPLE_CHOICE = 300, MOVED_PERMANENTLY = 301, MOVED_TEMPORARLY = 302, NOT_MODIFIED = 304,
    BAD_REQUEST = 400, AUTHORIZATION_REQUIRED = 401, FORBIDDEN = 403, NOT_FOUND = 404, NOT_ALLOWED = 405,
    SERVER_ERROR = 501, NOT_IMPLEMENTED = 502, SERVICE_UNAVAILABLE = 503, VERSION_NOT_SUPPORTED = 505;

  protected static
    $_status = array(
      self::CONTINU => 'Continue',
      self::SWITCHING_PROTOCOL => 'Switching Protocol',

      self::CREATED => 'Created',
      self::NO_CONTENT => 'No Content',
      self::PARTIAL_CONTENT => 'Partial Content',

      self::MULTIPLE_CHOICE => 'Multiple Choice',
      self::MOVED_PERMANENTLY => 'Moved Permanently',
      self::MOVED_TEMPORARLY => 'Moved Temporarly',
      self::NOT_MODIFIED => 'Not Modified',

      self::BAD_REQUEST => 'Bad Request',
      self::AUTHORIZATION_REQUIRED => 'Authorization Required',
      self::FORBIDDEN => 'Forbidden',
      self::NOT_FOUND => 'Not Found',
      self::NOT_ALLOWED => 'Not Allowed',

      self::SERVER_ERROR => 'Server Error',
      self::NOT_IMPLEMENTED => 'Not Implemented',
      self::SERVICE_UNAVAILABLE => 'Service Unavailable',
      self::VERSION_NOT_SUPPORTED => 'Version Not Supported'
     );

  /**
   * Send a header to the client
   *
   * @param string $header Header's name
   * @param boolean $replace Replace an existing header ?
   * @param integer $code
   */
  public function header($header, $replace = true, $code = self::OK) {
    if (\headers_sent ()) {
      // @todo Manage correctly exceptions
      throw new Exception('The header have already been sent');
    }

    \header($header, $replace, $code);
  }

  /**
   * Throws a redirection
   *
   * @param string $url URL to be redirected
   * @param integer $time The time before being redirected
   * @param integer $code Status code to be sent
   * @throws Exception if the URL is empty
   */
  public function redirect($url, $time = 0, $code = self::OK) {
    if (empty($url)) {
      throw new Exception('Can\'t redirect to an empty url');
    }

    $header = $time !== 0 ? "Refresh: {$time};url={$url}" : "Location: {$url}";

    $this->status($code);
    $this->header($header, true, $code);
  }

  /**
   * Sets the status for the current page
   *
   * @param integer $status Status of the page
   */
  public function status($status) {
    if (!isset(self::$_status[$status])) {
      throw new Exception('Unknown status');
    }

    $header = sprintf('%1$s %2$d %3$s', $_SERVER['SERVER_PROTOCOL'], $status, self::$_status[$status]);
    $this->header($header, true, $status);
  }

  /**
   * Send a cookie to the client
   *
   * @param string $name Name of the cookie
   * @param mixed $val Value of the cookie
   * @param integer $timeout Timeout of the cookie
   * @return bool Success of the operation
   */
  public function cookie($name, $val, $timeout) {
    return \setrawcookie($name, \rawurlencode($str), \time() + $timeout);
  }

  /**
   * Render the current page
   *
   * @todo To develop ?
   */
  public function render() {
    \ob_end_flush();
    exit;
  }

  /**
   * Gets an item sent by the server
   *
   * @param string $key Key of the parameter to be sent
   * @return mixed
   */
  public function server($key) {
    return $_SERVER[\strtolower($key)] ?: null;
  }

  /**
   * Magic method, catching the short cuts methods (like redirectXYZ)
   *
   * @param string $method Method name
   * @param array $args
   * @return mixed
   */
  public function __call($method, array $args) {
    if (substr($method, 0, 8) == 'redirect') {
      $this->redirect($args[0], $args[1] ?: 0, intval(substr($method, 8)));
      return;
    }
  }

}

/*
 * EOF
 */

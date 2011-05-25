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

namespace Http;

/**
 * Definition of the HttpResponse class
 *
 * Handles eveything sent by the server.
 *
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 * @package twk.http
 */
class Response {
  /**
   * Headers
   *
   * @todo check how to write CONTINUE for CONTINU...
   */
  const
    CONTINU = 100, SWITCHING_PROTOCOL = 101,
    OK = 200, CREATED = 201, NO_CONTENT = 204, PARTIAL_CONTENT = 206,
    MULTIPLE_CHOICE = 300, MOVED_PERMANENTLY = 301, MOVED_TEMPORARLY = 302, NOT_MODIFIED = 304,
    BAD_REQUEST = 400, AUTHORIZATION_REQUIRED = 401, FORBIDDEN = 403, NOT_FOUND = 404, NOT_ALLOWED = 405,
    SERVER_ERROR = 501, NOT_IMPLEMENTED = 502, SERVICE_UNAVAILABLE = 503, VERSION_NOT_SUPPORTED = 505;

  private static
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
   * View
   *
   * @var \View\iView
   */
  private $_view = null;

  protected
    $_status = self::OK,
    $_headers = array(),
    $_cookies = array();

  function __construct($_code) {
    $this->_status = $_code;
  }

  /**
   * Send a header to the client
   *
   * @param string $header Header's name
   * @param boolean $replace Replace an existing header ?
   * @param integer $code
   */
  public function header($header, $value = null, $replace = true, $code = self::OK) {
    $this->_headers[$header] = new Header($header, $value, $replace, $code);
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

    if ($time === 0) {
      $header = 'Location';
      $value = $url;
    } else {
      $header = 'Refresh';
      $value = $time . ';url=' . $url;
    }

    $this->status($code);
    $this->header($header, $value, true, $code);
  }

  /**
   * Sets the status header for the current page
   *
   * @param integer $_status Status of the page
   */
  public function status($_status) {
    if (!isset(self::$_status[$_status])) {
      throw new Exception('Unknown status');
    }

    $header = sprintf('%1$s %2$d %3$s', $this->server('server_protocol'), $_status, self::$_status[$_status]);
    $this->header($header, null, true, $_status);
    $this->setStatus($_status);
  }

  /**
   * Sends a cookie to the client
   *
   * @param string $name Name of the cookie
   * @param mixed $val Value of the cookie
   * @param integer $timeout Timeout of the cookie
   * @return void
   */
  public function cookie($name, $val, $timeout) {
    $this->_cookies[$name] = new Cookie($name, $val, $timeout);
  }

  /**
   * Render the current page
   * Sends all the headers (cookie, session, ...), and render the $view file.
   *
   * @param string $view File to be rendered
   * @return void
   * @todo well... todo.
   */
  public function render($view) {
    //\ob_end_flush(); // ?
    $this->_view->render($view);
    exit;
  }

  /**
   * Gets an item sent by the server
   *
   * @param string $key Key of the parameter to be sent
   * @return mixed
   */
  public function server($key) {
    return $_SERVER[\strtoupper($key)] ?: null;
  }

  /**
   * Affects a new view engine (if it is not null).
   *
   * @param \View\iView $_view View engine to affect
   * @return \View\iView View engine affected
   */
  public function view(\View\iView $_view = null) {
    if ($view !== null) {
      $this->_view = $_view;
    }

    return $this->view;
  }

  /**
   * Sends the header to the navigator
   *
   * @return void
   */
  public function sendHeaders() {
    if (\headers_sent ()) {
      // @todo Manage correctly exceptions
      throw new Exception('The header have already been sent');
    }

    foreach ($this->_headers as &$header) {
      $header->send();
    }

    foreach ($this->_cookies as &$cookie) {
      $cookie->send();
    }
  }

  public function getStatus() {
    return $this->_status;
  }

  private function setStatus($_status) {
    if ($this->isInvalid($_status)) {
      throw new Exception('Bad code for response component');
    }

    $this->_status = $_status;
  }

  /**
   * Magic method, catching the short cuts methods (like redirectXYZ)
   *
   * @param string $method Method name
   * @param array $args
   * @return mixed
   */
  public function __call($method, array $args) {
    /*
     * redirectXYZ methods
     *
     * Params : URL, Time to be redirected (0 : instant)
     */
    if (substr($method, 0, 8) == 'redirect') {
      $this->redirect($args[0], $args[1] ?: 0, intval(substr($method, 8)));
      return;
    }

    /*
     * isXYZ methods
     *
     * Params : $_status ; Status to check. If it is not set, use current status
     */
    if (substr($method, 0, 2) == 'is') {
      $code = substr($method, 0, 2);
      $status = $args[0] ?: $this->getStatus();
      $constant = strtoupper($code);

      if (filter_var($code, FILTER_VALIDATE_INT) !== false && isset(self::$_status[$code])) {
        $code = (int) $code;

        if ($this->isInvalid($status)) {
          throw new Exception('Code not valid');
        }

        return $status === $code;
      }

      switch ($code) {
        case 'Invalid':
          return $status < 100 || $status >= 600;

        case 'Informational':
          return $status >= 100 && $status < 200;

        case 'Successful':
          return $status >= 200 && $status < 300;

        case 'Redirection':
          return $status >= 300 && $status < 400;

        case 'ClientError':
          return $status >= 400 && $status < 500;

        case 'ServerError':
          return $status >= 500 && $status < 600;

        default:
          return defined(constant('self::' . $constant))
                 ? $status === constant('self::' . $constant)
                 : false;
      }
    }
  }
}

/*
 * EOF
 */

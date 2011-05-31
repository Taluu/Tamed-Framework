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
   * @todo complete these lists
   */
  const
    CONTINU = 100, SWITCHING_PROTOCOL = 101,
    OK = 200, CREATED = 201, NO_CONTENT = 204, PARTIAL_CONTENT = 206,
    MULTIPLE_CHOICE = 300, MOVED_PERMANENTLY = 301, FOUND = 302, SEE_OTHER = 303, NOT_MODIFIED = 304,
    BAD_REQUEST = 400, AUTHORIZATION_REQUIRED = 401, FORBIDDEN = 403, NOT_FOUND = 404, NOT_ALLOWED = 405,
    SERVER_ERROR = 501, NOT_IMPLEMENTED = 502, SERVICE_UNAVAILABLE = 503, VERSION_NOT_SUPPORTED = 505;

  private static
    $_status = array(
      self::CONTINU => 'Continue',
      self::SWITCHING_PROTOCOL => 'Switching Protocol',

      self::OK => 'OK',
      self::CREATED => 'Created',
      self::NO_CONTENT => 'No Content',
      self::PARTIAL_CONTENT => 'Partial Content',

      self::MULTIPLE_CHOICE => 'Multiple Choice',
      self::MOVED_PERMANENTLY => 'Moved Permanently',
      self::FOUND => 'Found',
      self::SEE_OTHER => 'See Other',
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
   * @var \View\Bridge
   */
  private $_view = null;

  protected
    $_statusCode = self::OK,
    $_redirect = false,
    $_headers = array(),
    $_cookies = array();

  function __construct($_code = self::OK) {
    $this->_statusCode = $_code;
  }

  /**
   * Send a header to the client
   *
   * @param string $header Header's name
   * @param boolean $replace Replace an existing header ?
   * @param integer $code
   * @return Header
   */
  public function header($header, $value = null, $replace = true, $code = self::OK) {
    $this->_headers[$header] = new Header($header, $value, $replace, $code);
    return $this->_headers[$header];
  }

  /**
   * Throws a redirection
   *
   * @param string $url URL to be redirected
   * @param integer $time The time before being redirected
   * @param integer $code Status code to be sent
   * @throws Exception if the URL is empty
   */
  public function redirect($url, $time = 0, $code = self::FOUND) {
    if (empty($url)) {
      throw new Exception('Can\'t redirect to an empty url');
    }

    if (!$this->isRedirect($code)) {
      throw new Exception(sprintf('Bad HTTP status response (%1$s given)', $code));
    }

    if ($time === 0) {
      $header = 'Location';
      $value = $url;
    } else {
      $header = 'Refresh';
      $value = $time . ';url=' . $url;
    }

    \Debug::info('Redirecting...');
    $this->_redirect = true;
    $this->status($code);
    $this->header($header, $value, true, $code);
    $this->sendHeaders();
  }

  /**
   * Sets the status header for the current page
   *
   * @param integer $_status Status of the page
   */
  public function status($_status) {
    if (!isset(self::$_status[$_status])) {
      throw new \Exception('Unknown status');
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
    $this->_headers[$name] = new Cookie($name, $val, $timeout);
  }

  /**
   * Render the current page
   * Sends all the headers (cookie, session, ...), and render the $view file.
   *
   * @param string $view File to be rendered
   * @return void
   */
  public function render($view = null) {
    //\ob_end_flush(); // ?
    $this->sendHeaders();

    $content = '';

    if (!$this->isRedirect() && $view !== null) {
      list($engineName, $engineVersion) = $this->_view->getEngineInfos(\View\Bridge::INFO_NAME | \View\Bridge::INFO_VERSION);
      \Debug::info('Rendering the template using ' . $engineName . ' (version ' . $engineVersion . ')');
      $content = $this->_view->render($view);
    }

    return $content;
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
   * @param \View\Bridge $_view View engine to affect
   * @return \View\Bridge View engine affected
   */
  public function view(\View\Bridge $_view = null) {
    if ($_view !== null) {
      $this->_view = $_view;
    }

    return $this->_view;
  }

  /**
   * Sends the header to the navigator
   *
   * @return void
   */
  public function sendHeaders($force = false) {
    if (\headers_sent()) {
      // @todo Manage correctly exceptions
      throw new Exception('The header have already been sent');
    }

    foreach ($this->_headers as &$header) {
      $header->send($force);
    }
  }

  public function getStatus() {
    return $this->_statusCode;
  }

  private function setStatus($_status) {
    if ($this->isInvalid($_status)) {
      throw new Exception('Bad code for response component');
    }

    $this->_statusCode = $_status;
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
     * Params :
     *  $url; URL to be redirected
     *  $time; Time to be redirected (0 : instant)
     *  $code; Status code (default: See Other)
     */
    if (substr($method, 0, 8) == 'redirect') {
      $status = intval(substr($method, 8));

      if (isset($args[2])) {
        $code = $args[2];
      } else {
        switch ($status) {
          case 404:
            $code = self::MOVED_PERMANENTLY;
            break;

          default:
            $code = self::FOUND;
        }
      }

      $this->redirect($args[0], isset($args[1]) ? $args[1] : 0, $code);

      return;
    }

    /*
     * isXYZ methods
     *
     * Params :
     *  $_status ; Status to check. If it is not set, use current status
     */
    if (substr($method, 0, 2) == 'is') {
      $code = substr($method, 2);
      $status = isset($args[0]) ? $args[0] : $this->getStatus();
      $constant = strtoupper($code);

      if (filter_var($code, FILTER_VALIDATE_INT) !== false) {
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

        case 'Redirected':
          return $this->isRedirect() || $this->_redirect === true;

        case 'Redirect':
          return in_array($status, array(201, 301, 302, 303, 307));

        default:
          return false;
      }
    }
  }
}

/*
 * EOF
 */

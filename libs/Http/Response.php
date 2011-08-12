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

use \View\Bridge;

/**
 * Definition of the HttpResponse class
 *
 * Handles eveything sent by the server.
 *
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 * @package twk.http
 *
 * @method void redirect404(string $url, int $time = 0) Redirects the user to $url in $time seconds, with a 404 status.
 *
 * @method bool isInvalid(int $status) checks if the http response is an invalid type (code < 100 or >= 600)
 * @method bool isInformational(int $status) checks if the http response is an informational type (code 1XX)
 * @method bool isSuccessful(int $status) checks if the http response is a successful type (code 2XX)
 * @method bool isRedirection(int $status) checks if the http response is a redirection type (code 3XX)
 * @method bool isRedirected(int $status) checks if the client was redirected
 * @method bool isRedirect(int $status) checks if this was a redirection
 * @method bool isClientError(int $status) checks if there was a client error (code 4XX)
 * @method bool isServerError(int $status) checks if there was a server error (code 5XX)
 * @method bool isError(int $status) checks if there was an error coming from the server (code 5XX) or the client (4XX)
 * @method bool isPartial(int $status) checks if this is a partial content
 */
class Response {
  /**
   * Headers
   *
   * @todo check how to write CONTINUE for CONTINU...
   * @todo complete this list
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

  protected
    $_statusCode = self::OK,
    $_content = null,
    $_redirect = false,
    $_headers = array(),
    $_cookies = array();

  /**
   * Construct the Response Object
   *
   * @param string $_code Status Response Code
   */
  function __construct($_code = self::OK) {
    $this->_statusCode = $_code;
  }

  /**
   * Send a header to the client
   *
   * @param string $header Header's name
   * @param boolean $replace Replace an existing header ?
   * @param integer $code
   *
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
   *
   * @return void
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
   * Render the current content
   *
   * Sends all the headers (cookie, session, ...), and returns the content.
   *
   * @return void
   */
  public function render() {
    //\ob_end_flush(); // ?
    $this->sendHeaders();
    return !$this->isRedirect() ? $this->_content : '';
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
   * Sends the header to the navigator
   *
   * @return void
   */
  public function sendHeaders($force = false) {
    if (\headers_sent()) {
      // @todo Manage correctly exceptions
      throw new \Exception('The header have already been sent');
    }

    foreach ($this->_headers as &$header) {
      $header->send($force);
    }
  }

  /**
   * @ignore
   */
  public function getStatus() {
    return $this->_statusCode;
  }

  /**
   * @ignore
   */
  private function setStatus($_status) {
    if ($this->isInvalid($_status)) {
      throw new \Exception('Bad code for response component');
    }

    $this->_statusCode = $_status;
  }

  /**
   * Sets the content of this page
   *
   * @param type $_content the content to be returned
   */
  public function setContent($_content = null) {
    $this->_content = $_content;
  }

  /**
   * @ignore
   * @todo Limit the use of this magic method
   * @todo for isXYZ methods : if it is an int, control which status can be used
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
      switch (intval(substr($method, 8))) {
        case 404:
          $code = self::MOVED_PERMANENTLY;
          break;

        default:
          $code = self::FOUND;
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

      switch ($code) {
        case 'Invalid':
          return $status < 100 || $status >= 600;
          break;

        case 'Informational':
          return $status >= 100 && $status < 200;
          break;

        case 'Successful':
          return $status >= 200 && $status < 300;
          break;

        case 'Redirection':
          return $status >= 300 && $status < 400;
          break;

        case 'ClientError':
          return $status >= 400 && $status < 500;
          break;

        case 'ServerError':
          return $status >= 500 && $status < 600;
          break;

        case 'Error':
          return $this->isClientError() || $this->isServerError();
          break;

        case 'Redirected':
          return $this->isRedirect() || $this->_redirect === true;
          break;

        case 'Redirect':
          return in_array($status, array(201, 301, 302, 303, 307));
          break;

        case 'Partial':
          return $status === 206;

        default:
          return false;
      }
    }
  }
}

/*
 * EOF
 */

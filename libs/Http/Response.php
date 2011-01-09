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
  const
    A = 0;

  protected
    $_status = array(

     );

  /**
   * Send a header to the client
   *
   * @param string $header Header's name
   * @param boolean $replace Replace an existing header ?
   * @param integer $code
   */
  public function header($header, $replace = true, $code = 200) {
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
  public function redirect($url, $time = 0, $code = 200) {
    if (empty($url)) {
      throw new Exception('Can\'t redirect to an empty url');
    }

    $header = $time !== 0 ? "Refresh: {$time};url={$url}" : "Location: {$url}";
    $this->header($header, true, $code);
  }

  /**
   * Sets the status for the current page
   *
   * @param integer $status Status of the page
   */
  public function status($status) {
    $this->header($this->_status[$status], true, $status);
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
    return $_SERVER[$key] ?: null;
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
      $status = intval(substr($method, 8));

      $this->status($status);
      $this->redirect($args[0], $args[1] ?: 0, $status);
      return;
    }
  }

}

/*
 * EOF
 */

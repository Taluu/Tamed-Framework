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
  public function header($header, $replace = true, $code = 200) {
    if (\headers_sent ()) {
      // @todo Manage correctly exceptions
      throw new Exception('todo');
    }

    return \header($header, $replace, $code);
  }

  public function redirect($url, $code = 200) {
    return $this->header('Location: ' . $url, true, $code);
  }

  public function redirect404($url) {
    $this->header('HTTP/1.0 404 Not Found');
    $this->redirect($url, 404);
  }

  public function cookie($name, $val, $timeout) {
    return \setrawcookie($name, \rawurlencode($str), \time() + $timeout);
  }

  public function render() {
    \ob_end_flush();
    exit;
  }

}

/*
 * EOF
 */

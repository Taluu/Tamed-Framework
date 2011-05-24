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
 * Represents a Cookie
 *
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 * @package twk.http
 * @see \setcookie()
 */
class Cookie extends Header {
  private
    $_name = null,
    $_value = null,
    $_timeout = 0,
    $_path = null,
    $_domain = null,
    $_secure = false,
    $_httpOnly = false;

  function __construct($_name, $_value, $_timeout = 0, $_path = './', $_domain = null, $_secure = false, $_httpOnly = false) {
    if ($_timeout instanceof \DateTime) {
      $_timeout = $_timeout->format('U');
    }

    $this->_name = $_name;
    $this->_value = $_value;
    $this->_timeout = $_timeout;
    $this->_path = $_path;
    $this->_domain = $_domain;
    $this->_secure = $_secure;
    $this->_httpOnly = $_httpOnly;
  }

  public function send() {
    return \setrawcookie($this->_name, \rawurlencode($this->_value),
                         $this->_timeout, $this->_path, $this->_domain,
                         $this->_secure, $this->_httpOnly);
  }

  public function getName() {
    return $this->_name;
  }

  public function getValue() {
    return $this->_value;
  }

  public function getTimeout() {
    return $this->_timeout;
  }

  public function isHttpOnly() {
    return $this->_httpOnly;
  }

  public function isSecure() {
    return $this->_secure;
  }

  public function getDomain() {
    return $this->_domain;
  }

  public function getPath() {
    return $this->_path;
  }
}

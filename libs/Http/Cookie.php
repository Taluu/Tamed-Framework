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
 * Represents a Cookie
 *
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 * @package tamed.http
 *
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

  /**
   * Build the cookie
   *
   * @param string $_name Cookie's name
   * @param mixed $_value Cookie's raw value
   * @param date $_timeout The time the cookie expires. May be a DateTime or a
   *                       unix timestamp.
   * @param string $_path The path on the server in which the cookie will be
   *                    available on.
   * @param string $_domain The domain that the cookie is available to.
   * @param bool $_secure Indicates that the cookie should only be transmitted
   *                      over a secure HTTPS connection from the client
   * @param bool $_httpOnly When TRUE the cookie will be made accessible only through the HTTP protocol.
   *
   * @see \setrawcookie()
   */
  function __construct($_name, $_value, $_timeout = 0, $_path = './', $_domain = null, $_secure = false, $_httpOnly = false) {
    if (!filter_var ($_timeout, FILTER_VALIDATE_INT)) {
      if (!$_timeout instanceof \DateTime) {
        $_timeout = new DateTime($_timeout);
      }

      $_timeout = $_timeout->format('U');
    }



    $this->_name = (string) $_name;
    $this->_value = (string) $_value;
    $this->_timeout = (int) $_timeout;
    $this->_path = (string) $_path;
    $this->_domain = (string) $_domain;
    $this->_secure = (bool) $_secure;
    $this->_httpOnly = (bool) $_httpOnly;
  }

  /**
   * Sends the cookie
   *
   * @return Returns TRUE on success or FALSE on failure.
   */
  public function send() {
    return \setrawcookie($this->_name, \rawurlencode($this->_value),
                         $this->_timeout, $this->_path, $this->_domain,
                         $this->_secure, $this->_httpOnly);
  }

  /**
   * @ignore
   */
  public function getName() {
    return $this->_name;
  }

  /**
   * @ignore
   */
  public function getValue() {
    return $this->_value;
  }

  /**
   * @ignore
   */
  public function getTimeout() {
    return $this->_timeout;
  }

  /**
   * @ignore
   */
  public function isHttpOnly() {
    return $this->_httpOnly;
  }

  /**
   * @ignore
   */
  public function isSecure() {
    return $this->_secure;
  }

  /**
   * @ignore
   */
  public function getDomain() {
    return $this->_domain;
  }

  /**
   * @ignore
   */
  public function getPath() {
    return $this->_path;
  }
}

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
 * Represents a Header
 *
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 * @package twk.http
 */
class Header {
  protected
    $_header = null,
    $_value = null,
    $_replace = false,
    $_status = Response::CONTINU;

  /**
   * Construct this header
   *
   * @param string $_header Header to send
   * @param string $_value Value of this header
   * @param bool $_replace Does this header replace an already existing value ?
   * @param int $_status Status code to be sent with this header
   */
    function __construct($_header, $_value = null, $_replace = true, $_status = Response::OK) {
      $this->_header = $_header;
      $this->_value = $_value;
      $this->_replace = $_replace;
      $this->_status = $_status;
    }

    /**
     * Sends the header
     */
  public function send() {
    $header = $this->_header . ': ' . $this->_value;

    if ($this->_value === null) {
      $header = $this->_header;
    }

    \header($header, $this->_replace, $this->_code);
  }

  public function getHeader() {
    return $this->_header;
  }

  public function getReplace() {
    return $this->_replace;
  }

  public function getStatus() {
    return $this->_status;
  }

  public function getValue() {
    return $this->_value;
  }
}
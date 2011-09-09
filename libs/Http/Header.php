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

namespace Tamed\Http;

use \Tamed\Debug;

/**
 * Represents a Header
 *
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 * @package tamed.http
 */
class Header {
  protected
    $_header = null,
    $_value = null,
    $_replace = false,
    $_status = Response::CONTINU,
    $_sent = false;

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
     *
     * @param bool $force forces the header to be sent... even if it was already sent
     */
  public function send($force = false) {
    if ($this->_sent === false || $force === true) {
      $header = $this->_header . ': ' . $this->_value;

      if ($this->_value === null) {
        $header = $this->_header;
      }

      Debug::info('Sending header (%1$s, code %2$d)', $header, $this->_status);
      \header($header, $this->_replace, $this->_status);

      $this->_sent = true;
    }
  }

  /**
   * @ignore
   */
  public function getHeader() {
    return $this->_header;
  }

  /**
   * @ignore
   */
  public function getReplace() {
    return $this->_replace;
  }

  /**
   * @ignore
   */
  public function getStatus() {
    return $this->_status;
  }

  /**
   * @ignore
   */
  public function getValue() {
    return $this->_value;
  }
}
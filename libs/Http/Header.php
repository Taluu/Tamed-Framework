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
    $_replace = false,
    $_status = Response::CONTINU;

  public function __construct() {

  }

  public function send() {
    
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
}
<?php
/**
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @copyright Copyleft (c) 2010+, Baptiste Clavié, Talus' Works
 * @link http://www.talus-works.net Talus' Works
 * @license http://creativecommons.org/licenses/by-sa/3.0/ CC-BY-SA 3.0+
 * @version $Id$
 */

namespace Controller\Sub;

use \Controller\Front;
use \Http\Response;

/**
 * Error Controller
 *
 * @package twk.controller.sub
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 */
class Error extends Front {
  public function notfound_404Action() {
    $this->_status = Response::NOT_FOUND;
    $this->_template = 'index';
  }
}

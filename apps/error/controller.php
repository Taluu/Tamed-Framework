<?php
/**
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @copyright Copyleft (c) 2010+, Baptiste Clavié, Talus' Works
 * @link http://www.talus-works.net Talus' Works
 * @license http://www.opensource.org/licenses/BSD-3-Clause Modified BSD License
 * @version $Id$
 */

namespace Tamed\Apps\Error;

use \Tamed\Controller\Front as FrontController;
use \Tamed\Http\Response;

/**
 * Error Controller
 *
 * @package tamed.apps.error
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 */
class Controller extends FrontController {
  public function notFound() {
    $this->_status = Response::NOT_FOUND;
    $this->_template = 'index';
  }
}

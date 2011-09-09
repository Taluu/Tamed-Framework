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

namespace Tamed\Example\Controller;

use \Tamed\Controller\Front;

/**
 * Home Controller
 *
 * @package tamedexampled.controller.sub
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 */
class Home extends Front {
  public function indexAction() {
    $this->_template = 'index';
    $this->test = $_SERVER['DOCUMENT_ROOT'];
  }
}

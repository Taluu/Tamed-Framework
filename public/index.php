<?php
/** 
 * Main Controller
 *
 * This is the Entry Point, choosing which controller we'll be using, and how
 * to make it all work. It appends every needed things, like how to get a correct
 * header and a correct footer.
 *
 * To make it simple, this is the basis of all the sub-controllers ; in order to
 * make them work, they MUST extend this class.
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @package Talus' Works
 * @copyright Copyleft (c) 2010+, Baptiste Clavié, Talus' Works
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 * @link http://www.talus-works.net Talus' Works
 * @license http://www.gnu.org/licenses/lgpl.html LGNU Public License 2+
 */
namespace Controller;

define('SAFE', true);
require __DIR__ . '/../libs/__init.php';

abstract class Front {
  protected $_view;

  final protected function  __construct() {
    $this->main();
    
    $this->_head();
    Obj::$tpl->parse($this->_view);
    $this->_foot();
  }

  final protected function _head() {}
  final protected function _foot() {}

  abstract protected function main();

  /**
   * @return Sub
   */
  final public static function start() {}
}

$p = Front::start();
echo 'lala';

/*
 * EOF
 */

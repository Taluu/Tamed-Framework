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

namespace View;

require __DIR__ . '/../Vendor/Talus-TPL/lib/Talus_TPL/Main.php';

/**
 * Definition of a view written in Talus TPL syntax
 *
 * Acts as a bridge between Talus' TPL and Talus' Works, allowing to use Talus TPL
 * in the views
 *
 * @package twk.view
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 */
class Talus_TPL extends Bridge {
  protected
    /**
     * @var \Talus_TPL
     */
    $_engine = null;

  public function __construct() {
    $dir = __DIR__ . '/../../views/';
    $this->_engine = new \Talus_TPL($dir . 'templates', $dir . 'cache', array());
  }

  protected function _assign(){
    $this->_engine->set($this->_vars);
  }

  public function bind($var, &$value){
    $this->_engine->bind($var, $value);
  }

  public function getEngineInfos($info = self::INFO_ALL) {
    $return = array();

    if ($info & self::INFO_NAME) {
      $return[] = 'Talus\' TPL';
    }

    if ($info & self::INFO_VERSION) {
      $return[] = \Talus_TPL::VERSION;
    }

    if ($info & self::INFO_ENGINE) {
      $return[] = $this->_engine;
    }

    if (count($return) == 1) {
      $return = $return[0];
    }

    return $return;
  }

  protected function _render($view) {
    return $this->_engine->pparse($view . '.html');
  }
}

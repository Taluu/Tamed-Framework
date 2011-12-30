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

namespace Tamed\View;

/**
 * Definition of the bridge between the framework & the selected view engine
 *
 * @package tamed.view
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 */
abstract class Bridge {
  const
    INFO_NAME = 1,
    INFO_VERSION = 2,
    INFO_ENGINE = 4,

    /** Returning all the infos **/
    INFO_ALL = 7;

  protected $_vars = array();

  /**
   * Renders a view
   *
   * @param string $view View to be rendered
   * @return string Result
   */
  final public function render($view) {
    $result = $this->_render($view);
    $this->_vars = array();
    
    return $result;
  }

  /**
   * Renders a view
   *
   * Must be overriten.
   *
   * @param string $_view View to be rendered
   * @param array $_context local variables
   * @return string Result
   */
  abstract protected function _render($_view, $_context = array());

  /**
   * Gets information regarding the engine
   *
   * @param int $info Level of information needed
   * @return mixed
   */
  abstract public function getEngineInfos($info = self::INFO_ALL);

  /**
   * @ignore
   */
  final public function __set($var, $value) {
    $this->_vars[$var] = $value;
  }
}

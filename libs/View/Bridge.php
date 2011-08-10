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

/**
 * Definition of the bridge between the framework & the selected view engine
 *
 * @package twk.view
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
   * Send all the defined variables to the view engine
   *
   * @return void
   */
  protected function _assign() {}

  /**
   * Binds a variable
   *
   * @param string $var Variable's name (tpl side)
   * @param mixed &$value Variable references (php side)
   * @return void
   */
  abstract public function bind($var, &$value);

  /**
   * Renders a view
   *
   * @param string $view View to be rendered
   * @return string Result
   */
  final public function render($view) {
    $this->_assign();
    return $this->_render($view);
  }

  /**
   * Renders a view
   *
   * Must be overriten.
   *
   * @param string $view View to be rendered
   * @return string Result
   */
  abstract protected function _render($view);

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

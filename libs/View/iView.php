<?php
/**
 * Definition of the View Interface
 *
 * Handles all the actions needed for the view.
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @package Talus' Works
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 * @copyright ©Talus, Talus' Works 2010+
 * @link http://www.talus-works.net Talus' Works
 * @license http://creativecommons.org/licenses/by-sa/3.0/ CC-BY-SA 3.0+
 */

namespace View;

interface iView {
  const
    INFO_NAME = 1,
    INFO_VERSION = 2,

    /** Returning all the infos **/
    INFO_ALL = 3;

  /**
   * Assigns a variable
   *
   * @param string $var Variable's name (tpl side)
   * @param string $value Variable's value
   * @return void
   */
  public function assign($var, $value);

  /**
   * Binds a variable
   *
   * @param string $var Variable's name (tpl side)
   * @param mixed &$value Variable references (php side)
   * @return void
   */
  public function bind($var, &$value);

  /**
   * Renders a view
   *
   * @return void
   */
  public function render($view);

  /**
   * Gets information regarding the engine
   *
   * @return mixed
   */
  public function getEngineInfos($info = self::INFO_ALL);
}

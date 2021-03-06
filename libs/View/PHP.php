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
 * Definition of a view written in PHP syntax
 *
 * Acts as a bridge between PHP and Talus' Works, letting PHP acts as the templating
 * engine
 *
 * @package tamed.view
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 */
class PHP extends Bridge {
  private $_view = null;

  /**
   * @inheritdoc
   */
  public function getEngineInfos($info = self::INFO_ALL) {
    $return = array();

    if ($info & self::INFO_ENGINE) {
      $return[] = null; // PHP Itself is the templating engine
    }

    if ($info & self::INFO_NAME) {
      $return[] = 'PHP';
    }

    if ($info & self::INFO_VERSION) {
      $return[] = PHP_VERSION;
    }

    if (count($return) == 1) {
      $return = $return[0];
    }

    return $return;
  }

  /**
   * @inheritdoc
   */
  protected function _render($_view, $_context = array()) {
    $this->_view = __DIR__ . '/../../views/templates/' . $_view . '.' . \PHP_EXT;

    ob_start();
    extract($_context, EXTR_REFS | EXTR_OVERWRITE);
    include $this->_view;
    $content = ob_get_clean();

    $this->_view = null;
    return $content;
  }
}

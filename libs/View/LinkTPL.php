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
 * Definition of a view written in Link TPL syntax
 *
 * Acts as a bridge between Link TPL and Tamed Framework, allowing to use Link
 * TPL in the views. Note, this bridge uses the Filesystem method for both the
 * loader and the cache systems.
 *
 * @package tamed.view
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 */
class LinkTPL extends Bridge {
  protected
    /**
     * @var \Link_Environnement
     */
    $_engine = null;

  /**
   * @inheritdoc
   */
  public function __construct() {
    // -- Note : when the PSR-0 system will be put in place, these lines shall disappear
    require __DIR__ . '/../../vendor/Link-TPL/lib/Link/Autoloader.php';
    \Link_Autoloader::register();
    
    $dir = __DIR__ . '/../../views';
    
    // -- loaders
    $loader = new \Link_Loader_Filesystem($dir . '/templates');
    $cache = new \Link_Cache_Filesystem($dir . '/cache');
    
    $this->_engine = new \Link_Environnement($loader, $cache, array());
  }

  /**
   * @inheritdoc
   */
  public function getEngineInfos($info = self::INFO_ALL) {
    $return = array();

    if ($info & self::INFO_NAME) {
      $return[] = 'Link\' TPL';
    }

    if ($info & self::INFO_VERSION) {
      $return[] = \Link_Environnement::VERSION;
    }

    if ($info & self::INFO_ENGINE) {
      $return[] = $this->_engine;
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
    return $this->_engine->pparse($_view . '.html', $_context);
  }
}

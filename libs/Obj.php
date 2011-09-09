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

namespace Tamed;

if (!defined('SAFE')) exit;

/**
 * Global Object Handler
 *
 * Contains all the global objects needed by the framework.
 *
 * @package twk
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 */
abstract class Obj {
  /**
   * @var \Configuration\Loader
   */
  static public $config = null;

  /**
   * @var \Controller\Front
   */
  static public $controller = null;

  /**
   * @var \Routing\Router
   */
  static public $router = null;
}

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

use \Tamed\Configuration\Loader;
use \Tamed\Controller\Front;

define('SAFE', true);
if (!defined('PHP_EXT')) define('PHP_EXT', \pathinfo(__FILE__, \PATHINFO_EXTENSION));

/*
 * Like Capitaine Mousse said in Jet...
 * Let's rock !
 *
 * @todo Handle correctly the exception
 */
try {
  require __DIR__ . '/../libs/__init.' . PHP_EXT;

  Tamed\Obj::$config->setEnv(Loader::ENV_DEV);

  $p = Front::getController()->render();
} catch (\Exception $e) {
  echo $e;
}

/*
 * EOF
 */

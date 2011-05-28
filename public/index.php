<?php
/**
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @copyright Copyleft (c) 2010+, Baptiste Clavié, Talus' Works
 * @link http://www.talus-works.net Talus' Works
 * @license http://creativecommons.org/licenses/by-sa/3.0/ CC-BY-SA 3.0+
 * @version $Id$
 */

namespace Controller;

define('SAFE', true);
if (!defined('PHP_EXT')) define('PHP_EXT', \pathinfo(__FILE__, \PATHINFO_EXTENSION));

require __DIR__ . '/../libs/__init.' . PHP_EXT;

/*
 * Like Capitaine Mousse said in his ShwaarkFramework...
 * Let's rock !
 *
 * @todo Handle the exception
 */
try {
  $p = Front::getController(null, array('view' => new \View\PHP)); // Using PHP instead of Talus TPL
  //$p = Front::getController();
} catch (Exception $e) {
  echo $e;
}

/*
 * EOF
 */

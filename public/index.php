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


/*
 * Like Capitaine Mousse said in his ShwaarkFramework...
 * Let's rock !
 *
 * @todo Handle the exception
 */
try {
  require __DIR__ . '/../libs/__init.' . PHP_EXT;

  //$p = Front::getController(null, array('view' => new \View\Talus_TPL)); // Using Talus TPL instead of PHP
  $p = Front::getController(); // By default, use PHP as the templating engine
} catch (\Exception $e) {
  echo $e;
}

/*
 * EOF
 */

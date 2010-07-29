<?php
/**
 * Starter
 *
 * This is a part of the Entry Point ; this file is called once (and only once !)
 * It starts all the necessary things to make it all work.
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @package Talus' Works
 * @copyright Copyleft (c) 2010+, Baptiste Clavié, Talus' Works
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 * @link http://www.talus-works.net Talus' Works
 * @license http://www.gnu.org/licenses/lgpl.html LGNU Public License 2+
 */

if (!defined('SAFE')) exit;
if (defined('ALREADY_STARTED')) exit;
define('ALREADY_STARTED', true);

spl_autoload_register(function ($class) {});

/**
 * Contains all the global objects needed for the project.
 */
abstract class Obj {
  static public $config = null;

  /**
   * @var Controler
   */
  static public $controler = null;

  /**
   * @var Talus_TPL
   */
  static public $tpl = null;

  /**
   * @var Router
   */
  static public $router = null;
}

abstract class Sys {}

/*
 * EOF
 */

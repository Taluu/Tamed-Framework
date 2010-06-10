<?php
/**
 * Point d'entrée du site (front controller).
 * Détermine quel controlleur il faut utiliser par la suite, suivant les
 * paramètres fournis.
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @package Talus' Works
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 * @copyright ©Talus, Talus' Works 2010+
 * @link http://www.talus-works.net Talus' Works
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 3+
 */

define('SAFE', true);

require '../libs/__init.php';

namespace Controllers;

abstract class Front {
  /**
   * Récupération du bon controlleur
   *
   * @return Front
   */
  final public static function start() {
    // -- blabla args blabla choix de la classe controller
  }

  final protected function _headFoot() {
    // -- entete du site, pied, vars, etc
  }

  abstract protected function _main();
}

Front::start();

<?php
/**
 * Initialisation du Bordel.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 * @package Talus' Works
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 * @copyright ©Talus, Talus' Works 2007+
 * @link http://www.talus-works.net Talus' Works
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 3+
 * @version $Id: __init.php 2 2009-11-09 16:28:30Z talus $
 */
 
if (!defined('ROOT') || !defined('PHP_EXT')) exit;

//TODO : Se documenter pour les espaces de noms... ?
spl_autoload_register(
  function ($load) {
    $folders = explode('\\', trim($load, '\\'));
    $file = str_replace('_', '-', array_pop($folders)) . '.' . PHP_EXT;

    $folders = empty($folders) ?: array('global');

    $dir = ROOT . '/libs/namespaces/' . implode(DIRECTORY_SEPARATOR, $folders);
    $file = "{$dir}/{$file}";

    // -- Il s'agit soit d'une classe, soit d'un namespace
    if (file_exists($file)) {
      require $file;
    } else {
      throw new Exception("Wazzat ?");
    }
  }
 );

/**
 * Regroupement des instances des objets nécessaires au framework
 */
abstract class Obj {
  /**
   * @var Talus_TPL
   */
  public static $tpl = null;

  /**
   * @var Router
   */
  public static $router = null;

  /**
   * @var DateTime
   */
  public static $now = null;
}

/**
 * Regroupement des différentes variables utiles pour tout le framework
 */
abstract class Sys {
  public static 
    $debug = false,
    $dst = false;
}

// -- Init des objets
Obj::$tpl = new Talus_TPL(__DIR__ . '/../apps/views/html/', __DIR__ . '/../apps/cache/');
Obj::$date = new DateTime('now', new DateTimeZone(TW_TIME_ZONE));
Obj::$router = new Router;

/*
 * Calcul du DST
 * 
 * On est dans le DST (Daylight Saving Time), c'est à dire en +1H si on est
 * après l'heure d'été, et avant l'heure d'hiver. On est en heure d'été si on
 * est après le dernier dimanche du mois de Mars, et avant le dernier dimanche
 * du mois d'Octobre. Le tout à l'heure UTC.
 *
 * @link http://docs.php.net/manual/fr/function.gmmktime.php#47605
 */
$compare = new DateTime('01:00:00', Obj::$now->getTimezone());
$dst = false;

$compare->setDate(Obj::$now->format('Y'), 4, 1);
$compare->modify('Last Sunday');
$dst = Obj::$now >= $compare;

$compare->setDate(Obj::$now->format('Y'), 11, 1);
$compare->modify('Last Sunday');
Sys::$dst = $dst && Obj::$now < $compare;

unset($dst, $compare);

/*
 * EOF
 */

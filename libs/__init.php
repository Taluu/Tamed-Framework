<?php
/**
 * Initialisation du Bordel.
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @package Talus' Works
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 * @copyright ©Talus, Talus' Works 2007+
 * @link http://www.talus-works.net Talus' Works
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License 3+
 */
 
if (!defined('SAFE')) exit;

// -- Autoloader
spl_autoload_register(
  function ($load) {
    $file = __DIR__ . DIRECTORY_SEPARATOR . $load . '.' . PHP_EXT;

    if (file_exists($file)) {
      require $file;
      return true;
    }

    return false;
  }
 );

/**
 * Regroupement des instances des objets nécessaires au framework
 */
abstract class Obj {
  /**
   * @var Talus_TPL\Main
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
Obj::$tpl = new Talus_TPL\Main(__DIR__ . '/../views/html/', __DIR__ . '/../views/cache/');
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

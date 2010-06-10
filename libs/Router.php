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

if (!defined('SAFE')) exit;

class Router {
  protected 
    $_params = array(),
    $_namedParams = array();

  public function __construct() {
    $this->_params = explode('/', trim($_SERVER['REQUEST_URI']));

    // -- Récupération du query string & interprétation
    $last = &$this->_params[count($this->_params) - 1];
    list($last, $qs) = explode('?', $last);

    $vars = array(); parse_str($qs, $vars); 
    $_GET = array();
    
    foreach ($vars as $var => $val) {
      $_GET[$var] = $val;
    }

    $_REQUEST = array_merge($_GET, $_POST, $_COOKIE);

    // -- Interprétation du request uri démuni du query string
    $this->_namedParams['controller'] = array_shift($this->_params);
    $this->_params['command'] = implode('/', $this->_params);
  }

  /**
   * Nommage de paramètres
   *
   * @param string|array $arg,... Noms des paramètres à nommer
   * @return Router
   */
  public function name($arg) {
    $args = func_get_args();

    if (count($args) == 1 && is_array($arg)) {
      $args = $arg;
    }

    $nbArgs = min(count($args), count($this->_params) - 1);

    for ($i = 0; $i < $nbArgs; ++$i) {
      if ($args[$i] == 'controller') {
        throw new Exception('Controller est un paramètre nommé spécial et ne peut être employé autrement');
      }

      $this->_namedParams[$args[$i]] = array_shift($this->_params);
    }

    // -- Actualisation de command
    $commands = array();

    foreach ($this->_namedParams as $param => $val) {
      if ($param == 'controller') continue;
      $commands[] = $param . ':' . $val;
    }

    unset($this->_params['command']);
    $this->_params['command'] = implode('/', array_merge($command, $this->_params));

    return $this;
  }

  /**
   * Getter pour un paramètre nommé $p (sauf command)
   *
   * @param string $p Nom à récupérer
   * @return &string Valeur du paramètre (null si non trouvé)
   */
  protected function &_get($p) {
    if ($p == 'command') {
      return $this->_params['command'];
    }

    if (isset($this->_namedParams[$p])) {
      return $this->_namedParams[$p];
    }

    return null;
  }

  /**
   * Récupère un ou plusieurs paramètres
   *
   * @param mixed $p,... Noms à récupérer
   * @return &mixed Valeurs des paramètres (null si non trouvé)
   */
  public function &get($p) {
    $p = is_array($p) ?: func_get_args();

    if (count($p) > 1) {
      $return = array();

      foreach ($p as &$n) {
        $return[$n] = $this->_get($n);
      }

      return $return;
    }

    return $this->_get($p);
  }
}

/*
 * EOF
 */

<?php
/**
 * Moteur de gestion de TPLs -- Customisé pour TWK2
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
 * @package Talus' TPL
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 * @copyright ©Talus, Talus' Works 2006+
 * @link http://www.talus-works.net Talus' Works
 * @license http://www.gnu.org/licenses/lgpl.html LGNU Public License 2+
 */

namespace Talus_TPL;

if (!defined('PHP_EXT')) define('PHP_EXT', pathinfo(__FILE__, PATHINFO_EXTENSION));

class Main {
  protected
    $_root = './',

    $_tpl = '',
    $_last = array(),
    $_included = array(),

    $_blocks = array(),
    $_vars = array(),

    /**
     * @var Talus_TPL\Compiler_Interface
     */
    $_compiler = null,

    /**
     * @var Talus_TPL\Cache_Interface
     */
    $_cache = null;

  protected static $_autoloadSet = false;

  const
    INCLUDE_TPL = 0,
    REQUIRE_TPL = 1,
    VERSION = '1.8.0 Custom';

  /**
   * Initialisation.
   *
   * @param string $root Le dossier contenant les templates.
   * @param string $cache Le dossier contenant le cache.
   * @param array $dependencies Dépendances pour la Dependency Injection
   * @return void
   */
  public function __construct($root, $cache, array $dependencies = array()){
    // -- Destruction du cache des fichiers de PHP
    clearstatcache();

    // -- Mise en place de l'autoload si il n'a pas encore été défini
    if (self::$_autoloadSet === false) {
      spl_autoload_register('self::_autoload');
      self::$_autoloadSet = true;
    }

    // -- Init des paramètres
    $this->_last = array();
    $this->_included = array();
    $this->_blocks['.'] = array(array());
    $this->_vars = &$this->_blocks['.'][0];

    // -- Gestion des dépendances
    $this->dependencies($dependencies);

    // -- Si pas de DI, comportement par défaut
    if ($this->_compiler === null) {
      $this->_compiler = new Compiler;
    }

    if ($this->_cache === null) {
      $this->_cache = new Cache;
    }

    // -- Mise en place du dossier de templates
    $this->dir($root, $cache);
  }

  /**
   * Gestion de l'autoload pour les classes Talus' TPL
   *
   * @param string $class Nom de la classe à charger
   * @throws Exceptions\Autoload
   * @return bool
   */
  private static function _autoload($class) {
    // -- Une classe ne commençant pas par Talus_TPL.. OSEF !
    if (mb_strpos($class, __NAMESPACE__) !== 0) {
      return false;
    }

    // -- Extraction de l'arborescence, ejection du premier namespace
    $file = explode('\\', $class); array_shift($file);
    $file = __DIR__ . implode('\\', $file) . PHP_EXT;
   
    // -- Si le fichier n'existe pas, on jette une exception
    if (!file_exists(__DIR__ . DIRECTORY_SEPARATOR . $file)) {
      throw new Exceptions\Autoload(array('Classe %s non trouvée', $class), 8);
      return false;
    }

    // -- Inclusion du bon fichier
    require $file;
    return true;
  }

  /**
   * Permet de choisir le dossier contenant les tpls.
   *
   * @param string $root Le dossier contenant les templates.
   * @param string $cache Le dossier contenant le cache des tpls.
   * @throws Talus_Dir_Exception
   * @return void
   *
   * @since 1.7.0
   */
  public function dir($root = './', $cache = './cache/') {
    // -- On ampute le root du slash final, si il existe.
    $root = rtrim($root, '/');

    // -- Le dossier existe-t-il ?
    if (!is_dir($root)) {
      throw new Exceptions\Dir(array('%s n\'est pas un répertoire.', $root), 1);
      return;
    }

    $this->_root = $root;
    $this->_cache->dir($cache);
  }

  /**
   * Définit une ou plusieurs variable. Agit également comme getter (pour
   * Talus_TPL_Cache::exec()).
   *
   * @param array|string $vars Variable(s) à ajouter
   * @param mixed $value Valeur de la variable si $vars n'est pas un array
   * @return &array
   *
   * @since 1.3.0
   */
  public function &set($vars, $value = null){
    if (is_array($vars)) {
      $this->_vars = array_merge($this->_vars, $vars);
    } elseif ($vars !== null) {
      $this->_vars[$vars] = $value;
    }

    return $this->_vars;
  }

  /**
   * Définit une variable par référence.
   *
   * @param mixed $var Nom de la variable à ajouter
   * @param mixed &$value Valeur de la variable à ajouter.
   * @throws Exceptions\Var
   * @return void
   *
   * @since 1.7.0
   */
  public function bind($var, &$value) {
    if (mb_strtolower(gettype($var)) != 'string') {
      throw new Exceptions\Vars('Nom de variable référencée invalide.', 3);
      return;
    }

    $this->_vars[$var] = &$value;
  }

  /**
   * Permet d'ajouter une itération d'un bloc et de ses variables
   * Si $vars = null, alors on retourne le bloc
   *
   * @param string $block Nom du bloc à ajouter.
   * @param array|string $vars Variable(s) à assigner à ce bloc
   * @param string $value Valeur de la variable si $vars n'est pas un array
   * @return void
   *
   * @since 1.5.1
   */
  public function &block($block, $vars, $value = null) {
    /*
     * Si le nom du bloc est un bloc racine, et que les vars sont nulles, alors
     * cette méthode joue un rôle de getter, et renvoi le bloc racine en question
     */
    if ($vars === null) {
      if (strpos($block, '.') === false) {
        $return = array();

        if (isset($this->_blocks[$block])) {
          $return = &$this->_blocks[$block];
        }

        return $return;
      }

      throw new Exceptions\Block('Nom de Variable invalide.');
      return null;
    }

    if (!is_array($vars)) {
      $vars = array($vars => $value);
    }

    /*
     * Récupération de tous les blocs, du nombre de blocs, et mise en place d'une
     * référence sur la variable globale des blocs.
     *
     * Le but d'une telle manipulation est de parcourir chaque élément "$current",
     * afin d'accéder au bloc désiré, et permettre ainsi l'initialisation des
     * variables pour la dernière instance du bloc appelé.
     */
    $blocks = explode('.', $block);
    $curBlock = array_pop($blocks); // Bloc à instancier
    $current = &$this->_blocks;
    $cur = array();
    $nbRows = 0;

    foreach ($blocks as &$cur) {
      if (!isset($current[$cur])) {
        throw new Exceptions\Block(array('Le bloc %s n\'est pas défini.', $cur), 4);
        return null;
      }

      $current = &$current[$cur];
      $current = &$current[count($current) -  1];
    }

    if (!isset($current[$curBlock])) {
      $current[$curBlock] = array();
      $nbRows = 0;
    } else {
      $nbRows = count($current[$curBlock]);
    }

    /*
     * Variables spécifiques aux blocs (inutilisables autre part) :
     *
     * FIRST : Est-ce la première itération (true/false) ?
     * LAST : Est-ce la dernière itération (true/false) ?
     * CURRENT : Itération actuelle du bloc.
     * SIZE_OF : Taille totale du bloc (Nombre de répétitions totale)
     *
     * On peut être à la première itération ; mais ce qui est sur, c'est
     * qu'on est forcément à la dernière itération.
     *
     * Si le nombre d'itération est supérieur à 0, alors ce n'est pas la
     * première itération, et celle d'avant n'était pas la dernière.
     *
     * Quant au nombre d'itérations (SIZE_OF), il suffit de lier la variable
     * de l'instance actuelle aux autres, et ensuite d'incrémenter cette
     * même variable
     */
    $vars['FIRST'] = true;
    $vars['LAST'] = true;
    $vars['CURRENT'] = $nbRows + 1;
    $vars['SIZE_OF'] = 0;

    if ($nbRows > 0) {
      $vars['FIRST'] = false;
      $current[$curBlock][$nbRows - 1]['LAST'] = false;

      $vars['SIZE_OF'] = &$current[$curBlock][0]['SIZE_OF'];
    }

    ++$vars['SIZE_OF'];
    $current[$curBlock][] = $vars;

    return $current[$curBlock];
  }

  /**
   *  Parse & execute un TPL.
   *
   * @param mixed $tpl TPL concerné
   * @param mixed $cache Utiliser le cache si il existe ?
   * @throws Exceptions\Parse
   * @return bool
   */
  public function parse($tpl, $cache = true){
    // -- Si plusieurs fichiers en paramètres, alors plusieurs appels de parse()
    if (func_num_args() > 2 || is_array($tpl)) {
      // -- Retrait du deuxieme paramètre ($cache) si nombre de paramètres > 2
      if (func_num_args() > 2) {
        $tpl = func_get_args();
        array_shift($tpl); array_shift($tpl); array_unshift($tpl, func_get_arg(0));
      }

      foreach ($tpl as &$file) {
        $this->parse($file);
      }

      return true;
    }

    // -- Erreur critique si vide
    if (strlen((string) $tpl) === 0) {
      throw new Exceptions\Parse('Aucun modèle à parser.', 5);
      return false;
    }

    $file = sprintf('%1$s/%2$s', $this->_root, $tpl);

    // -- Déclaration du fichier
    if (!isset($this->_last[$file])) {
      if (!is_file($file)) {
        throw new Exceptions\Parse(array('Le modèle %s n\'existe pas.', $tpl), 6);
        return false;
      }

      $this->_last[$file] = filemtime($file);
    }

    $this->_tpl = $tpl;
    $this->_cache->file($this->_tpl, 0);

    // -- Si le cache n'existe pas, ou n'est pas valide (ou qu'on force), on le met à jour.
    if (!$this->_cache->isValid($this->_last[$file]) || !$cache) {
      $this->_cache->put($this->str(file_get_contents($file), false));
    }

    $this->_cache->exec($this);
    return true;
  }

  /**
   * Parse & execute une chaine de caractère TPL
   *
   * @param string $str Chaine de caractère à parser
   * @param bool $exec Faut-il exécuter le code TPL ?
   * @throws Exceptions\Parse
   * @return string Code PHP généré
   */
  public function str($str, $exec = true) {
    if (empty($str)) {
      throw new Exceptions\Parse('Aucune chaine spécifiée.');
      return false;
    }

    // -- Compilation
    $compiled = $this->_compiler->compile($str);

    // -- Mise en cache, execution
    if ($exec === true) {
      $this->_tpl = sprintf('tmp_%s.html', sha1($str));
      $this->_cache->file($this->_tpl, 0);
      $this->_cache->put($compiled);
      $this->_cache->exec($this);
      $this->_cache->destroy();
    }

    return $compiled;
  }

  /**
   * Parse un TPL
   * Implémention de __invoke() pour PHP >= 5.3
   *
   * @param mixed $tpl TPL concerné
   * @see Talus_TPL::parse()
   * @return void
   */
  public function __invoke($tpl) {
    return $this->parse($tpl);
  }

  /**
   * Parse le TPL, mais renvoi directement le résultat de celui-ci (entièrement
   * parsé, et donc déjà executé par PHP).
   *
   * @param string $tpl Nom du TPL à parser.
   * @param integer $ttl Temps de vie (en secondes) du cache de niveau 2
   * @return string
   *
   * @todo Cache de niveau 2 ??
   */
  public function pparse($tpl = '', $ttl = 0){
    ob_start();
    $this->parse($tpl);
    return ob_get_clean();
  }

  /**
   * Inclue un TPL : Le parse si nécessaire
   *
   * @param string $file Fichier à inclure.
   * @param bool $once N'inclure qu'une fois ?
   * @param integer $type Inclusion requise ?
   * @return void
   *
   * @see Compiler::compile()
   * @throws Exceptions\Runtime
   * @throws Exceptions\Parse
   */
  public function includeTpl($file, $once = false, $type = self::INCLUDE_TPL){
    // -- Extraction des paramètres
    $qString = '';

    if (strpos($file, '?') !== false) {
      list($file, $qString) = explode('?', $file, 2);
    }

    /*
     * Si un fichier ne doit être présent qu'une seule fois, on regarde si il a
     * déjà été inclus au moins une fois.
     *
     * Si oui, on ne l'inclue pas ;
     * Si non, on l'ajoute à la pile des fichiers inclus.
     */
    if ($once){
      $toInclude = sprintf('%1$s/%2$s', $this->root(), $file);

      if (in_array($toInclude, $this->_included)) {
        return;
      }

      $this->_included[] = $toInclude;
    }

    $data = '';
    $current = array(
      'vars' => $this->_vars,
      'tpl' => $this->_tpl
     );

    try {
      // -- On ne change les vars que si y'a la présence d'un QS
      if (!empty($qString)) {
        // -- Récupération des paramètres nommés
        $vars = array();
        parse_str($qString, $vars);

        // -- Si MAGIC_QUOTES (grmph), on saute les \ en trop...
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
          $vars = array_map('stripslashes', $vars);
        }

        // -- Traitement des paramètres
        $this->set(array_change_key_case($vars, CASE_UPPER));
      }

      $data = $this->pparse($file);
    } catch (Exceptions\Parse $e) {
      /*
       * Si l'erreur est la n°6 (tpl non existant), et qu'il s'agit d'une balise
       * "require", on renvoit une autre exception (Exceptions\Runtime) ;
       * sinon, on affiche juste le message de l'exception capturée si c'est
       * include, ou on rejette l'erreur si c'en est pas un.
       */
      if ($e->getCode() === 6) {
        if ($type == self::REQUIRE_TPL) {
          throw new Exceptions\Runtime(array('Ceci était une balise "require" : puisque le template %s n\'existe pas, le script est interrompu.', $file), 7);
          exit;
        }

        echo $e->getMessage();
      } else {
        throw $e;
      }
    }

    $this->_tpl = $current['tpl'];
    $this->_vars = $current['vars'];

    echo $data;
  }

  /**#@+
   * Getters / Setters
   */

  /**
   * Root
   *
   * @return string
   */
  public function root() {
    return $this->_root;
  }

  /**
   * Compilateur
   *
   * @return Interfaces\Compiler
   */
  public function compiler() {
    return $this->_compiler;
  }

  /**
   * Cache
   *
   * @return Interfaces\Cache
   */
  public function cache() {
    return $this->_cache;
  }

  /**
   * Mets à jour les dépendances.
   *
   * @contributor Jordane Vaspard
   * @param mixed $dependencies,.. Dépendances
   * @return void
   * @throws Exceptions\Dependency
   */
  public function dependencies($dependencies = array()) {
    if (func_num_args() > 1) {
      $dependencies = func_get_args();
    } elseif (!is_array($dependencies)) {
      $dependencies = array($dependencies);
    }

    foreach ($dependencies as &$dependency) {
      if ($dependency instanceof Interfaces\Compiler) {
        $this->_compiler = $dependency;
      } elseif ($dependency instanceof Interfaces\Cache) {
        $this->_cache = $dependency;
      } else {
        throw new Exceptions\Dependency(
                array('%s n\'est pas une dépendance reconnue.', get_class($dependency)));
      }
    }
  }

  /**#@-*/
}

/*
 * EOF
 */

<?php
/**
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @copyright ©Talus, Talus' Works 2011+
 * @link http://www.talus-works.net Talus' Works
 * @license http://www.opensource.org/licenses/BSD-3-Clause Modified BSD License
 * @version $Id$
 */

namespace Tamed\Loader;

/**
 * ClassLoader following the specs of PSR-0
 *
 * If a class Test have a namespace (let's say \Tamed\Example), if this namespace
 * is registered, the file should be located in a Tamed/Example/Test.php.
 *
 * Each part of the namespaced name represents a directory (Tamed is a directory,
 * Example is subdirectory, even if its name has _ in it). If the class name has
 * any _, each _ is translated into a /.
 *
 * This autoloader will autoload only if a prefix is recognized (whether it is
 * or it is not a namespaced name).
 *
 * Example usage, if we are on the root of the project :
 *
 *  <?php
 *    $loader = new Classloader;
 *
 *    $loader->registerPrefixes(array(
 *      'Tamed' => array(__DIR__ . '/apps/', __DIR__ . '/vendors/Tamed/src/Tamed'),
 *      'Talus_TPL_' => __DIR__ . '/vendors/Talus_TPL/lib/Talus_TPL'
 *     ));
 *
 *    $loader->register();
 *
 * This autoloader is based on the UniversalClassLoader of Symfony 2.
 *
 * @package tamed.loader
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 * @link http://groups.google.com/group/php-standards/web/psr-0-final-proposal
 */
class ClassLoader {
  protected $_prefixes = array();

  /**
   * Register a couple of prefixes for given classes.
   *
   * @param array $_prefixes couple of prefixes to load
   * @return void
   */
  public function registerPrefixes(array $_prefixes = array()) {
    foreach ($_prefixes as $prefix => $dirs) {
      $this->registerPrefix($prefix, $dirs);
    }
  }

  /**
   * Register a prefix for given classes
   *
   * It may be a real prefix (Talus_TPL_ for example) or a namespaced prefix
   * (like Tamed\Loader for example). It will be stripped when loaded, if there
   * is any matches.
   *
   * If $_dir is a couple of directories, when a given class will be loaded, this
   * autoloader will search in each of these directories (by the order of the
   * declaration).
   *
   * @param string $_prefix prefix to register
   * @param array|string $_dirs corresponding directories
   * @return void
   */
  public function registerPrefix($_prefix, $_dirs) {
    $this->_prefixes[$_prefix] = (array) $_dirs;
  }

  /**
   * Register this autoloader
   *
   * @param bool $_prepend Whether to prepend or not this autoloader on the stack
   */
  public function register($_prepend = false) {
    $self = &$this;

    spl_autoload_register(function ($class) use (&$self) {
        if (($file = $self->getFile($class)) !== null) {
          require $file;
          return true;
        }

        return false;
      }, true, $_prepend);
  }

  /**
   * Gets a file for a given class
   *
   * Search through all the prefixes if the class can be loaded
   *
   * @param type $class class to be loaded
   * @return string|null the file containing the class declaration if found, null
   *                     otherwise
   */
  public function getFile($class) {
    // -- removing the first \
    $class = ltrim($class, '\\');

    // -- exploring each prefixes
    foreach ($this->_prefixes as $prefix => $dirs) {
      if (($pos = mb_strpos($class, $prefix)) === 0) {
        $class = trim(mb_substr($class, mb_strlen($prefix)), '\\');

        $namespace = '';
        $className = $class;
        $classFile = '';

        if (($pos = mb_strrpos($class, '\\')) !== false) {
          $namespace = mb_substr($class, 0, $pos);
          $className = mb_substr($class, $pos + 1);

          $classFile .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }

        $classFile .= str_replace('_', DIRECTORY_SEPARATOR, $className);
        $classFile .= '.' . PHP_EXT;

        foreach ($dirs as &$dir) {
          $file = $dir . DIRECTORY_SEPARATOR . $classFile;

          if (file_exists($file)) {
            return $file;
          }
        }
      }
    }

    return null;
  }
}

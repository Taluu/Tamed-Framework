<?php
/**
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @copyright ©Talus, Talus' Works 2010+
 * @link http://www.talus-works.net Talus' Works
 * @license http://creativecommons.org/licenses/by-sa/3.0/ CC-BY-SA 3.0+
 * @version $Id$
 */

namespace Configuration;

/**
 * Definition of Config class
 * 
 * Represents a Configuration file, extracted from a JSON file in /conf/
 * 
 * @package twk.configuration
 * @author Baptiste "Talus" Clavié <clavie.b@gmail.com>
 */
class Config {
  protected 
    $_file = null,
     
    $_datas = array(),
    $_appliedCallbacks = array(),
     
    $_const = false,
    $_loaded = false;
     
  /**
   * @param string $_file Filename to be loaded
   * @param bool $_isConst Can we modify the configuration values ?
   */
  public function __construct($_file, $_isConst = false) {
    $this->_file = $_file;
    $this->_const = (bool) $_isConst;
  }
  
  /**
   * Loads from the configuration file
   *
   * @param \Closure $_callback Callback to be applied (if any)
   * @return void
   */
  public function load(\Closure $_callback = null) {
    if ($this->_loaded === true) {
      return;
    }
    
    if (!is_file($this->_file)) {
      throw new \Exception('Configuration file ' . $this->_file . ' not found');
    }
    
    $this->_datas = json_decode(file_get_contents($this->_file), true);
    
    $ex = $this->_checkJson();
    
    if ($ex !== null) {
      throw new \Exception('Bad JSON (' . $ex . ') in configuration file ' . $this->_file);
    }
    
    if ($_callback !== null) {
      $this->_applyCallback($_callback);
    }
    
    $this->_loaded = true;
  }
  
  public function __get($var) {
    if (!isset($this->_datas[$var])) {
      throw new Exception('Unknown configuration value for ' . $var);
    }
    
    return $this->_datas[$var];
  }
  
  public function __set($var, $val) {
    if ($this->_const === true) {
      throw new Exception('Unable to modify a constant configuration !');
    }

    $this->_datas[$var] = $val; 
  }
  
  /**
   * Applies a callback on the datas
   * 
   * @param \Closure $_callback callback to be applied
   * @todo check how to verify that this callback is used only once ?
   */
  public function applyCallback(\Closure $_callback) {
    array_walk($this->_datas, $_callback);
  }
  
  public function save() {
    if ($this->_const) {
      throw new Exception('Can\'t save into configuration file ' . $this->_file . ', because it is constant');
    }
    
    $data = json_encode($this->_datas);
    
    $ex = $this->_checkJson();
    
    if ($ex !== null) {
      throw new \Exception('Bad JSON (' . $ex . ') in configuration file ' . $this->_file);
    }
    
    file_put_contents($this->_file, $data);
  }
  
  private function _checkJson() {
    switch (json_last_error()) {
      case JSON_ERROR_DEPTH:
        $ex = 'Maximum stack depth exceeded';
        break;
      
      case JSON_ERROR_STATE_MISMATCH:
        $ex = 'Invalid or malformed JSON';
        break;
      
      case JSON_ERROR_CTRL_CHAR:
        $ex = 'Control character error, possibly incorrectly encoded';
        break;
      
      case JSON_ERROR_SYNTAX:
        $ex = 'Syntax error';
        break;
      
      case JSON_ERROR_UTF8:
        $ex = 'Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
      
      default:
      case JSON_ERROR_NONE:
        $ex = null;
    }
    
    return $ex;
  }
}

/*
 * EOF
 */

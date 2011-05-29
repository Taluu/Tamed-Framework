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

$routes = array(
  '404' => new \Routing\Route('error', 'notfound_404', '/error/notfound_404'),
  'default' => new \Routing\Route('home', 'index', '/:any')
 );

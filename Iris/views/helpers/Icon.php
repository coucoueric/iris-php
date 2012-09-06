<?php

namespace Iris\views\helpers;

/*
 * This file is part of IRIS-PHP.
 *
 * IRIS-PHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * IRIS-PHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with IRIS-PHP.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * @copyright 2012 Jacques THOORENS
 *
 * 
 * @author Jacques THOORENS (irisphp@thoorens.net)
 * @see http://irisphp.org
 * @license GPL version 3.0 (http://www.gnu.org/licenses/gpl.html)
 * @version $Id: $ * @todo : test it and maybe suppress
 */

/**
 * Creates an iconic link
 *
 */
class Icon extends _ViewHelper {
   
    protected static $_Singleton = TRUE;
    protected $_baseDir = '/icones/';
    protected $_javaScript = FALSE;

    public function help(){
        return $this;
    }
    
   /**
     * Création d'un lien-icone
     * 
     * @param string $ref
     * @param string $iconName
     * @param string $help
     * @param string $desc
     * @param string $iconText
     * @return type 
     */
    public function link($ref, $iconName, $help, $desc=null, $iconText='',$class=\NULL) {
        $desc = is_null($desc) ? $iconName : $desc;
        $icon = $this->_view->image($this->_baseDir . $iconName, $desc, $help, '', $class) . $iconText;
        return '<a href="' . $ref . '">' . $icon . '</a>';
    }
   
    /**
     * Création d'un lien-icone avec du javascript
     * 
     * @param string $ref
     * @param string $iconName
     * @param string $help
     * @param string $desc
     * @param string $iconText
     * @return type 
     */
    public function jsLink($ref, $iconName, $help, $desc=null, $iconText='') {
    }
    
    
    /**
     * accesseur en écriture pour le répertoire de base des icônes
     * 
     * @param type $baseDir : répertoire de base pour les icônes
     */
    public function setBaseDir($baseDir) {
        $this->_baseDir = $baseDir;
    }

    
    private function _setJS(){
        if($this->_javaScript){
            return;
        }
        $this->_view->javascriptLoader('jsLink',<<<JS

JS
);
    }
}

?>

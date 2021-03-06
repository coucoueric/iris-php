<?php


namespace Tutorial\controllers\helpers;

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
 * @copyright 2011-2013 Jacques THOORENS
 */

/**
 * Generates a Screen object with all the informations necessary to
 * load the resources for one screen : images, text, voice and synchonisation.
 * The content may a simple image (IMAGE), a simple (VIEW) or a simple text (TEXT)
 * or both a text and a view, displayed in tabs (TEXTVIEW)
 * 
 * Project IRIS-PHP
 * 
 * @author Jacques THOORENS (irisphp@thoorens.net)
 * @see http://irisphp.thoorens.net
 * @license GPL version 3.0 (http://www.gnu.org/licenses/gpl.html)
 * @version :$Id:
 */
abstract class _Content extends \Iris\controllers\helpers\_ControllerHelper {

    

    /**
     * Returns an new completed item.
     * 
     * @param int $num
     * @return \Tutorial\Content\Screen
     */
    public function help($num) {
        return $this->getItem($num);
    }

    /**
     * The method will be overwritten in each concret tutorial
     * 
     * @return \Tutorial\Content\Screen
     */
    protected abstract function getItem($num);
}


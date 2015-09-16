<?php
namespace Iris\Engine;

/*
 * This file is part of IRIS-PHP, distributed under the General Public License version 3.
 * A copy of the GNU General Public Version 3 is readable in /library/gpl-3.0.txt.
 * More details about the copyright may be found at
 * <http://irisphp.org/copyright> or <http://www.gnu.org/licenses/>
 *  
 * @copyright 2011-2015 Jacques THOORENS
 */

/**
 * A singleton has only on instance and must have a non public constructor
 * 
 * @author Jacques THOORENS (irisphp@thoorens.net)
 * @see http://irisphp.org
 * @license GPL version 3.0 (http://www.gnu.org/licenses/gpl.html)
 * @version $Id: $
 */
trait tSingleton {

    
    protected function __construct() {
    }
    
    /**
     * Returns the unique instance or creates it if necessary.
     * 
     * @staticvar \static $Instance Serves to store the unique instance
     * @return \static
     */
    public static function GetInstance() {
        static $Instance = \NULL;
        if (is_null($Instance)) {
            $Instance = new static();
        }
        return $Instance;
    }

}



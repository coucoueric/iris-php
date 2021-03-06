<?php
namespace Iris\Admin\models;

/*
 * This file is part of IRIS-PHP, distributed under the General Public License version 3.
 * A copy of the GNU General Public Version 3 is readable in /library/gpl-3.0.txt.
 * More details about the copyright may be found at
 * <http://irisphp.org/copyright> or <http://www.gnu.org/licenses/>
 *  
 * @copyright 2011-2016 Jacques THOORENS
 */

/**
 * Model for the application modules
 * 
 * @author Jacques THOORENS (irisphp@thoorens.net)
 * @see http://irisphp.org
 * @license GPL version 3.0 (http://www.gnu.org/licenses/gpl.html)
 * @version $Id: $ */
class TModules extends _IrisObject {
    
    protected static $_InsertionKeys = ['Name'];
    
    
    public static function DDLText() {
        return <<<SQL
CREATE  TABLE "main"."modules" (
        "id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL , 
        "Name" TEXT  NOT NULL,
        "Deleted" BOOLEAN DEFAULT 0);
SQL;
    }

}



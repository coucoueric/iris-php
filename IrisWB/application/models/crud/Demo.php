<?php

namespace models\crud;


/**
 * Project : srv_IrisWB
 * Created for IRIS-PHP 1.0 RC2
 *
 * CRUD operations for Demo generated by 
 * iris.php -- entitygenerate
 */
class Demo extends \Iris\DB\DataBrowser\_Crud {
    
    
    
    
    public function __construct($param = NULL) {
        $this->setCrudEntity('products');
        //static::$_EntityManager = '...';
        parent::__construct($param);
        // where to go after CRUD
        $this->setEndUrl('index');
        // where to in case of error
        $this->setErrorURL('error');
    }


    
}

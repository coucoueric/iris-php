<?php

namespace modules\tutorial\controllers;

/**
 * srv_IrisWB
 * Created for IRIS-PHP 0.9 - beta
 * Description of {CONTROLLER}
 * 
 * @author jacques
 * @license not defined
 */
class _tutorial extends \modules\_application {

    
    /**
     * This method can contain module level
     * settings
     */
    protected final function _moduleInit() {
        // You should modify this demo layout
        // these parameters are only for demonstration purpose
        // and required by the default layout defined in _tutorial
        $this->__buttons = 1 + 4;
        $this->__logoName = 'mainLogo';
    }

}

<?php
namespace Iris\MVC;
use \Iris\Errors;

/*
 * This file is part of IRIS-PHP, distributed under the General Public License version 3.
 * A copy of the GNU General Public Version 3 is readable in /library/gpl-3.0.txt.
 * More details about the copyright may be found at
 * <http://irisphp.org/copyright> or <http://www.gnu.org/licenses/>
 *  
 * @copyright 2011-2015 Jacques THOORENS
 */

/**
 * _BaseController:
 * an abstract class ancestor of any controller of the application.
 * <br/>
 * It provides mechanisms for: <ul>
 * <li> init : permits to simulate a standard creator </li>
 * <li> predispatch : any treatement to be done before the action</li>
 * <li> action : calls the action asked in URL</li>
 * <li> postdispatch : any treatment to be done after the action</li> 
 * </ul>
 * 
 */
class _BaseController {

    use \Iris\views\helpers\tViewHelperCaller;

    /**
     * Each controller has a type to be defined in subclasses
     * (used in error/debugging messages)
     * 
     * @var string
     */
    protected static $_Type = '';

    /**
     * The view linked to the controller (not to confound with the script
     * through which it displays itself)
     * 
     * @var \Iris\MVC\View
     */
    protected $_view;

    /**
     * The response which is driving the controller
     * 
     * @var \Iris\Engine\Response
     */
    protected $_response;

    /**
     * The active router when the controller was created
     *  
     * @var \Iris\Engine\Router
     */
    private $_router;

    /**
     * If the controller is not the main controller, a reference to 
     * this controller
     * 
     * @var _Controller
     */
    protected static $_MainController = NULL;

    /**
     * This directory may be used to store the view scripts without
     * explicitely saying so
     * 
     * @var string
     */
    protected $_defaultScriptDir = \NULL;

    /**
     * 
     * @param \Iris\Engine\Response $response 
     */
    public function __construct(\Iris\Engine\Response $response, $actionName = 'index') {
        $this->_response = $response;
        $this->_view = new View();
        $this->_view->setViewScriptName($actionName);
        \Iris\Errors\Handler::SystemTrace(static::$_Type, $response);
    }

    /**
     * Callback for customize application level contructor
     */
    protected function _applicationInit() {
        
    }

    /**
     * Callback for customize module level constructor
     */
    protected function _moduleInit() {
        
    }

    /**
     * Callback for customize constructor
     */
    protected function _init() {
        
    }

    /**
     * Prohibits any internal controller by default
     * To permits access it must have an overridden version of security
     * Public access is required for the dispatcher to access it
     * 
     * Some special cases with no code: <ul>
     * <li> various internal controllers  (!admin/ajax, !documents/file,
     * !iris/index, $iris/reset)
     * <li> iris/MVC/TestController
     * <li> main/_Error
     * <li> 
     * </ul>
     */
    public function security() {
        if ($this->_response->isInternal()) {
            $this->displayError(\Iris\Errors\Settings::TYPE_PRIVILEGE);
        }
    }

    /**
     * Callback to be called after initer and before action and dispatch
     */
    public function preDispatch() {
        
    }

    /**
     * Callback to be called after action and dispatch
     */
    public function postDispatch() {
        
    }

    /**
     * Call to the action method specified in the URL. Some treatment is
     * involved:<ul>
     * <li> verify permission (or reroute)
     * <li> verify method existence (invent it if necessary through _callAction)
     * </ul>
     */
    public function excecuteAction() {
        // if problem no return
        $action = $this->_response->getActionName();
        $this->_view->setResponse($this->_response);
        $actionName = $action . 'Action';
        $methodes = get_class_methods(get_class($this));
        // caution : in subcontrollers, the parameters are provided by the
        // main controller 
        $parameters = $this->getParameters();
        if (array_search($actionName, $methodes) === FALSE) {
            $this->__callAction($actionName, $parameters);
        }
        else {
            if (count($parameters)) {
                call_user_func_array(array($this, $actionName), $parameters);
            }
            else {
                $this->$actionName();
            }
        }
    }

    /**
     * Do the verification of ACL according to permissions defined 
     * in application/config/XXacl.ini
     * 
     * Some exceptions exists <ul>
     * <li> in !documents/file : everybody is allowed to have access
     * <li> in AjaxController subclasses the verification depends on $hasAcl value (true in mother class)
     * <li> in TestController subclasses the verification is done only in production mode
     * <li> in _Error subclasses no verification are done
     * <li> in WB controllers the verification depends on $aclIgnore value (true in mother class)
     * </ul>
     */
    protected function _verifyAcl() {
        // always happy
        $acl = \Iris\Users\Acl::GetInstance();
        $resource = '/' . $this->getModuleName() . '/' . $this->getControllerName();
        if (!$acl->hasPrivilege($resource, $this->getActionName())) {
            $this->displayError(Errors\Settings::TYPE_PRIVILEGE);
            // no return
        }
    }

    /**
     * Some controllers may have "action" methods called "magically" through this
     * method (overridden)
     * 
     * @param string $actionName the name of the action (e.g. "createAction" )
     * @param string[] parameters the parameters from URL
     */
    public function __callAction($actionName, $parameters) {
        \Iris\Engine\Memory::SystemTrace();
        throw new \Iris\Exceptions\ControllerException("Unknown action: $actionName");
    }

    /**
     * The view is converted to string (and echoed if requested). This method
     * is used only in case of no layout or by islets and subcontrollers
     * 
     * @param boolean $echoing if false, produce a string and return it
     * @return string (in case of not echoing) 
     */
    public function dispatch($echoing = \TRUE) {
        //$this->_view->setResponse($this->_response);
        if ($echoing) {
            echo $this->_view->render();
        }
        else {
            // in case of islet
            return $this->_view->render();
        }
    }

    public function quote($text, $data = \NULL) {
        if (is_null($data)) {
            $data = $this->_view;
        }
        $quoteView = new \Iris\MVC\Quote($text, $data);
        $render = $quoteView->render();
        $this->_view->addPrerending($render);
        $this->setViewScriptName('__QUOTE__');
    }

    /**
     * Offers a way to render a view manually by giving its name. 
     * By default, it is echoed directly.
     * 
     * @param string $scriptName
     * @param boolean $echoing
     * @param boolean $absolute If true, the name if an absolute one (relative to project root)
     * @return mixed 
     */
    public function renderNow($scriptName, $echoing = TRUE, $absolute = \FALSE) {
        $rendering = $this->_view->render($scriptName, $absolute);
        if ($echoing) {
            echo $rendering;
        }
        else {
            return $rendering;
        }
    }

    /**
     * Offers a way to render a view manually by giving its name. 
     * By default, it is echoed directly. Here the file name is absolute (starting from project root)
     * 
     * @param string $scriptName
     * @param boolean $echoing
     * @return mixed 
     */
    public function renderFile($scriptName, $echoing = TRUE) {
        return $this->renderNow($scriptName, $echoing, Template::ABSOLUTE);
    }

    public function preRender($scriptName) {
        //$this->_prerendering .= $this->renderNow($scriptName, \FALSE);
        $this->_view->addPrerending($this->renderNow($scriptName, \FALSE));
    }

    /**
     * Returns the subtype of the controller, essentially for
     * debugging purpose
     * 
     * @return string
     */
    public function getType() {
        return static::$_Type;
    }

    /**
     * Returns the module in which the controler has been found
     * (not in the URI if its a default one)
     * 
     * @return string 
     */
    public function getModuleName() {
        return $this->_response->getModuleName();
    }

    /**
     * Returns the name of the controller
     * 
     * @return string 
     */
    public function getControllerName() {
        return $this->_response->getControllerName();
    }

    /**
     * Returns the name of the expected action to be taken
     * @return string 
     */
    public function getActionName() {
        return $this->_response->getActionName();
    }

    /**
     * In standard controllers, the parameters are
     * in the URI (and then in response)
     * 
     * @return array
     */
    public function getParameters() {
        return $this->_response->getParameters();
    }

    /**
     *
     * @return \Iris\Engine\Router 
     */
    public function getRouter() {
        if (is_null($this->_router)) {
            $this->_router = \Iris\Engine\Router::GetInstance();
        }
        return $this->_router;
    }

    /**
     * Explicitely change the script to be rendered
     * 
     * @param type $scriptName 
     */
    public function setViewScriptName($scriptName) {
        $this->_view->setViewScriptName($scriptName);
    }

    
    /**
     * Sets a default directory for all scripts.
     * 
     * @param string $defaultScriptDir
     */
    public function setDefaultScriptDir($defaultScriptDir) {
        $this->_view->setDefaultScriptDir($defaultScriptDir);
    }
    
    /**
     *
     * @return Iris\Engine\Reponse 
     */
    public function getResponse() {
        return $this->_response;
    }

    /**
     * Permits to replace an action by another. The end
     * of the current action is not executed (an exception
     * is thrown, detected by the Program::run() method.
     * 
     * @param string $action
     * @throws \Iris\Exceptions\RedirectException
     */
    public function redirect($action) {
        $actionPara = explode('/', $action);
        $actionName = array_shift($actionPara);
        $this->_response->setAction($actionName);
        $this->setViewScriptName($actionName);
        $this->_response->setParameters($actionPara);
        $this->excecuteAction();
        $this->dispatch();
        $this->postDispatch();
        throw new \Iris\Exceptions\RedirectException('First');
    }

    /**
     * Permits to have a new URL (optionaly in another server)
     * 
     * @param string $URI
     * @param boolean $sameServer
     */
    public function reroute($URI, $sameServer = TRUE) {
        // if parameters have been put in array
        if (is_array($URI)) {
            list($URI, $sameServer) = $URI;
        }
        if ($sameServer) {
            $URI = \Iris\Engine\Superglobal::GetServer('HTTP_HOST') . $URI;
        }
        $URI = "http://$URI";
        header("location:$URI");
    }

    /**
     * A special reroute for Error (using Handlers)
     * 
     * @param string $errorAction
     */
    public function displayError($type) {
        $errorName = [
            Errors\Settings::TYPE_STANDARD => 'standard',
            Errors\Settings::TYPE_PRIVILEGE => 'privilege',
            Errors\Settings::TYPE_FATAL => 'fatal',
        ];
        $errorController = \Iris\Errors\Settings::$Controller;
        $this->reroute("$errorController/" . $errorName[$type]);
    }

    /**
     * If an non existent method is called from one of the controller method
     * the program tries to use a controller helper
     * 
     * @param string $functionName the non existent method
     * @param mixed[] $arguments the optional arguments as an array
     * @return mixed the value returned by the helper
     */
    public function __call($functionName, $arguments) {
        try {
            $actionResult = \Iris\controllers\helpers\_ControllerHelper::HelperCall($functionName, $arguments, $this);
            return $actionResult;
        }
        catch (_Exception $exc) {
            throw new \Iris\Exceptions\ResponseException("Action or action helper '$functionName' not found");
        }
    }

    /**
     * The better way to transmit a value to a view. By default to the main view,
     * if the third parameter is used to a subcontroller view.
     * 
     * @param string $name the variable name
     * @param mixed $values the variable value
     * @param int $number the view number (0 is mainview, other refers to a subcontroller view) 
     * @return mixed (for fluent interface)
     */
    public function toView($name, $values, $number = 0) {
        if (is_null($name)) {
            foreach ($values as $key => $value) {
                $this->toView($key, $value, $number);
            }
        }
        else {
            $this->_toView1($name, $values, $number);
        }
        return $values;
    }

    /**
     *
     * @param string $name the variable name
     * @param mixed $value the variable value
     * @param int $number the view number (ONLY TAKEN INTO ACCOUNT IN TRUE _CONTROLLER) 
     */
    protected function _toView1($name, $value, $number = 0) {
        $this->_view->$name = $value;
    }

    /**
     * An alias for toView
     * 
     * @param string $name the variable name
     * @param mixed $value the variable value
     * @param int $number the view number (0 is mainview, other refers to a subcontroller view) 
     * @return mixed (for fluent interface)
     */
    public function __($name, $value, $number = 0) {
        return $this->toView($name, $value, $number);
    }

    /**
     * A simpler way to transmit values toView, the variable name is prepended by __
     * (this method is reserved for main view)
     * 
     * @param string $__name the variable name
     * @param mixed $value the variable value
     */
    public function __set($__name, $value) {
        if (strpos($__name, '__') !== 0) {
            throw new \Iris\Exceptions\ControllerException(('Illegal attribute or bad view variable'));
        }
        else {
            $name = substr($__name, 2);
            $this->_view->$name = $value;
        }
    }

    /**
     *
     * @param string $name
     * @param mixed $value 
     */
    protected function _toMemory($name, $value) {
        \Iris\Engine\Memory::Set($name, $value);
    }

    /**
     * Islets often needs to get value from memory. An easier access...
     * 
     * @param string $name
     * @param mixed $default 
     */
    protected function _fromMemory($name, $default = NULL) {
        return \Iris\Engine\Memory::Get($name, $default);
    }

    /**
     *
     * @return _Controller
     */
    public static function GetMainController() {
        return self::$_MainController;
    }

    /**
     * this function is usefull in islet or subcontrollers. At this level,
     * it is provided as in insight of debugging
     * 
     * @param type $param
     * @throws \Iris\Exceptions\ControllerException
     */
    public function setParameters($param) {
        throw new \Iris\Exceptions\ControllerException('Normal controllers cannot have external parameters. Your controller must be an Islet or a Subcontroller');
    }

    

}

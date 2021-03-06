<?php
namespace modules\dojo\controllers;

/*
 * This file is part of IRIS-PHP, distributed under the General Public License version 3.
 * A copy of the GNU General Public Version 3 is readable in /library/gpl-3.0.txt.
 * More details about the copyright may be found at
 * <http://irisphp.org/copyright> or <http://www.gnu.org/licenses/>
 *  
 * @copyright 2011-2015 Jacques THOORENS
 */

/**
 * This controller tests the display of various sorts of container: <ul>
 * <li> accordionContainer
 * <li> borderContainer
 * <li> tabContainer
 * <li> linkedTabContainer
 * <li> splitContainer
 * <li> stackContainer
 * <li> timerStackContainer
 * </ul>
 * 
 * @author jacques
 * @license not defined
 */
class containers extends _dojo {

    protected function _init() {
        $this->setViewScriptName('all');
    }

    public function tabsAction($default = 'first') {
        $position = \Dojo\views\helpers\TabContainer::TOP;
        $this->callViewHelper('dojo_tabContainer', "container")
                ->setDefault($default)
                // the dimensions should be placed in the view script
                ->setDim(250, 450)
                ->setPosition($position)
                ->setItems([
                    "first" => 'First tab',
                    "second" => 'Second tab',
        ]);
    }

    /**
     * An alias for tabs action
     * 
     * @param type $default
     * @return type
     */
    public function tabsTopAction($default = 'first') {
        $this->_tabs($default, \Dojo\views\helpers\TabContainer::TOP);
    }

    public function tabsLeftAction($default = 'first') {
        $this->_tabs($default, \Dojo\views\helpers\TabContainer::LEFT);
    }

    public function tabsRightAction($default = 'first') {
        $this->_tabs($default, \Dojo\views\helpers\TabContainer::RIGHT);
    }

    public function tabsBottomAction($default = 'first') {
        $this->_tabs($default, \Dojo\views\helpers\TabContainer::BOTTOM);
    }

    private function _tabs($default, $position) {
        $this->callViewHelper('dojo_tabContainer', "container")
                ->setDefault($default)
                ->setDim(250, 450)
                ->setPosition($position)
                ->setItems([
                    "first" => 'First tab',
                    "second" => 'Second tab',
        ]);
    }

    public function linkedTabsAction($default = 'first') {
        $this->setViewScriptName(\NULL);
        return $this->_tabs($default, \Dojo\views\helpers\TabContainer::TOP);
    }

    public function accordionsAction($default = 'first') {
        $this->__title = "Example of accordions";
        $this->callViewHelper('dojo_accordionContainer', "container")
                ->setDefault($default)
                ->setDim(250, 450)
                ->setItems([
                    "first" => 'First tab',
                    "second" => 'Second tab',
        ]);
    }

    public function splitAction($default = 'first') {
        $this->__title = "Example of split screen";
        $this->callViewHelper('dojo_splitContainer', "container")
                ->setDefault($default)
                ->setDim(250, 450)
                ->setItems([
                    "first" => 'First tab',
                    "second" => 'Second tab',
        ]);
    }

    public function stackAction($default = 'first') {
        $this->__title = "Example of stack";
        $position = \Dojo\views\helpers\StackContainer::NONE;
        $this->_stackAction('container', $default, $position);
    }

    public function bottomControlledStackAction($default = 'first') {
        $this->__title = "Example of controlled stack";
        $position = \Dojo\views\helpers\StackContainer::BOTTOM;
        $this->_stackAction('container', $default, $position);
    }
    
    public function topControlledStackAction($default = 'first') {
        $this->__title = "Example of controlled stack";
        $position = \Dojo\views\helpers\StackContainer::TOP;
        $this->_stackAction('container', $default, $position);
    }
    /**
     * A stack of two levels having or not controller at the bottom
     * 
     * @param string $default
     * @param int $position
     */
    private function _stackAction($name, $default, $position) {
        $this->__title = "Example of stack";
        // the use of GetInstance from the view helper gives a smarter
        // way to access the method from IDE, but....
        //$this->callViewHelper('dojo_stackContainer', "container")
        $helper = \Dojo\views\helpers\StackContainer::GetInstance()
                ->setView($this->_view)
                ->help($name)
                ->setDefault($default)
                ->setName($name)
                ->setDim(250, 450)
                ->setPosition($position)
                ->setItems([
                    "first" => 'First tab',
                    "second" => 'Second tab',
        ]);
        // .. we need to transmit the helper to the view through its name
        //$this->toView($name, $helper->help($name));
    }

    public function borderAction() {
        // reset to default script
        $this->setViewScriptName('');
        $this->__title = "Example of border";
        $this->callViewHelper('dojo_borderContainer', "container")
                ->setDim(250, 450)
                ->setLayoutMode(\Dojo\views\helpers\BorderContainer::HEADLINE)
                ->setItems([
                    \Dojo\views\helpers\BorderContainer::TOP => 'First tab',
                    \Dojo\views\helpers\BorderContainer::BOTTOM => 'Second tab',
                    \Dojo\views\helpers\BorderContainer::LEFT => 'Third tab',
                    \Dojo\views\helpers\BorderContainer::RIGHT => 'Fourth tab',
                    \Dojo\views\helpers\BorderContainer::CENTER => 'Fith tab',
        ]);
    }

    public function borderSideAction() {
        $this->setViewScriptName('border');
        $this->__title = "Example of border";
        $this->callViewHelper('dojo_borderContainer', "container")
                ->setDim(250, 450)
                ->setLayoutMode(\Dojo\views\helpers\BorderContainer::SIDEBAR)
                ->setItems([
                    \Dojo\views\helpers\BorderContainer::TOP => 'First tab',
                    \Dojo\views\helpers\BorderContainer::BOTTOM => 'Second tab',
                    \Dojo\views\helpers\BorderContainer::LEFT => 'Third tab',
                    \Dojo\views\helpers\BorderContainer::RIGHT => 'Fourth tab',
                    \Dojo\views\helpers\BorderContainer::CENTER => 'Fith tab',
        ]);
    }

    public function titleAction() {
        // reset to default script
        $this->setViewScriptName('');
        $this->__title = "Example of title pane";
        $this->callViewHelper('dojo_titlePane', 'titlepane');
        $this->callViewHelper('dojo_titlePane', 'titlepane2');
    }

    /**
     * Example of programatical use of a container (tab)
     */
    public function tabProgAction() {
        $this->setViewScriptName('');
        // using a partial, you can produce whatever output you want.
        $text1 = $this->callViewHelper('partial', 'random');
        // a simple helper can be directly called
        $text2 = $this->callViewHelper('loremIpsum', [101, 102, 103, 104]);
        // template text is rendered literally
        $text3 = '<h4>No evaluation</h4>{loremIpsum([10, 20, 30, 40]}';
        // use helper quote() to have quoted template
        $text4 = $this->callViewHelper('quote', '<h4>Good evaluation</h4>{loremIpsum([10, 20, 30, 40])}');
        $this->__data = [
            "Tab 1 " => $text1,
            "Tab 2" => $text2,
            'Tab 3' => $text3,
            'Tab 4' => $text4];
    }

}

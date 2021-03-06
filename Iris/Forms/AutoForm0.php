<?php

namespace Iris\Forms;

use Iris\DB as db;

/*
 * This file is part of IRIS-PHP, distributed under the General Public License version 3.
 * A copy of the GNU General Public Version 3 is readable in /library/gpl-3.0.txt.
 * More details about the copyright may be found at
 * <http://irisphp.org/copyright> or <http://www.gnu.org/licenses/>
 *  
 * @copyright 2011-2015 Jacques THOORENS
 */

/**
 * A self generated form for rapid development. It can serve as
 * a basis for a customized form or use an ini file.
 * 
 * @author Jacques THOORENS (irisphp@thoorens.net)
 * @see http://irisphp.org
 * @license GPL version 3.0 (http://www.gnu.org/licenses/gpl.html)
 * @version $Id: $ */
class AutoForm0 {

    /**
     * the related entity
     * 
     * @var \Iris\DB\_Entity 
     */
    private $_entity;

    /**
     * the available metadata
     * 
     * @var \Iris\DB\Metadata
     */
    private $_metadata;

    /**
     * the form factory to be used
     * 
     * @var \Iris\Forms\_FormFactory
     */
    private $_formFactory;

    /**
     * the future form
     * 
     * @var \Iris\Forms\_Form
     */
    private $_form;

    /**
     * the specs found in ini file
     * 
     * @var \Iris\Forms\ElementSpecs[]
     */
    private $_fieldSpecs = [];

    /**
     * Stores the entity and finds its metadata
     * 
     * @param \Iris\DB\_Entity $entity
     */
    public function __construct(db\_Entity $entity) {
        $this->_entity = $entity;
        $this->_metadata = $entity->getMetadata();
        $this->_formFactory = _FormFactory::GetDefaultFormFactory();
    }

    /**
     * Prepare an automatic form, ready to be rendered
     * or manually modified
     * 
     * @return \Iris\Forms\Elements\Form
     */
    public function prepare() {
        //$this->_scanConfigFile(); // may overidde $this->_formFactory
        $this->_form = $this->_formFactory->createForm("iris_autoform_" . $this->_metadata->getTablename());
        foreach ($this->_metadata->getFields() as $name => $field) {
            $this->_createElement($name, $field);
        }
        $this->_addSubmit();
        return $this->_form;
    }

    /**
     * Returns the HTML string for the form, after preparing it 
     * if necessary
     * 
     * @return string
     */
    public function render() {
        if (is_null($this->_form)) {
            $this->prepare();
        }
        return $this->_form->render();
    }

    /**
     * Places all the data specified in the forms ini file, to be used
     * by the element generator.
     */
    private function _scanConfigFile() {
        $paraForm = \Iris\Engine\Memory::Get('param_forms', []);
        $entityName = $this->_entity->getEntityName();
        if (isset($paraForm[$entityName])) {
            /* @var $fields \Iris\SysConfig\Config */
            $fields = $paraForm[$entityName];
            foreach ($fields as $name => $field) {
                if ($name == '_Mode_') {
                    $this->_form->setMethod($fields[0]);
                }
                else {
                    $name2 = substr($name, 0, strlen($name) - 1);
                    $data = explode('!', $field . "!   !   !   !   !   !   !   !   !   !   !   !   !   !   !   !   !   !   !   !   !   !   ");
                    if (isset($this->_fieldSpecs[$name2])) {
                        $specs = $this->_fieldSpecs[$name2];
                        $name = $name2;
                    }
                    else {
                        $specs = new \Iris\Forms\ElementSpecs($name);
                        $type = array_shift($data);
                        $specs->setType($type);
                    }
                    $this->_addSpecs($specs, $data);
                    $this->_fieldSpecs[$name] = $specs;
                }
            }
        }
    }

    /**
     * 
     * @param \Iris\Forms\ElementSpecs $spec
     * @param type $data
     */
    private function _addSpecs($spec, $data) {
        foreach ($data as $item) {
            $command = strtoupper($item[0]);
            $text = substr($item, 2);
            switch($command){
                case 'L ':
                    $spec->setLabel($text);
                    break;
                case 'T ':
                    $spec->setTitle($text);
                    break;
                case '':
                    break;
                case '':
                    break;
                case '':
                    break;
                case '':
                    break;
                case '':
                    break;
                case '':
                    break;
                case '':
                    break;
            }
        }
    }

    /**
     * Creates a new element using the metadata, or the ini field specifications
     * 
     * @param string $name
     * @param \Iris\DB\MetaItem $field
     */
    private function _createElement($name, $field) {
        $formFactory = $this->_formFactory;
        if (isset($this->_fieldSpecs[$name])) {
            $element = $this->_fromConfig($name, $formFactory);
        }
        else {
            if (is_null($field->getForeignPointer())) {
                $element = $this->_fromMetadata($field, $formFactory);
            }
            else {
                
            }
        }
    }

    /**
     * Creates a new element the ini field specifications
     * 
     * @param string $name
     * @param \Iris\Forms\_FormFactory $formFactory
     */
    public function _fromConfig($name, $formFactory) {
        //$meta = $this->_metadata->getFields()[$this->_entity->getEntityName()];
        iris_debug($this->_entity->getEntityName());
        return $this->_fromMetadata($meta, $formFactory);
    }

    /**
     * Creates a new element using the metadata
     * 
     * @param \Iris\DB\MetaItem $field
     * @param \Iris\Forms\_FormFactory $formFactory
     */
    private function _fromMetadata($field, $formFactory) {
        $name = $field->getFieldName();
        //print $field->getType();
        switch ($field->getType()) {
            case 'BOOL':
                $element = $formFactory->createCheckbox($name);
                break;
            case 'DATETIME':
                $element = $formFactory->createDate($name);
                break;
            default:
                $element = $formFactory->createText($name);
        }
        $element->addTo($this->_form);
    }

    /**
     * If necessary, adds a submit button to the form
     */
    private function _addSubmit() {
        if (is_null($this->_form->getComponent('submit'))) {
            $formFactory = $this->_form->getFormFactory();
            $element = $formFactory->createSubmit('Submit');
            $element->setValue('Send')->addTo($this->_form);
        }
    }

    
     /**
     * 
     * @param ElementSpecs $elementSpecs
     */
    public function addSpecs($elementSpecs, $params = \NULL) {
        if (!$elementSpecs instanceof ElementSpecs) {
            $elementSpecs = new ElementSpecs($elementSpecs, $params);
        }
        $index = $elementSpecs->getName();
        $this->_elementTypes[$index] = $elementSpecs;
    }
}

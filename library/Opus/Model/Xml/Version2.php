<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category    Framework
 * @package     Opus_Model
 * @author      Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Second implementation of Opus XML representation.
 */
class Opus_Model_Xml_Version2 implements Opus_Model_Xml_Strategy {

    /**
     * Holds current configuration.
     *
     * @var Opus_Model_Xml_Conf
     */
    private $_config;

    /**
     * Holds current representation version.
     *
     * @var string
     */
    private $_version = '2.0';

    /**
     * Use the given element to create a model instance. If a constructor attribute map is set
     * the object creation incorporates using constructor arguments from the XML element.
     *
     * If an Xlink Resolver is configured an occurance of xlink:href will be used to fetch
     * a Model instance from the specified URL.
     *
     * @param DOMElement $element   Element to use for model creation.
     * @param string     $classname (Optional) Class name of class to be created. If not given, the node name is used.
     * @throws Opus_Model_Exception Thrown if the model reffered to by the elements name is unknown.
     * @return Opus_Model_Abstract Created model
     */
    protected function _createModelFromElement(DOMElement $element, $classname = null) {
        if (null === $classname) {
            $classname = $element->nodeName;
        }
        if (false === class_exists($classname)) {
            throw new Opus_Model_Exception('Model class ' . $classname . ' not known.');
        }

        // When xlink:href given use resolver to obtain model
        $ref = $element->attributes->getNamedItem('href');
        if ((null !== $this->_config->_xlinkResolver) and (null !== $ref)) {
            $model = $this->_config->_xlinkResolver->get($ref->value);
            return $model;
        }

        // Handle constructor attributes
        if (true === array_key_exists($classname, $this->_config->_constructionAttributesMap)) {
            $init = array();
            foreach ($this->_config->_constructionAttributesMap[$classname] as $constructorElement) {
                if (null !== $constructorElement) {
                    // get child node that has nodeName equal to constructor element
                    $childElements = $element->childNodes;
                    foreach ($childElements as $childElement) {
                        if (XML_ELEMENT_NODE === $childElement->nodeType) {
                            if ($constructorElement === $childElement->nodeName) {
                                $init[] = $childElement->nodeValue;
                                // remove constructor element from element list
                                $element->removeChild($childElement);
                            }
                        }
                    }
                } else {
                    $init[] = null;
                }
            }
            $creator = new ReflectionClass($classname);
            $model = $creator->newInstanceArgs($init);
        } else {
            $model = new $classname;
        }

        return $model;
    }

    /**
     * If there is a mapping for a model available a xlink:href string is created.
     *
     * @param Opus_Model_Abstract $model Model to link.
     * @return null|string Returns a string or null if no mapping is available
     */
    protected function _createXlinkRef(Opus_Model_Abstract $model) {
        // detect wether the model is persistent and shall be represented as xlink
        $uri = null;

        // determine the real model class name (there might be an link model in between)
        $valueModelClassName = get_class($model);
        if ($model instanceof Opus_Model_Dependent_Link_Abstract) {
            $valueModelClassName = $model->getModelClass();
        }

        // is there a mapping from class name to resource name?
        if (true === array_key_exists($valueModelClassName, $this->_config->_resourceNameMap)) {
            // is the model a persisted database object?
            if ($model instanceof Opus_Model_AbstractDb) {

                // return associated model id if $model is a link model
                if ($model instanceof Opus_Model_Dependent_Link_Abstract) {
                    $modelId = $model->getLinkedModelId();
                } else {
                    $modelId = $model->getId();
                }

                if (null !== $modelId) {
                    $resourceName = $this->_config->_resourceNameMap[$valueModelClassName];
                    $uri = $this->_config->_baseUri . '/' . $resourceName . '/' . $modelId;
                }
            }
        }
        return $uri;
    }

    /**
     * Maps attribute model informations to a DOMDocument.
     *
     * @param Opus_Model_Abstract $model      Model informations for attribute mapping.
     * @param DOMDocument         $dom        General DOM document.
     * @param DOMNode             $rootNode   Node where to add created structure.
     * @param boolean             $unTunneled Should only current (true) or all (false, default) fields shown.
     * @return void
     */
    protected function _mapAttributes(Opus_Model_Abstract $model, DOMDocument $dom, DOMNode $rootNode, $unTunneled = false) {

        if ((true === $unTunneled) and ($model instanceOf Opus_Model_Dependent_Link_Abstract)) {
            $fields = $model->describeUntunneled();
        } else if ((true === $unTunneled) and ($model instanceOf Opus_Model_Dependent_Abstract)) {
            $fields = array();
        } else {
            $fields = $model->describe();
        }
        $excludeFields = $this->_config->_excludeFields;
        if (count($excludeFields) > 0) {
            $fields_diff = array_diff($fields, $excludeFields);
        } else {
            $fields_diff = $fields;
        }

        foreach ($fields_diff as $fieldname) {
            $field = $model->getField($fieldname);
            $this->_mapField($field, $dom, $rootNode);
        }
    }

    /**
     * Map field information to a DOMDocument.
     *
     * @param Opus_Model_Field $field    Contains informations about mapping field.
     * @param DOMDocument      $dom      General DOM document.
     * @param DOMNode          $rootNode Node where to add created structure.
     * @return void
     */
    protected function _mapField(Opus_Model_Field $field, DOMDocument $dom, DOMNode $rootNode) {

        $fieldName = $field->getName();
        $modelClass = $field->getValueModelClass();
        $fieldValues = $field->getValue();

        if ((true === empty($fieldValues)) and (true === $this->_config->_excludeEmpty)) {
            return;
        }

        if (null === $modelClass) {
            // create a new element
            $element = $dom->createElement($fieldName);
            // workaround for simple fields with multiple values
            if (true === $field->hasMultipleValues()) {
                $fieldValues = implode(',', $fieldValues);
            }
            if ($fieldValues instanceOf Zend_Date) {
                $fieldValues = $fieldValues->getIso();
            }
            // set value
            //if (empty($fieldValues) === false)
            $element->nodeValue = $fieldValues;
            $rootNode->appendChild($element);
        } else {
            if (false === is_array($fieldValues)) {
                $fieldValues = array($fieldValues);
            }

            foreach ($fieldValues as $value) {
                $childNode = $dom->createElement($fieldName);
                $rootNode->appendChild($childNode);

                // if a field has no value then is nothing more to do
                // TODO maybe must be there an other solution
                if (null === $value) {
                    continue;
                }

                // delivers a URI if a mapping for the given model exists
                $uri = $this->_createXlinkRef($value);

                if (null !== $uri) {
                    $childNode->setAttribute('xlink:type', 'simple');
                    $childNode->setAttribute('xlink:href', $uri);
                    $this->_mapAttributes($value, $dom, $childNode, true);
                } else {
                    $this->_mapAttributes($value, $dom, $childNode);
                }
            }
        }
    }

    /**
     * Maps model information to a DOMDocument.
     *
     * @param Opus_Model_Abstract $model    Contains model information of mapping.
     * @param DOMDocument         $dom      General DOM document.
     * @param DOMNode             $rootNode Node where to add created structure.
     * @return void
     */
    protected function _mapModel(Opus_Model_Abstract $model, DOMDocument $dom, DOMNode $rootNode) {
        $fields = $model->describe();
        $excludeFields = $this->_config->_excludeFields;
        if (count($excludeFields) > 0) {
            $fields_diff = array_diff($fields, $excludeFields);
        } else {
            $fields_diff = $fields;
        }

        $childNode = $dom->createElement(get_class($model));
        $rootNode->appendChild($childNode);

        foreach ($fields_diff as $fieldname) {
            $field = $model->getField($fieldname);
            $this->_mapField($field, $dom, $childNode);
        }
    }

    /**
     * Recursively populates model's fields from an Xml DomElement.
     *
     * @param  Opus_Model_Abstract  $model   The model to be populated.
     * @param  DOMElement           $element The DomElement holding the field names and values.
     * @return Opus_Model_Abstract  $model   The populated model.
     */
    protected function _populateModelFromXml(Opus_Model_Abstract $model, DOMElement $element) {
        $fieldList = $model->describe();

        // fields exist as child elements
        foreach ($element->childNodes as $fieldNode) {

            // skip non-element nodes
            if (XML_ELEMENT_NODE !== $fieldNode->nodeType) {
                continue;
            }

            $fieldName = $fieldNode->nodeName;
            $fieldValue = $fieldNode->nodeValue;

            if (in_array($fieldName, $fieldList) === false) {
                throw new Opus_Model_Exception('Field ' . $fieldName . ' not defined. Model class: '
                    . get_class($model));
            } else {
                $fieldObj = $model->getField($fieldName);
                $modelclass = $fieldObj->getValueModelClass();
                // determine accessor function
                if (true === $fieldObj->hasMultipleValues()) {
                    $accessor = 'add';
                } else {
                    $accessor = 'set';
                }

                // omit setting values if XML node has no child nodes
                // neither XML_ELEMENT_TEXT nor XML_ELEMENT_NODE
                if (true === $fieldNode->hasChildNodes()) {
                    if (null !== $modelclass) {
                        $submodel = $this->_createModelFromElement($fieldNode, $modelclass);
                        $callname = $accessor . $fieldName;
                        // TODO better handling of accessor methods
                        if ('add' === $accessor) {
                            // if we add values then we need to do this on the returned model
                            $tempModel = $model->$callname($submodel);
                            $this->_populateModelFromXml($tempModel, $fieldNode);
                        } else {
                            // setting of values should be done on submodel
                            $model->$callname($submodel);
                            $this->_populateModelFromXml($submodel, $fieldNode);
                        }
                    } else {
                        $callname = $accessor . $fieldName;
                        $model->$callname($fieldValue);
                    }
                }
            }
        }
        return $model;
    }

    /**
     * Initiate class with a valid config object.
     */
    public function __construct() {
        $this->_config = new Opus_Model_Xml_Conf;
    }

    /**
     * (non-PHPdoc)
     * @see library/Opus/Model/Xml/Opus_Model_Xml_Strategy#getDomDocument()
     */
    public function getDomDocument() {
        if (null === $this->_config->_model) {
            throw new Opus_Model_Exception('No Model given for serialization.');
        }

        $this->_config->_dom = new DomDocument('1.0', 'UTF-8');
        $root = $this->_config->_dom->createElement('Opus');
        $root->setAttribute('version', $this->_version);
        $this->_config->_dom->appendChild($root);
        $root->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');

        $this->_mapModel($this->_config->_model, $this->_config->_dom, $root);

        return $this->_config->_dom;
    }

    /**
     * (non-PHPdoc)
     * @see library/Opus/Model/Xml/Opus_Model_Xml_Strategy#getModel()
     */
    public function getModel() {
        if (null !== $this->_config->_dom) {
            $root = $this->_config->_dom->getElementsByTagName('Opus')->item(0);
            if (null === $root) {
                throw new Opus_Model_Exception('Root element "Opus" not found.');
            }
            $model = $this->_createModelFromElement($root->firstChild);
            $this->_config->_model = $this->_populateModelFromXml($model, $root->firstChild);
        }
        return $this->_config->_model;

    }

    /**
     * (non-PHPdoc)
     * @see library/Opus/Model/Xml/Opus_Model_Xml_Strategy#getVersion()
     */
    public function getVersion() {
        return floor($this->_version);
    }

    /**
     * (non-PHPdoc)
     * @see library/Opus/Model/Xml/Opus_Model_Xml_Strategy#setDomDocument()
     */
    public function setDomDocument(DomDocument $dom) {
        $this->_config->_dom = $dom;
    }

    /**
     * (non-PHPdoc)
     * @see library/Opus/Model/Xml/Opus_Model_Xml_Strategy#setup()
     */
    public function setup(Opus_Model_Xml_Conf $conf) {
        $this->_config = $conf;
    }

    /**
     * (non-PHPdoc)
     * @see library/Opus/Model/Xml/Opus_Model_Xml_Strategy#setXml()
     */
    public function setXml($xml) {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        // Disable libxml error reporting because it generates warnings
        // wich will be ignored in production but turned into an exception
        // in PHPUnit environments
        libxml_use_internal_errors(true);
        $success = $dom->loadXml($xml);
        if (false === $success) {
            $errors = libxml_get_errors();
            foreach ($errors as $error) {
                $errmsg = $error->message . "\n";
            }
            libxml_clear_errors();
            throw new Opus_Model_Exception($errmsg);
        }
        $this->setDomDocument($dom);
    }

    /**
     * (non-PHPdoc)
     * @see library/Opus/Model/Xml/Opus_Model_Xml_Strategy#updateFromXml()
     */
    public function updateFromXml($xml) {
        //
    }
}
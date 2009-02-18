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
 * @author      Felix Ostrowski (ostrowski@hbz-nrw.de)
 * @author      Ralf Claußnitzer (ralf.claussnitzer@slub-dresden.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Abstract class for all domain models in the Opus framework.
 * It implements set and get accessors for field handling and rendering
 * of an array and an xml representation as well.
 *
 * @category    Framework
 * @package     Opus_Model
 */

abstract class Opus_Model_Abstract
{

    /**
     * Holds all fields of the domain model.
     *
     * @var array
     */
    protected $_fields = array();

    /**
     * Array of validator prefixes used to instanciate validator classes for fields.
     *
     * @var array
     */
    protected $_validatorPrefix = array('Opus_Validate');

    /**
     * Array of filter prefixes used to instanciate filter classes for fields.
     *
     * @var array
     */
    protected $_filterPrefix = array('Opus_Filter');

    /**
     * @TODO: Change name of this array to somewhat more general.
     * @TODO: Not enforce existence of custom _fetch and _store methods in Opus_Model_AbstractDb.
     *
     * In this array extra information for each field of the model can be
     * given, such like the classname of a referenced model object or specific options.
     *
     * It is an associative array referencing an declaration array for each field.
     *
     * 'MyField' => array(
     *          'model' => 'Opus_Model_Dependent_Title',
     *          'options' => array('type' => 'main'))
     *
     * @var array
     */
    protected $_externalFields = array();

    /**
     * @TODO: This should be an option in externalFields[].
     *
     * Fields to be not reported by describe().
     *
     * @var array
     */
    protected $_hiddenFields = array();

    /**
     * @TODO: Provide a more fine grained workflow by implementing pre and post operations.
     *
     * Start standard model initialization workflow:
     * 1 - _init();
     * 2 - _addValidators();
     * 3 - _addFilters();
     */
    public function __construct() {
        $this->_init();
        $this->_addValidators();
        $this->_addFilters();
    }

    /**
     * Overwrite to initialize custom fields.
     *
     * @return void
     */
    abstract protected function _init();


    /**
     * Add validators to the fields. Opus_Validate_{fieldname} classes are
     * expected to exist. The base classname prefixes are defined in $_validatorPrefix.
     *
     * @return void
     */
    protected function _addValidators() {
        foreach ($this->_fields as $fieldname => $field) {
            foreach ($this->_validatorPrefix as $prefix) {
                $classname = $prefix . '_' . $fieldname;
                // suppress warnings about not existing classes
                if (@class_exists($classname) === true) {
                    $field->setValidator(new $classname);
                    break;
                }
            }
        }
    }

    /**
     * Add filters to the fields. Opus_Filter_{fieldname} classes are
     * expected to exist. The base classname prefixes are defined in $_filterPrefix.
     *
     * @return void
     */
    protected function _addFilters() {
        foreach ($this->_fields as $fieldname => $field) {
            foreach ($this->_filterPrefix as $prefix) {
                $classname = $prefix . '_' . $fieldname;
                // suppress warnings about not existing classes
                if (@class_exists($classname) === true) {

                    $filter = $field->getFilter();
                    if (is_null($filter) === true) {
                        $filter = new Zend_Filter();
                        $field->setFilter($filter);
                    }
                    $filter->addFilter(new $classname);
                    break;
                }
            }
        }
    }

    /**
     * Magic method to access the models fields via virtual set/get methods.
     *
     * @param string $name      Name of the method beeing called.
     * @param array  $arguments Arguments for function call.
     * @throws Opus_Model_Exception If an unknown field or method is requested.
     * @throws InvalidArgumentException When adding a link to a field without an argument.
     * @return mixed Might return a value if a getter method is called.
     */
    public function __call($name, array $arguments) {
        $accessor = substr($name, 0, 3);
        $fieldname = substr($name, 3);

        $argumentModelGiven = false;
        if (empty($arguments) === false) {
            if (is_null($arguments[0]) === false) {
                $argumentModelGiven = true;
            }
        };

        if (array_key_exists($fieldname, $this->_fields) === false) {
            throw new Opus_Model_Exception('Unknown field: ' . $fieldname);
        }

        $fieldIsExternal = array_key_exists($fieldname, $this->_externalFields);
        if ($fieldIsExternal === true) {
            $fieldHasThroughOption = array_key_exists('through', $this->_externalFields[$fieldname]);
        }
        $field = $this->getField($fieldname);

        switch ($accessor) {
            case 'get':
                if (empty($arguments) === false) {
                    return $field->getValue($arguments[0]);
                } else {
                    return $field->getValue();
                }
                break;

            case 'set':
                if (empty($arguments) === true) {
                    throw new Opus_Model_Exception('Argument required for setter function!');
                }
                if (($fieldIsExternal === true)
                and ($fieldHasThroughOption === true)
                and ($argumentModelGiven === true)) {

                    $linkmodelclass = $this->_externalFields[$fieldname]['through'];
                    $linkmodel = new $linkmodelclass;

                    if (($arguments[0] instanceof Opus_Model_Dependent_Link_Abstract) === true) {
                        $linkmodel->setModel($arguments[0]->_model);
                    } else {
                        $linkmodel->setModel($arguments[0]);
                    }
                    $model = $linkmodel;

                } else {
                    $model = $arguments[0];
                }

                $field->setValue($model);
                return $field->getValue();
                break;

            case 'add':
                // get Modelclass if model is linked
                if ($fieldIsExternal and $fieldHasThroughOption === true) {

                    $linkmodelclass = $this->_externalFields[$fieldname]['through'];

                    // Check if $linkmodelclass is a known class name
                    if (class_exists($linkmodelclass) === false) {
                        throw new Opus_Model_Exception("Link model class '$linkmodelclass' does not exist.");
                    }
                    $linkmodel = new $linkmodelclass;

                    if ((count($arguments) === 1)) {
                        if (($arguments[0] instanceof Opus_Model_Dependent_Link_Abstract) === true) {
                            $linkmodel->setModel($arguments[0]->_model);
                        } else {
                            $linkmodel->setModel($arguments[0]);
                        }
                    } else {
                        throw new InvalidArgumentException('Argument required when adding to a link field.');
                    }
                    $model = $linkmodel;

                } else {
                    if ((count($arguments) === 1)) {
                        $model = $arguments[0];
                    } else {
                        if (is_null($field->getValueModelClass()) === true) {
                            throw new Opus_Model_Exception('Add accessor without parameter currently only available for fields holding models.');
                        }
                        $modelclass = $field->getValueModelClass();
                        $model = new $modelclass;
                    }
                }

                $field->addValue($model);
                return $model;
                break;

            default:
                throw new Opus_Model_Exception('Unknown accessor function: ' . $accessor);
                break;
        }

    }

    /**
     * Add an field to the model. If a field with the same name has already been added,
     * it will be replaced by the given field.
     *
     * @param Opus_Model_Field $field Field instance that gets appended to the models field collection.
     * @return Opus_Model_Abstract Provide fluent interface.
     */
    public function addField(Opus_Model_Field $field) {
        $fieldname = $field->getName();
        $this->_fields[$fieldname] = $field;

        // set Modelclass if a model exists
        if (array_key_exists($fieldname, $this->_externalFields) === true) {
            if (array_key_exists('model', $this->_externalFields[$fieldname]) === true) {
                $model = $this->_externalFields[$fieldname]['model'];
                $field->setValueModelClass($model);
            }
        }

        return $this;
    }

    /**
     * Return a reference to an actual field.
     *
     * @param string $name Name of the requested field.
     * @return Opus_Model_Field The requested field instance. If no such instance can be found, null is returned.
     */
    public function getField($name) {
        if (array_key_exists($name, $this->_fields) === true) {
            return $this->_fields[$name];
        } else {
            return null;
        }
    }

    /**
     * Get a list of all fields attached to the model. Filters all fieldnames
     * that are defined to be hidden in $_hiddenFields.
     *
     * @see    Opus_Model_Abstract::_hiddenFields
     * @return array    List of fields
     */
    public function describe() {
        $result = array();
        foreach (array_keys($this->_fields) as $fieldname) {
            if (in_array($fieldname, $this->_hiddenFields) === false) {
                $result[] = $fieldname;
            }
        }
        return $result;
    }

    /**
     * By default, the textual representation of a modeled entity is
     * its class name.
     *
     * @return string Model class name.
     */
    public function getDisplayName() {
        return get_class($this);
    }

    /**
     * Get a nested associative array representation of the model.
     *
     * @return array A (nested) array representation of the model.
     */
    public function toArray() {
        $result = array();
        foreach ($this->_fields as $fieldname => $field) {

            // Call to getField() to ensure fetching of pending fields.
            if (in_array($fieldname, $this->_pending) === true) {
                $field = $this->getField($fieldname);
            }

            if ($field->hasMultipleValues()) {
                $fieldvalues = array();
                foreach($field->getValue() as $value) {
                    if ($value instanceof Opus_Model_Abstract) {
                        $fieldvalues[] = $value->toArray();
                    } else {
                        $fieldvalues[] = $value;
                    }
                }
                $result[$fieldname] = $fieldvalues;
            } else {
                if ($field->getValue() instanceof Opus_Model_Abstract) {
                    $result[$fieldname] = $field->getValue()->toArray();
                } else {
                    $result[$fieldname] = $field->getValue();
                }
            }
        }
        return $result;
    }

    /**
     * Returns an Xml-string representation of the model.
     *
     * @return string A plain Xml-string representation of the model.
     */
    public function toXml() {
        $result = '<' . get_class($this);
        $result .= $this->_recurseXml();
        $result .= '</' . get_class($this) . '>';
        return $result;
    }

    /**
     * Recurses over the model's field to generate an Xml-string.
     *
     * @return string A plain Xml-string representation of the model.
     */
    protected function _recurseXml() {
        $result = '';

        // Iterate internal fields and add them as attributes
        foreach ($this->_fields as $fieldname => $field) {
            if (array_key_exists($fieldname, $this->_externalFields) === true) {
                continue;
            }

            // Call to getField() to ensure fetching of pending fields.
            if (in_array($fieldname, $this->_pending) === true) {
                $field = $this->getField($fieldname);
            }

            if ($field->hasMultipleValues()) {
                foreach($field->getValue() as $value) {
                    $result .= ' ' . $fieldname . '=' . '"' . $value . '"';
                }
            } else {
                $result .= ' ' . $fieldname . '=' . '"' . $field->getValue() . '"';
            }
        }

        $result .= '>';

        // Iterate external fields and add them as child elements
        foreach ($this->_fields as $fieldname => $field) {
            if (array_key_exists($fieldname, $this->_externalFields) === false) {
                continue;
            }
            // Call to getField() to ensure fetching of pending fields.
            if (in_array($fieldname, $this->_pending) === true) {
                $field = $this->getField($fieldname);
            }

            if ($field->hasMultipleValues()) {
                foreach($field->getValue() as $value) {
                    if ($value instanceof Opus_Model_Abstract) {
                        $result .= '<' . $fieldname . $value->_recurseXml() . '</' . $fieldname . '>';
                    }
                }
            } else {
                if ($field->getValue() instanceof Opus_Model_Abstract) {
                    $result .= '<' . $fieldname . $field->getValue()->_recurseXml() . '</' . $fieldname . '>';
                }
            }
        }

        return $result;
    }

}

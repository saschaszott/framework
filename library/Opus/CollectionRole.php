<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @package     Opus
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @author      Felix Ostrowski <ostrowski@hbz-nrw.de>
 * @author      Tobias Tappe <tobias.tappe@uni-bielefeld.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2010-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus;

use Opus\Db\Collections;
use Opus\Db\TableGateway;
use Opus\Model\AbstractDb;
use Opus\Model\Field;
use Opus\Validate\CollectionRoleName;

/**
 * Domain model for collection roles in the Opus framework
 *
 * Fields:
 *
 * DisplayBrowsing
 * DisplayFrontdoor
 * Acceptable values are:
 * - 'Name'
 * - 'Number'
 * - 'Name, Number'
 * - 'Number, Name'
 *
 *
 *
 * @category    Framework
 * @package     Opus
 * @uses        \Opus\Model\AbstractModel
 *
 * @method void setName(string $name)
 * @method string getName()
 *
 * @method void setOaiName(string $oaiName)
 * @method string getOaiName()
 *
 * @method void setPosition(integer $pos)
 * @method integer getPosition()
 *
 * @method void setVisible(boolean $visible)
 * @method boolean getVisible()
 *
 * @method void setVisibleBrowsingStart(boolean $visibleBrowsingStart)
 * @method boolean getVisibleBrowsingStart()
 *
 * @method void setVisibleFrontdoor(boolean $visibleFrontdoor)
 * @method boolean getVisibleFrontdoor()
 *
 * @method void setVisibleOai(boolean $visibleOai)
 * @method boolean getVisibleOai()
 *
 * @method void setDisplayBrowsing(string $format)
 * @method string getDisplayBrowsing()
 *
 * @method void setDisplayFrontdoor(string $format)
 * @method string getDisplayFrontdoor()
 *
 * @method void setIsClassification(boolean $isClassification)
 * @method boolean getIsClassification()
 *
 * @method void setAssignRoot(boolean $assignRoot)
 * @method boolean getAssignRoot()
 *
 * @method void setAssignLeavesOnly(boolean $assignLeavesOnly)
 * @method boolean getAssignLeavesOnly()
 *
 * @method void setRootCollection(Collection $collection)
 * @method Collection getRootCollection()
 *
 * @method void setHideEmptyCollections(boolean $hideEmptyCollections)
 * @method boolean getHideEmptyCollections()
 *
 * @method void setLanguage(string $language)
 * @method string getLanguage()
 */
class CollectionRole extends AbstractDb
{

    /**
     * Specify then table gateway.
     *
     * @var string Classname of \Zend_DB_Table to use if not set in constructor.
     */
    protected static $_tableGatewayClass = 'Opus\Db\CollectionsRoles';

    /**
     * The documents external fields, i.e. those not mapped directly to the
     * Opus\Db\Documents table gateway.
     *
     * @var array
     * @see \Opus\Model\Abstract::$_externalFields
     */
    protected $_externalFields = [
        // Will contain the Root Node of this Role.
        'RootCollection' => [
            'model' => 'Opus\Collection',
            'options' => ['left_id' => 1],
            'fetch' => 'lazy',
        ],
    ];

    /**
     * Plugins to load
     *
     * @var array
     */
    public function getDefaultPlugins()
    {
        return [
            'Opus\Model\Plugin\InvalidateDocumentCache',
            'Opus\CollectionRole\Plugin\DeleteTree'
        ];
    }

    /**
     * Initialize model.
     *
     * @return void
     */
    protected function _init()
    {
        // Attributes, which are defined by the database schema.
        $name = new Field('Name');
        $name->setMandatory(true)->setValidator(new CollectionRoleName());
        $this->addField($name);

        $oaiName = new Field('OaiName');
        $oaiName->setMandatory(true)->setValidator(new \Zend_Validate_NotEmpty());
        $this->addField($oaiName);

        $position = new Field('Position');
        $this->addField($position);


        // Attributes for defining visibility.
        $visible = new Field('Visible');
        $visible->setCheckbox(true);
        $this->addField($visible);

        $visibleBrowsingStart = new Field('VisibleBrowsingStart');
        $visibleBrowsingStart->setCheckbox(true);
        $this->addField($visibleBrowsingStart);

        $visibleFrontdoor = new Field('VisibleFrontdoor');
        $visibleFrontdoor->setCheckbox(true);
        $this->addField($visibleFrontdoor);

        $visibleOai = new Field('VisibleOai');
        $visibleOai->setCheckbox(true);
        $this->addField($visibleOai);


        // Attributes for defining output formats.
        $displayBrowsing = new Field('DisplayBrowsing');
        $this->addField($displayBrowsing);

        $displayFrontdoor = new Field('DisplayFrontdoor');
        $this->addField($displayFrontdoor);

        $isClassification = new Field('IsClassification');
        $this->addField($isClassification);

        $assignRoot = new Field('AssignRoot');
        $this->addField($assignRoot);

        $assignLeavesOnly = new Field('AssignLeavesOnly');
        $this->addField($assignLeavesOnly);

        // Virtual attributes, which depend on other tables.
        $rootCollection = new Field('RootCollection');
        $this->addField($rootCollection);

        // Attribute to determine visibility of empty collections
        $hideEmptyCollections = new Field('HideEmptyCollections');
        $hideEmptyCollections->setCheckbox(true);
        $this->addField($hideEmptyCollections);

        $language = new Field('Language');
        $this->addField($language);
    }

    /**
     * Returns long name.
     *
     * @see \Opus\Model\Abstract#getDisplayName()
     * @return string Model class name and identifier (e.g. Opus\CollectionRole#1234).
     *
     * TODO: Outsource this->getName to this->getRootNode->getName
     */
    public function getDisplayName()
    {
        return $this->getName();
    }

    /**
     * Fixes ordering of all CollectionRoles by re-numbering position columns.
     *
     * @return void
     */
    public static function fixPositions()
    {
        $table = TableGateway::getInstance(self::$_tableGatewayClass);
        $db = $table->getAdapter();

        // FIXME: Hardcoded table and column names.
        $reorderQuery = 'SET @pos = 0; '
                . ' UPDATE collections_roles '
                . ' SET position = ( SELECT @pos := @pos + 1 ) '
                . ' ORDER BY position, id ASC;';
        // echo "reorder: $reorder_query\n";
        $db->query($reorderQuery);

        return;
    }

    /**
     * Returns position number of last collection role.
     * @return int Highest used position number for collection roles
     */
    public static function getLastPosition()
    {
        $table = TableGateway::getInstance(self::$_tableGatewayClass);
        $db = $table->getAdapter();

        $query = 'SELECT MAX(position) FROM collections_roles;';

        $result = $db->fetchOne($query);

        return ( int )$result;
    }

    /**
     * Overwrite standard storage procedure to shift positions.  The parameter
     * describes the new position of the current role.
     *
     * TODO: This method belongs to Opus\Db\CollectionsRoles.
     * TODO: Make sure this method only gets called if the field changed.
     *
     * @param integer $to Target position after saving.
     *
     * @return void
     */
    protected function _storePosition($to = PHP_INT_MAX)
    {
        $field = $this->_getField('Position', true);
        if (false === $field->isModified()) {
            return;
        }

        $to = (int) $to;
        if ($to < 1) {
            $to = 1;
        }

        $row = $this->_primaryTableRow;
        $db = $row->getTable()->getAdapter();

        // Re-Order.
        // TODO: This reorder-query is only nesseccary, if someone destroyed the
        // TODO: strict ordering.  If the table is strictly ordered, then the
        // TODO: code below will preserve this property.
        self::fixPositions();

        // Find the current position of the current row in the new ordering.
        // Case 1: If row is new, shift all nodes plus one.
        // Case 2: If row is old, shift nodes in between plus/minus one.
        $range = $db->quoteInto("position >= ?", $to);
        $posShift = ' + 1 ';

        if (! $this->isNewRecord()) {
            $posQuery = 'SELECT position FROM collections_roles WHERE id = ?';
            $pos = $db->fetchOne($posQuery, $this->getId());

            $range = "position BETWEEN ? AND ?";
            if ($to < $pos) {
                $range = $db->quoteInto($range, $to, null, 1);
                $range = $db->quoteInto($range, $pos, null, 1);
                $posShift = ' + 1 ';
            } else {
                $range = $db->quoteInto($range, $pos, null, 1);
                $range = $db->quoteInto($range, $to, null, 1);
                $posShift = ' - 1 ';
            }
        }

        // Move.
        $moveQuery = 'UPDATE collections_roles '
                . ' SET position = position ' . $posShift
                . ' WHERE ' . $range;
        $db->query($moveQuery);

        // Update this row.
        $row->{'position'} = (int) $to;

        return;
    }

    /**
     * Overwrites standard toArray() to prevent infinite recursion due to parent
     * collections.
     *
     * @return array A (nested) array representation of the model.
     * @deprecated Method shouldn't be used any more.  Use object or xml.
     *
     * TODO Check why this method isn't used any more.
     * TODO What should be the behaviour of this function.
     */
    public function toArray()
    {
        $result = parent::toArray();

        $root = $this->getRootCollection();

        if (isset($root)) {
            $collections = [];

            foreach ($root->getChildren() as $child) {
                $collections[] = [
                    'Id' => $child->getId(),
                    'Name' => $child->getName(),
                ];
            }

            $result['RootCollection'] = $collections;
        }

        return $result;
    }

    /**
     * ALTERNATE CONSTRUCTOR: Retrieve Opus\CollectionRole instance by name.
     * Returns null if name is null *or* nothing found.
     *
     * @param string $name Name of collection role to look for.
     *
     * @return CollectionRole
     */
    public static function fetchByName($name = null)
    {
        if (false === isset($name)) {
            return;
        }

        $table = TableGateway::getInstance(self::$_tableGatewayClass);
        $select = $table->select()->where('name = ?', $name);
        $row = $table->fetchRow($select);

        if (isset($row)) {
            return new CollectionRole($row);
        }

        return;
    }

    /**
     * ALTERNATE CONSTRUCTOR: Retrieve Opus\CollectionRole instance by oaiName.
     * Returns null if name is null *or* nothing found.
     *
     * TODO: Return Opus\Model\NotFoundException?
     *
     * @param string $oaiName OaiName of collection role to look for.
     *
     * @return CollectionRole
     */
    public static function fetchByOaiName($oaiName = null)
    {
        if (false === isset($oaiName)) {
            return;
        }

        $table = TableGateway::getInstance(self::$_tableGatewayClass);
        $select = $table->select()->where('oai_name = ?', $oaiName);
        $row = $table->fetchRow($select);

        if (isset($row)) {
            return new CollectionRole($row);
        }

        return;
    }

    /**
     * Retrieve all Opus\CollectionRole instances from the database.
     *
     * @return array Array of Opus\CollectionRole objects.
     *
     * TODO: Modify self::getAllFrom to take parameters.
     */
    public static function fetchAll()
    {
        // $roles = self::getAllFrom('Opus\CollectionRole', self::$_tableGatewayClass);
        $table = TableGateway::getInstance(self::$_tableGatewayClass);
        $roles = $table->fetchAll(null, 'position');
        return self::createObjects($roles);
    }

    /**
     * Mass-constructur.
     *
     * @param array $array Array of whatever new Collection(...) takes.
     *
     * @return array|Collection Constructed Opus\Collection(s).
     *
     * TODO: Refactor this method as fetchAllFromSubselect(...) in AbstractDb?
     * TODO: Code duplication from/in Opus\Collection!
     */
    public static function createObjects($array)
    {
        $results = [];

        // FIXME: get_called_class() only supported in PHP5 >= 5.3
        //   $class   = get_called_class();
        // FIXME: Add Model_AbstractDb::createObjects(...) when using PHP 5.3

        foreach ($array as $element) {
            $c = new CollectionRole($element);
            $results[] = $c;
        }

        return $results;
    }

    /* ********************************************************************** *
     * Everything which deals with OAI sets goes here:
     * ********************************************************************** */

    /**
     * Fetches all valid/visible/non-empty oai subset names for this role,
     * (i.e. for those collections that  contain at least one document, are
     * visible, have a proper oai name).
     *
     * FIXME: Unit-tests, if empty OaiSets are returned.
     *
     * @return array Array-hash with (id, name, oai_name, count)
     *
     * @see modules/oai/controllers/IndexController.php
     */
    public function getOaiSetNames()
    {
        if (is_null($this->getId())) {
            return [];
        }

        $select = "SELECT c.id, c.oai_subset, c.number, c.name,
                          count(DISTINCT l.document_id) AS count
            FROM link_documents_collections AS l,
                 collections AS c, documents AS d
            WHERE l.collection_id = c.id AND l.document_id = d.id
            AND c.role_id = ? AND d.server_state = 'published'
            AND c.visible = 1 AND c.oai_subset IS NOT NULL AND c.oai_subset != ''
            GROUP BY c.id, c.oai_subset, c.number, c.name";

        $db = \Zend_Db_Table::getDefaultAdapter();
        $select = $db->quoteInto($select, $this->getId());
        return $db->fetchAll($select);
    }

    /**
     * Fetches all oai subset names for this role.
     * @return array
     */
    public function getAllOaiSetNames()
    {
        if (is_null($this->getId())) {
            return [];
        }

        $select = "SELECT c.id, c.oai_subset, c.number, c.name
            FROM collections AS c
            WHERE c.role_id = ? AND c.visible = 1 AND c.oai_subset IS NOT NULL AND c.oai_subset != ''
            GROUP BY c.id, c.oai_subset, c.number, c.name";

        $db = \Zend_Db_Table::getDefaultAdapter();
        $select = $db->quoteInto($select, $this->getId());
        return $db->fetchAll($select);
    }

    /**
     * Returns all valid oai role (i.e. for those collection roles that contain
     * at least one visible collection with proper oai name and at least one
     * published document).
     *
     * The visibility and oai name of the root collection is derived from the
     * collection role (VisibleOai and OaiName).
     *
     * @return array Array-hash with (id, name, oai_name, count)
     *
     * @see modules/oai/controllers/IndexController.php
     *
     * TODO why does incomplete GROUP BY not cause an exception here (like in getOaiSetNames)?
     */
    public static function fetchAllOaiEnabledRoles()
    {
        $select = "SELECT r.id, r.name, r.oai_name,
                          count(DISTINCT l.document_id) AS count
            FROM link_documents_collections AS l, collections_roles AS r,
                 collections AS c, documents AS d
            WHERE l.collection_id = c.id
            AND l.document_id = d.id
            AND d.server_state = 'published'
            AND c.role_id = r.id
            AND ((c.visible = 1 AND c.oai_subset IS NOT NULL AND c.oai_subset != '') OR c.parent_id IS NULL)
            AND r.visible = 1
            AND r.visible_oai = 1
            AND r.oai_name IS NOT NULL
            AND r.oai_name != ''
            GROUP BY r.oai_name";

        $db = \Zend_Db_Table::getDefaultAdapter();
        return $db->fetchAll($select);
    }

    /**
     * Return the ids of documents in an oai set.
     *
     * @param  string $oaiSetName The name of the oai set.
     *
     * @return array The ids of the documents in the set.
     *
     * FIXME: Need Collection constructor-by-oaiSetName.
     * FIXME: Check OAI set names for invalid characters (i.e. ':')
     * FIXME: Belongs to Opus\Collection
     * FIXME: Code duplication from getDocumentIdsInSet.
     *
     * @see modules/oai/controllers/IndexController.php
     */
    public function existsDocumentIdsInSet($oaiSetName)
    {
        $colonPos = strrpos($oaiSetName, ':');
        $oaiPrefix = substr($oaiSetName, 0, $colonPos);
        $oaiPostfix = substr($oaiSetName, $colonPos + 1);

        if ($this->getOaiName() !== $oaiPrefix) {
            throw new \Exception("Given OAI prefix does not match this role.");
        }

        $oaiPrefix = $this->getOaiName();

        $db = \Zend_Db_Table::getDefaultAdapter();

        $quotePostfix = $db->quote("$oaiPostfix");
        $quoteRoleId = $db->quote($this->getId());

        $select = " SELECT count(c.id) FROM collections AS c "
                . " WHERE c.oai_subset = $quotePostfix "
                . " AND c.role_id = $quoteRoleId "
                . " AND EXISTS ( "
                . "    SELECT l.document_id "
                . "    FROM link_documents_collections AS l "
                . "    WHERE l.collection_id = c.id AND l.role_id = c.role_id "
                . " )";

        $db = \Zend_Db_Table::getDefaultAdapter();
        $result = $db->fetchOne($select);
        // $this->log("$oaiSetName: $result");

        if (isset($result) and $result > 0) {
            return true;
        }

        return false;
    }

    /**
     * Return the ids of documents in an oai set.
     *
     * @param  string $oaiSetName The name of the oai set.
     *
     * @return array              The ids of the documents in the set.
     *
     * FIXME: Replace method by something more general.
     * FIXME: Don't use internal knowledge from database.
     * FIXME: Make this method non-static.
     *
     * @see modules/oai/controllers/IndexController.php
     */
    public static function getDocumentIdsInSet($oaiSetName)
    {
        $colonPos = strrpos($oaiSetName, ':');
        $oaiPrefix = substr($oaiSetName, 0, $colonPos);
        $oaiPostfix = substr($oaiSetName, $colonPos + 1);

        $role = self::fetchByOaiName($oaiPrefix);
        if (is_null($oaiPrefix) === true) {
            throw new \Exception("Given OAI prefix does not exist in roles.");
        }

        $oaiPrefix = $role->getOaiName();

        $db = \Zend_Db_Table::getDefaultAdapter();

        $quotePostfix = $db->quote("$oaiPostfix");
        $quoteRoleId = $db->quote($role->getId());

        $subselect = "SELECT DISTINCT id FROM collections "
                . "   WHERE oai_subset = $quotePostfix "
                . "     AND role_id = $quoteRoleId "
                . "     AND visible = 1 ";

        $select = "SELECT DISTINCT document_id FROM link_documents_collections "
                . " WHERE role_id = $quoteRoleId "
                . "   AND collection_id IN ($subselect) ";

        $db = \Zend_Db_Table::getDefaultAdapter();
        $result = $db->fetchCol($select);
        // $role->log("$oaiSetName: #" . count($result));

        return $result;
    }

    /**
     * Returns collection for OAI subset.
     * @param $oaisubset
     */
    public function getCollectionByOaiSubset($oaisubset)
    {
        $db = \Zend_Db_Table::getDefaultAdapter();

        $quoteOaiSubset = $db->quote("$oaisubset");

        $select = "SELECT c.id
            FROM collections_roles AS r, collections AS c
            WHERE c.role_id = r.id
            AND c.oai_subset = $quoteOaiSubset";

        $result = $db->fetchOne($select);

        if ($result === false) {
            return null;
        } else {
            return new Collection($result);
        }
    }

    /* ********************************************************************** *
     * Everything which depends on $this->getRootNode() goes here:
     * ********************************************************************** */

    /**
     * Fetch-method for field "RootCollection".
     *
     * @return Collection
     */
    protected function _fetchRootCollection()
    {
        if ($this->isNewRecord()) {
            return;
        }

        $collections = TableGateway::getInstance(Collections::class);
        $root = $collections->getRootNode($this->getId());

        if (! isset($root)) {
            return;
        }

        return new Collection($root);
    }

    /**
     * Store root collection: Initialize Node.
     *
     * @param Collection $collection Collection to store as Root.
     *
     * @see AbstractDb
     */
    public function _storeRootCollection($collection)
    {
        if (! isset($collection)) {
            return;
        }

        if ($collection->isNewRecord()) {
            $collection->setPositionKey('Root');
            $collection->setRoleId($this->getId());
        }

        $collection->store();
    }

    /**
     * Extend magic add-method.  Add $collection if given; otherwise
     * create.
     *
     * @param Collection $collection (optional) collection object to add
     *
     * @return Collection
     */
    public function addRootCollection($collection = null)
    {
        if (isset($collection)) {
            $collection = parent::addRootCollection($collection);
        } else {
            $collection = parent::addRootCollection();
        }

        if ($collection->isNewRecord() and ! $this->isNewRecord()) {
            $collection->setPositionKey('Root');
            $collection->setRoleId($this->getId());
        }

        return $collection;
    }
}

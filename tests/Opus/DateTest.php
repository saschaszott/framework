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
 * @category    Tests
 * @package     Opus
 * @author      Ralf Claußnitzer (ralf.claussnitzer@slub-dresden.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Test cases for class Opus_Date.
 *
 * @package Opus
 * @category Tests
 *
 * @group DateTest
 */
class Opus_DateTest extends TestCase {

    protected $_locale_backup;

    /**
     * Prepare german locale setup.
     *
     */
    public function setUp() {
        $this->_locale_backup = Zend_Registry::get('Zend_Locale');
        Zend_Registry::set('Zend_Locale', new Zend_Locale('de'));
    }
    
    /**
     * Restore previously set locale
     *
     */
    public function tearDown() {
        Zend_Registry::set('Zend_Locale', $this->_locale_backup);
    }

    /**
     * Test creation of a Opus_Date model.
     *
     * @return void
     */
    public function testCreate() {
        $od = new Opus_Date;
    }   
 
    /**
     * Test if a valid Zend_Date object can be created.
     *
     * @return void
     */   
    public function testGetZendDate() {
        $od = new Opus_Date;
        $od->setYear(2005)
            ->setMonth(10)
            ->setDay(24);
        $zd = $od->getZendDate();
        
        $this->assertNotNull($zd, 'Object expected.');
        $this->assertTrue($zd instanceof Zend_Date, 'Returned object is not Zend_Date.');
    }
    
    /**
     * Test creation by passing string as constructor argument.
     *
     * @return void
     */
    public function testCreateWithStringConstructionArgument() {
        $od = new Opus_Date('1972-11-10');
        $this->assertEquals(1972, (int) $od->getYear(), 'Year values dont match.');        
        $this->assertEquals(11, (int) $od->getMonth(), 'Month values dont match.');        
        $this->assertEquals(10, (int) $od->getDay(), 'Day values dont match.');        
    }

    /**
     * Test creation by passing Zend_Date as constructor argument.
     *
     * @return void
     */
    public function testCreateWithZendDateConstructionArgument() {
        $now = new Zend_Date;
        $od = new Opus_Date($now);
        $this->assertEquals($od->getYear(), $now->get(Zend_Date::YEAR), 'Year values dont match.');
        $this->assertEquals($od->getMonth(), $now->get(Zend_Date::MONTH), 'Month values dont match.');
        $this->assertEquals($od->getDay(), $now->get(Zend_Date::DAY), 'Day values dont match.');
    }

    /**
     * Test creation by passing Opus_Date as constructor argument.
     *
     * @return void
     */
    public function testCreateWithOpusDateConstructionArgument() {
        $now = new Opus_Date;
        $now->setNow();
        $od = new Opus_Date($now);
        $this->assertEquals($od->getYear(), $now->getYear(), 'Year values dont match.');
        $this->assertEquals($od->getMonth(), $now->getMonth(), 'Month values dont match.');
        $this->assertEquals($od->getDay(), $now->getDay(), 'Day values dont match.');
    }

    /**
     * Test creation by passing DateTime as constructor argument.
     *
     * @return void
     */
    public function testCreateWithDateTimeConstructionArgument() {
        $now = new DateTime;
        $od = new Opus_Date($now);
        $this->assertEquals($od->getYear(), $now->format('Y'), 'Year values dont match.');
        $this->assertEquals($od->getMonth(), $now->format('m'), 'Month values dont match.');
        $this->assertEquals($od->getDay(), $now->format('d'), 'Day values dont match.');
    }

    /**
     * Test if Opus_Date swaps month/year when locale == en
     *
     * @return void
     */
    function testIfParsingOfIsoDateSwapsDayAndMonth() {
        $locale = new Zend_Locale("en");
        Zend_Registry::set('Zend_Locale', $locale);
        $date = new Opus_Date('2010-06-04T02:36:53Z');

        $this->assertEquals(4, $date->getDay());
        $this->assertEquals(6, $date->getMonth());
    }

    /**
     * Test if setNow really sets now.
     *
     * @return void
     */
    function testSetNow() {
        $date = new Opus_Date();
        $date->setNow();

        $this->assertEquals(date('Y'), $date->getYear());
        $this->assertEquals(date('m'), $date->getMonth());
        $this->assertEquals(date('d'), $date->getDay());
    }

    /**
     * Test if converting from-to string is invariant.
     *
     * @return void
     */
    function testFromStringToStringIsInvariant() {
        $date = new Opus_Date();
        $date->setFromString('2010-06-04T22:36:53Z');

        $this->assertEquals(2010, $date->getYear());
        $this->assertEquals(06, $date->getMonth());
        $this->assertEquals(04, $date->getDay());

        $this->assertEquals(22, $date->getHour());
        $this->assertEquals(36, $date->getMinute());
        $this->assertEquals(53, $date->getSecond());

        $this->assertEquals('2010-06-04T22:36:53+00:00', "$date");
    }

    /**
     * Test if converting from-to string is invariant.
     *
     * @return void
     */
    function testFromStringToStringKeepsTimeZone() {
        $date = new Opus_Date();
        $date->setFromString('2010-06-04T22:36:53+2:3');

        $this->assertEquals(2010, $date->getYear());
        $this->assertEquals(06, $date->getMonth());
        $this->assertEquals(04, $date->getDay());

        $this->assertEquals(22, $date->getHour());
        $this->assertEquals(36, $date->getMinute());
        $this->assertEquals(53, $date->getSecond());

        $this->assertEquals('2010-06-04T22:36:53+02:03', "$date");
    }

    /**
     * Test if setFromString() handles broken dates correctly.
     *
     * @return void
     */
    function testSetFromStringErrorHandling() {

        $invalidStrings = array(
            '',
            null,
            '2010',
            '2011-12-bla',
            '01.01.2010',
            '2011-12-12T23:59:59',
            '2011-12-12X99:99:99Z',
        );
        foreach ($invalidStrings AS $invalidString) {
            try {
                $date = new Opus_Date();
                $date->setFromString($invalidString);
                $this->fail("Missing expected InvalidArgumentException for invalid string '{$invalidString}'.");
            }
            catch (InvalidArgumentException $e) {
                // OK.
            }
        }

    }

    /**
     * Test if setFromString() handles invalid time zone parameter.
     *
     * @return void
     */
    function testSetTimezoneErrorHandling() {

        $invalidStrings = array(
            null,
            new stdClass(),
            '',
            'bla',
        );
        foreach ($invalidStrings AS $invalidString) {
            try {
                $date = new Opus_Date();
                $date->setTimezone($invalidString);
                $this->fail("Missing expected InvalidArgumentException for invalid timezone '{$invalidString}'.");
            }
            catch (InvalidArgumentException $e) {
                // OK.
            }
        }

    }

}

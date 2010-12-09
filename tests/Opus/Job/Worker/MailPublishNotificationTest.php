<?php
/*
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
 * @category    Framework Unit Test
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Opus_Job_Worker_MailPublishNotificationTest extends TestCase {

    protected function setUp() {
        parent::setUp();

        $account = new Opus_Account();
        $account->setLogin('admin')
            ->setPassword('foobar-'.rand())
            ->store();

        $account = new Opus_Account();
        $account->setLogin('hasmail')
            ->setPassword('foobar-'.rand())
            ->setEmail('has@mail.de')
            ->store();
    }

    /**
     * Tests getting global recipients.
     */
    public function testGetGlobalRecipients() {
        $mail = new Opus_Job_Worker_MailPublishNotification();

        // Only one user exists *and* has an email address
        $recipients = $mail->getGlobalRecipients();
        $this->assertNotNull($recipients);
        $this->assertEquals(1, count($recipients));

        // The uses is called "hasemail" and his mail is "has@mail.de"
        $this->assertEquals('hasmail', $recipients[0]['login']);
        $this->assertEquals('has@mail.de', $recipients[0]['address']);
    }

    /**
     * Tests getting recipients (from empty list)
     */
    public function testGetRecipientsForEmptyList() {
        $mail = new Opus_Job_Worker_MailPublishNotification();
        $recipients = $mail->getRecipients();
        $this->assertNotNull($recipients);
        $this->assertEquals(0, count($recipients));
    }

    /**
     * Tests getting recipients (from invalid list)
     */
    public function testGetRecipientsForInvalidUser() {
        $mail = new Opus_Job_Worker_MailPublishNotification();
        $users = array('doesnotexist');
        $recipients = $mail->getRecipients($users);
        $this->assertNotNull($recipients);
        $this->assertEquals(0, count($recipients));
    }

    /**
     * Tests getting recipients (from existing user, without mail)
     */
    public function testGetRecipientsForUserWithoutMail() {
        $mail = new Opus_Job_Worker_MailPublishNotification();
        $users = array('admin');
        $recipients = $mail->getRecipients($users);
        $this->assertNotNull($recipients);
        $this->assertEquals(0, count($recipients));
    }

    /**
     * Tests getting recipients (from existing user, without mail)
     */
    public function testGetRecipientsForUserWithMail() {
        $mail = new Opus_Job_Worker_MailPublishNotification();
        $users = array('hasmail');
        $recipients = $mail->getRecipients($users);
        $this->assertNotNull($recipients);
        $this->assertEquals(1, count($recipients));
    }

}

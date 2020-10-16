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
 * @category    Application
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Doi;

use Opus\Permission;

class UserRecipientProvider implements NotificationRecipientProvider
{

    const PERMISSION = 'resource_doi_notification';

    /**
     * Returns array with recipients.
     *
     * Each recipient is returned as an array with 'name' and 'address' of the recipient. The 'name' should be the
     * full name and the address the email of the recipient.
     *
     * @return array|mixed
     */
    public function getRecipients()
    {
        $recipients = [];

        $accounts = Permission::getAccounts(self::PERMISSION);

        foreach ($accounts as $account) {
            $email = $account->getEmail();

            if (strlen(trim($email)) == 0) {
                continue; // do not add to recipient list
            }

            $name = $account->getFullName();

            if (strlen(trim($name)) == 0) {
                $name = $account->getLogin();
            }

            $recipients[] = [
                'name' => $name,
                'address' => $email
            ];
        }

        return $recipients;
    }
}

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
 * @category    Test
 * @package     Framework
 * @author      Edouard Simon (edouard.simon@zib.de)
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Model\Plugin;

use Opus\Model\ModelInterface;
use Opus\Model\Plugin\AbstractPlugin;

/**
 *
 */
class AbstractPluginMock extends AbstractPlugin
{

    /**
     * Array containing names of methods that have been called
     */
    public $calledHooks = [];

    /**
     * @see {Opus\Model\Plugin\PluginInterface::preStore}
     */
    public function preStore(ModelInterface $model)
    {
        $this->calledHooks[] = __METHOD__;
    }

    /**
     * @see {Opus\Model\Plugin\PluginInterface::preFetch}
     */
    public function preFetch(ModelInterface $model)
    {
        $this->calledHooks[] = __METHOD__;
    }

    /**
     * @see {Opus\Model\Plugin\PluginInterface::postStore}
     */
    public function postStore(ModelInterface $model)
    {
        $this->calledHooks[] = __METHOD__;
    }

    /**
     * @see {Opus\Model\Plugin\PluginInterface::postStoreInternal}
     */
    public function postStoreInternal(ModelInterface $model)
    {
        $this->calledHooks[] = __METHOD__;
    }

    /**
     * @see {Opus\Model\Plugin\PluginInterface::postStoreExternal}
     */
    public function postStoreExternal(ModelInterface $model)
    {
        $this->calledHooks[] = __METHOD__;
    }

    /**
     * @see {Opus\Model\Plugin\PluginInterface::preDelete}
     */
    public function preDelete(ModelInterface $model)
    {
        $this->calledHooks[] = __METHOD__;
    }

    /**
     * @see {Opus\Model\Plugin\PluginInterface::postDelete}
     */
    public function postDelete($modelId)
    {
        $this->calledHooks[] = __METHOD__;
    }
}
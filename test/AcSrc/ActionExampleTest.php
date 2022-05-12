<?php
/**
 * Gectrl, PHP generic controller
 *
 * This file is a part of Gectrl.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2021-22 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      https://kigkonsult.se
 * @license   Subject matter of licence is the software Gectrl.
 *            The above copyright, link, package and version notices,
 *            this licence notice shall be included in all copies or substantial
 *            portions of the Gectrl.
 *
 *            Gectrl is free software: you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation, either version 3 of
 *            the License, or (at your option) any later version.
 *
 *            Gectrl is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public License
 *            along with Gectrl. If not, see <https://www.gnu.org/licenses/>.
 */
namespace Kigkonsult\Gectrl\AcSrc;

use Kigkonsult\Gectrl\ActionClassInterface;
use Kigkonsult\Gectrl\Package;

/**
 * Class ActionExampleTest
 *
 * ActionClass example
 *
 * @package Kigkonsult\Gectrl\AcSrc
 */
class ActionExampleTest extends Action0Base implements ActionClassInterface, OtherInterface
{
    /**
     * @inheritDoc
     */
    public static function getExecOrder() : int
    {
        return 2;
    }

    /**
     * @inheritDoc
     */
    public static function evaluate( Package $package, mixed $config = null, mixed $logger = null ) : bool
    {
        return ( ! $package->isOutputSet());
    }

    /**
     * @inheritDoc
     */
    public static function doAction( Package $package, mixed $config = null, mixed $logger = null ) : bool
    {
        $package->setWorkData( self::OTHER, true )
            ->setWorkData( self::class, parent::hello())

            ->setOutput( $package->getInput())

            ->setResultLog( self::class, true );

        return true;
    }
}

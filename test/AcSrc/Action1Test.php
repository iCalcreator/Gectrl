<?php
/**
 * Gectrl, PHP generic controller
 *
 * Copyright 2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   Gectrl
 * Version   1.0
 * License   Subject matter of licence is the software Gectrl.
 *           The above copyright, link, package and version notices,
 *           this licence notice shall be included in all copies or
 *           substantial portions of the Gectrl.
 *
 *           Gectrl is free software: you can redistribute it and/or modify
 *           it under the terms of the GNU Lesser General Public License as
 *           published by the Free Software Foundation, either version 3 of
 *           the License, or (at your option) any later version.
 *
 *           Gectrl is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *           GNU Lesser General Public License for more details.
 *
 *           You should have received a copy of the
 *           GNU Lesser General Public License
 *           along with Gectrl.
 *           If not, see <https://www.gnu.org/licenses/>.
 *
 * This file is a part of Gectrl.
 */
namespace Kigkonsult\Gectrl\AcSrc;

use Kigkonsult\Gectrl\ActionClassInterface;
use Kigkonsult\Gectrl\Package;

class Action1Test extends Action0Base implements ActionClassInterface
{
    public static function getExecOrder() : int
    {
        return 1;
    }

    public static function evaluate( Package $package ) : bool
    {
        return false;
    }

    public static function doAction( Package $package ) : bool
    {
        return false;
    }
}
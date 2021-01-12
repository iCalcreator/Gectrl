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
declare( strict_types = 1 );
namespace Kigkonsult\Gectrl;

/**
 * Interface ActionClassInterface
 *
 * Prescribe strategy (application logic) actionClasses contract,
 *   applied as the (Gectrl) invoke of condition 'evaluation' and opt, logic 'doAction' methods,
 *
 * Classes implementing the interface may also
 *   extend a baseClass
 *   implement other interface(s)
 *   use the singleton pattern
 *   ....
 *
 * Method getExecOrder MUST return an unique EXECORDER (int) number,
 *   class (method doAction) will be invoked in order
 * A low EXECORDER number (first?) and the 'evaluate' method simply 'return true;'
 *   may the 'doAction' method be used for
 *      input sanitation, validation, assert...
 *      single-load multi-use work resource (stored, with key, in package workData)
 * A high EXECORDER number (last?) and the 'evaluate' method simply 'return true;'
 *   may the 'doAction' method be used for
 *     'default' action
 *     final (output) preparation
 *
 * One or both of evaluate/doAction methods below may be a factory method or not...
 *
 * @package Kigkonsult\Gectrl
 */
interface ActionClassInterface
{
    /**
     * MUST return an unique EXECORDER (int) number, class (methods) will be invoked in order
     */
    public static function getExecOrder() : int;

    /**
     * Method names, used in Gectrl
     */
    const EVALUATE  = 'evaluate';
    const DOACTION  = 'doAction';

    /**
     * Evaluates application logic invoke condition
     *
     * Argument Package is passed as reference
     * A bool true return will cause Gectrl to invoke the 'doAction' method (below), false not
     *
     * @param Package $package
     * @return bool
     */
    public static function evaluate( Package $package ) : bool;

    /**
     * Application logic, will be invoked if method evaluate (above) return true
     *
     * Argument Package is passed as reference
     * A (bool) true return (here) will force exec break and Gectrl to return the package
     *
     * @param Package $package
     * @return bool
     */
    public static function doAction( Package $package ) : bool;
}

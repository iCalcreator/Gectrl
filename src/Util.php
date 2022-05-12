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
declare( strict_types = 1 );
namespace Kigkonsult\Gectrl;

use Exception;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

use function bin2hex;
use function chr;
use function ord;
use function random_bytes;
use function str_split;
use function strcasecmp;
use function vsprintf;

/**
 * Gectrl util, assert and sort methods
 */
class Util
{
    /**
     * @see https://www.php.net/manual/en/function.com-create-guid.php#117893
     * @return string
     * @throws Exception
     * @since 20220509 1.8.3
     */
    public static function getGuid() : string
    {
        static $FMT = '%s%s-%s-%s-%s-%s%s%s';
        $bytes = random_bytes( 16 );
        $bytes[6] = chr( ord( $bytes[6] ) & 0x0f | 0x40 ); // set version to 0100
        $bytes[8] = chr( ord( $bytes[8] ) & 0x3f | 0x80 ); // set bits 6-7 to 10
        return vsprintf( $FMT, str_split( bin2hex( $bytes ), 4 ) );
    }

    /**
     * Asserts actionClass
     *
     * Return true for class implementing ActionClassInterface,
     *   trait / interface / abstract class returns false
     * Throws exception on Reflection error
     *
     * @link https://www.php.net/manual/en/language.operators.type.php#102988
     * @param class-string<ActionClassInterface> $actionClass   ActionClassInterface class FQCN
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function assertActionClass( string $actionClass ) : bool
    {
        static $FMT7 = 'Reflectionerror for %s : %s';
        static $FMT8 = 'Class %s do not implement ActionClassInterface';
        try {
            $reflectionClass = new ReflectionClass( $actionClass );
        }
        catch( ReflectionException $re ) {
            throw new InvalidArgumentException( sprintf( $FMT7, $actionClass, $re->getMessage() ), 21, $re );
        }
        return match ( true ) {
            ( $reflectionClass->isTrait() || // catch both false and null
                $reflectionClass->isInterface() || $reflectionClass->isAbstract() ) => false,
            $reflectionClass->implementsInterface( ActionClassInterface::class ) => true,
            default => throw new InvalidArgumentException( sprintf( $FMT8, $actionClass ), 28 )
        };
    }
    /**
     * @var callable
     */
    public static $ACTIONCLASSSORTER = [ __CLASS__, 'actionClassSort' ];

    /**
     * Sort ActionClassInterfaces (i.e. string FQCNs) on method getExecOrder result
     *
     * If equal, sort on actionClasses name
     *
     * @param class-string<ActionClassInterface> $a
     * @param class-string<ActionClassInterface> $b
     * @return int
     */
    public static function actionClassSort( string $a, string $b ) : int
    {
        $aeo = $a::getExecOrder();
        $beo = $b::getExecOrder();
        return match( true ) {
            ( $aeo === $beo ) => strcasecmp( $a, $b ),
            ( $aeo < $beo )   => -1,
            default           => 1
        };
    }
}

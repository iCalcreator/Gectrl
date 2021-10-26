<?php
/**
 * Gectrl, PHP generic controller
 *
 * This file is a part of Gectrl.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
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
use RuntimeException;

use function in_array;
use function strcasecmp;
use function sprintf;
use function usort;

/**
 * Class Gectrl
 *
 * Gectrl is a PHP generic controller class
 *   Supports the MVC software design pattern
 *   Distinguish controller and application logic using a strategy pattern
 *
 * The controller provides coordination logic
 *
 * The controller delegates application logic to actionClasses,
 *   using implementations of the (strategy) ActionClassInterface,
 *   invoking of actionClass condition 'evaluate' and logic 'doAction' methods,
 *   passing all data information in an encapsulated Package class instance
 *     input, output, config, logger etc
 *
 * @package Kigkonsult\Gectrl
 */
class Gectrl
{
    /**
     * @var callable
     */
    private static $SORTER = [ __CLASS__, 'actionClassSort' ];

    /**
     * Array (string[]) FQCNs for actionClasses
     *
     * @var string[]
     */
    private array $actionClasses = [];

    /**
     * @var Package
     */
    private Package $package;

    /**
     * Gectrl constructor
     *
     * @param mixed $config
     * @param mixed $logger
     * @param string[] $actionClasses
     * @throws Exception
     */
    public function __construct( $config = null, $logger = null, array $actionClasses = [] )
    {
        $this->setPackage( new Package( $config, $logger ));
        if( ! empty( $actionClasses )) {
            $this->setActionClasses( $actionClasses );
        }
    }

    /**
     * Gectrl factory method
     *
     * @param mixed $config
     * @param mixed $logger
     * @param string[] $actionClasses
     * @return Gectrl
     */
    public static function init(
        $config = null,
        $logger = null,
        array $actionClasses = []
    ) : Gectrl
    {
        return new self( $config, $logger, $actionClasses );
    }

    /**
     * Main method, assert Gectrl instance, invoke actionClass:evaluate/doAction in order
     *
     * Accepts any kind of input (scalar/array/object)
     *   for the default (internally) created Package class instance
     * OR
     *   a (replacing) externally created package class instance
     *
     * @param mixed $input   any kind of input (scalar/array/object) or a Package instance
     * @return Package
     * @throws RuntimeException
     */
    public function main( $input = null ) : Package
    {
        switch( true ) {
            case ( null === $input ) :
                break;
            case ( $input instanceof Package ) :
                $this->setPackage( $input );
                break;
            default :
                $this->package->setInput( $input );
                break;
        } // end switch
        $this->assert();
        usort( $this->actionClasses, self::$SORTER );
        foreach( $this->actionClasses as $actionClass ) {
            if( false === $actionClass::{ActionClassInterface::EVALUATE}( $this->package )) {
                continue;
            }
            if( true === $actionClass::{ActionClassInterface::DOACTION}( $this->package )) {
                break;
            }
        } // end foreach
        return $this->package;
    }

    /**
     * Sort ActionClassInterfaces (i.e. string FQCNs) on method getExecOrder result
     *
     * If equal, sort on actionClasses name
     *
     * @param string $a
     * @param string $b
     * @return int
     */
    private static function actionClassSort( string $a, string $b ) : int
    {
        $aeo = $a::getExecOrder();
        $beo = $b::getExecOrder();
        if( $aeo == $beo ) {
            return strcasecmp( $a, $b );
        }
        return ( $aeo < $beo ) ? -1 : 1;
    }

    /**
     * Assert input and actionClasses are set
     *
     * @return void
     * @throws RuntimeException
     */
    private function assert() : void
    {
        static $FMT1 = 'No Gectrl input';
        static $FMT2 = 'No Gectrl actions';
        if( ! $this->package->isInputSet()) {
            throw new RuntimeException( $FMT1, 11 );
        }
        if( ! $this->isActionClassSet()) {
            throw new RuntimeException( $FMT2, 12 );
        }
    }

    /**
     * Return (string[]) actionClasses (FQCNs)
     *
     * @return string[]  i.e. ActionClassInterface class FQCN list
     */
    public function getActionClasses() : array
    {
        return $this->actionClasses;
    }

    /**
     * Return bool, true if actionsClasses (fqcn) is set, otherwise false
     *
     * @param string|null $fqcn
     * @return bool
     */
    public function isActionClassSet( string $fqcn = null ) : bool
    {
        if( ! empty( $fqcn )) {
            return in_array( $fqcn, $this->actionClasses, true );
        }
        return ( ! empty( $this->actionClasses ));
    }

    /**
     * Add (string) actionClass (FQCN)
     *
     * Opt. traits / interfaces / abstract classes are ignored
     * Throws exception on Reflection, interface or trait error
     *
     * @param string $actionClass   ActionClassInterface class FQCN
     * @return Gectrl
     * @throws InvalidArgumentException
     */
    public function addActionClass( string $actionClass ) : Gectrl
    {
        if( self::assertActionClass( $actionClass )) {
            $this->actionClasses[] = $actionClass;
        }
        return $this;
    }

    /**
     * Asserts actionClass
     *
     * Return true for class implementing ActionClassInterface,
     *   trait / interface / abstract class false
     * Throws exception on Reflection, interface or trait error
     *
     * @link https://www.php.net/manual/en/language.operators.type.php#102988
     * @param string $actionClass   ActionClassInterface class FQCN
     * @return bool
     * @throws InvalidArgumentException
     */
    private static function assertActionClass( string $actionClass  ) : bool
    {
        static $FMT1 = 'Reflectionerror for %s, %s';
        static $FMT2 = 'Class %s implements NOT ActionClassInterface';
        try {
            $reflectionClass = new ReflectionClass( $actionClass );
        }
        catch( ReflectionException $re ) {
            throw new InvalidArgumentException(
                sprintf( $FMT1, $actionClass, $re->getMessage()),
                21,
                $re
            );
        }
        $isTrait = $reflectionClass->isTrait();
        if(( true === $isTrait ) ||
            $reflectionClass->isInterface() ||
            $reflectionClass->isAbstract()) {
            return false;
        }
        if( empty( $isTrait ) || // null|false
            ! $reflectionClass->implementsInterface( ActionClassInterface::class  )) {
            throw new InvalidArgumentException( sprintf( $FMT2, $actionClass ), 22 );
        }
        return true;
    }

    /**
     * Set (string[]) actionClasses (FQCNs)
     *
     * For extracting namespace(s) for actionsClasses,
     * you may use https://gitlab.com/hpierce1102/ClassFinder
     *
     * @param string[] $actionClasses  ActionClassInterface class FQCN list
     * @return Gectrl
     * @throws InvalidArgumentException
     */
    public function setActionClasses( array $actionClasses ) : Gectrl
    {
        $this->actionClasses = [];
        foreach( $actionClasses as $actionFqcn ) {
            $this->addActionClass( $actionFqcn );
        }
        return $this;
    }

    /**
     * Return Package
     *
     * @return Package
     */
    public function getPackage() : Package
    {
        return $this->package;
    }

    /**
     * Set (replace) Package
     *
     * @param Package $package
     * @return Gectrl
     */
    public function setPackage( Package $package ) : Gectrl
    {
        $this->package = $package;
        return $this;
    }
}

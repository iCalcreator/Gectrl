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
use RuntimeException;

use function in_array;
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
 *     along with opt config and logger
 *
 * @package Kigkonsult\Gectrl
 * @since 20220511 1.8.5
 */
class Gectrl
{
    /**
     * Array (string[]) FQCNs for actionClasses
     *
     * @var class-string<ActionClassInterface>[]
     */
    private array $actionClasses = [];

    /**
     * @var bool
     */
    private bool $actionClassesCheck = false;

    /**
     * @var null|Package
     */
    private ? Package $package = null;

    /**
     * Opt any config
     *
     * @var null|mixed
     * @since 20220509 1.8.2
     */
    private mixed $config = null;

    /**
     * Opt any logger
     *
     * @var null|mixed
     * @since 20220509 1.8.2
     */
    private mixed $logger = null;

    /**
     * Gectrl constructor
     *
     * @param mixed $config
     * @param mixed $logger
     * @param class-string<ActionClassInterface>[] $actionClasses
     * @throws Exception
     * @since 20220510 1.8.4
     */
    public function __construct(
        mixed $config = null,
        mixed $logger = null,
        ? array $actionClasses = []
    )
    {
        if( null !== $config ) {
            $this->config = $config;
        }
        if( null !== $logger ) {
            $this->logger = $logger;
        }
        if( ! empty( $actionClasses )) {
            $this->setActionClasses( $actionClasses );
        }
    }

    /**
     * Gectrl factory method
     *
     * @param mixed    $config
     * @param mixed    $logger
     * @param class-string<ActionClassInterface>[] $actionClasses
     * @return Gectrl
     * @throws Exception
     * @since 20220509 1.8.2
     */
    public static function init(
        mixed $config = null,
        mixed $logger = null,
        ? array $actionClasses = []
    ) : Gectrl
    {
        return new self( $config, $logger, $actionClasses );
    }

    /**
     * Process ONE input transaction
     *
     * @param mixed $input   any kind of input (scalar/array/object) or a Package instance
     * @return Package
     * @throws Exception
     * @throws RuntimeException
     * @since 20220511 1.8.5
     */
    public function processOne( mixed $input ) : Package
    {
        $this->setInput( $input );
        return $this->main();
    }

    /**
     * Process MULTIPLE input transactions
     *
     * Empty input array allowed, returns empty array
     *
     * @param mixed[]|Package[] $input   any kind of input, array of scalar/array/object or Package[]
     * @return Package[]
     * @throws Exception
     * @throws RuntimeException
     * @since 20220511 1.8.5
     */
    public function processMany( array $input ) : array
    {
        $output = [];
        foreach( $input as $packageInput ) {
            $output[] = $this->processOne( $packageInput );
        }
        return $output;
    }

    /**
     * Main method, assert Gectrl instance, invoke actionClass:evaluate/doAction in order
     *
     * Accepts any kind of input (scalar/array/object)
     *   for the default (internally) created Package class instance
     * OR
     *   a (replacing) externally created package class instance
     *
     * @param mixed $input   any kind of input, scalar/array/object or a Package instance
     * @return Package
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @since 20220511 1.8.5
     */
    public function main( mixed $input = null ) : Package
    {
        static $FMT1 = 'No Gectrl (Package input) set';
        static $FMT2 = 'No Gectrl actions set';
        if( null !== $input ) {
            $this->setInput( $input );
        }
        elseif(( null === $this->package )) {
            throw new RuntimeException( $FMT1, 11 );
        }
        if( ! $this->actionClassesCheck && ! $this->isActionClassSet()) {
            throw new RuntimeException( $FMT2, 12 );
        }
        foreach( $this->actionClasses as $actionClass ) {
            if( false === $actionClass::{ActionClassInterface::EVALUATE}(
                $this->package,
                $this->config,
                $this->logger
                )) {
                continue; // skip this action
            }
            if( true === $actionClass::{ActionClassInterface::DOACTION}(
                $this->package,
                $this->config,
                $this->logger
                )) {
                break; // true force break exec of actions
            }
        } // end foreach
        return $this->package;
    }

    /**
     * Return (string[]) actionClasses (FQCNs) sorted om execOrder (and FQCN)
     *
     * @return class-string<ActionClassInterface>[]  i.e. ActionClassInterface class FQCN list
     */
    public function getActionClasses() : array
    {
        return $this->actionClasses;
    }

    /**
     * Return bool, true if actionsClasses (or fqcn) is set, otherwise false
     *
     * @param string|null $fqcn
     * @return bool
     */
    public function isActionClassSet( ? string $fqcn = null ) : bool
    {
        if( ! empty( $fqcn )) {
            return in_array( $fqcn, $this->actionClasses, true );
        }
        if( ! empty( $this->actionClasses )) {
            $this->actionClassesCheck = true;
            return true;
        }
        return false;
    }

    /**
     * Add (string) actionClass (FQCN)
     *
     * Opt. traits / interfaces / abstract classes are ignored
     * Throws exception on Reflection error
     *
     * @param class-string<ActionClassInterface> $actionClass   ActionClassInterface class FQCN
     * @return Gectrl
     * @throws InvalidArgumentException
     * @since 20220509 1.8.1
     */
    public function addActionClass( string $actionClass ) : Gectrl
    {
        if( Util::assertActionClass( $actionClass ) ) {
            $this->actionClasses[] = $actionClass;
            usort( $this->actionClasses, Util::$ACTIONCLASSSORTER );
        }
        return $this;
    }

    /**
     * Set (string[]) actionClasses (FQCNs)
     *
     * For extracting namespace(s) for actionsClasses,
     * you may use https://gitlab.com/hpierce1102/ClassFinder
     *
     * @param class-string<ActionClassInterface>[] $actionClasses  ActionClassInterface class FQCN list
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
     * @return null|Package
     */
    public function getPackage() : ? Package
    {
        return $this->package;
    }

    /**
     * Set (replace) Package
     *
     * @param mixed|Package $input
     * @throws InvalidArgumentException
     * @return Gectrl
     * @since 20220511 1.8.5
     */
    public function setInput( mixed $input ) : Gectrl
    {
        if( ! $input instanceof Package ) {
            try {
                $input = new Package( $input );
            }
            catch( Exception $e ) {
                throw new InvalidArgumentException( $e->getMessage(), 31, $e );
            }
        }
        $this->setPackage( $input );
        return $this;
    }

    /**
     * Set (replace) Package, input MUST be set
     *
     * @param Package $package
     * @throws InvalidArgumentException
     * @return Gectrl
     * @since 20220511 1.8.5
     */
    public function setPackage( Package $package ) : Gectrl
    {
        static $FMT4 = 'No input set in Package %s / %f';
        if( ! $package->isInputSet()) {
            throw new InvalidArgumentException(
                sprintf( $FMT4, $package->getCorrelationId(), $package->getTimestamp()),
                41
            );
        }
        $this->package = $package;
        return $this;
    }

    /**
     * Return (mixed) config
     *
     * @return mixed
     */
    public function getConfig() : mixed
    {
        return $this->config;
    }

    /**
     * Return bool, true if config is set, otherwise false
     *
     * @return bool
     */
    public function isConfigSet() : bool
    {
        return ( null !== $this->config );
    }

    /**
     * Set (mixed) config
     *
     * @param mixed $config
     * @return Gectrl
     */
    public function setConfig( mixed $config ) : Gectrl
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Return logger
     *
     * @return mixed
     */
    public function getLogger() : mixed
    {
        return $this->logger;
    }

    /**
     * Return bool, true if logger is set, otherwise false
     *
     * @return bool
     */
    public function isLoggerSet() : bool
    {
        return ( null !== $this->logger );
    }

    /**
     * Set logger
     *
     * @param mixed $logger
     * @return Gectrl
     */
    public function setLogger( mixed $logger ) : Gectrl
    {
        $this->logger = $logger;
        return $this;
    }
}

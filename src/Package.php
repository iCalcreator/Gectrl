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
use Kigkonsult\KeyValueMgr\KeyValueMgr;

use function gettype;
use function microtime;
use function number_format;

/**
 * Class Package
 *
 * A transaction data information encapsulated package class with
 *   unique timestamp and guid (default set at instance creation)
 *   opt config and logger(s)
 *   any kind of (_scalar_/_array_/_object_) __input__ and actionClasses __output__
 *   intermediate (tmp/work) data
 *   assemble a result log
 *
 * The Package class instance argument is always passed as reference
 *   when Gectrl are
 *     invoking the ActionClassInterface methods
 *     return exec result
 *
 * Package is internally using KeyValueMgr for workData and resultLog (below)
 *   and is to recommend as config.
 *
 * @package Kigkonsult\Gectrl
 * @since 20220509 1.8.2
 */
class Package
{
    /**
     * Current Unix timestamp with microseconds, default 'microtime( true)' at instance create
     *
     * @var float
     */
    private float $timestamp;

    /**
     * Unique guid, default set at instance create
     *
     * @var string
     */
    private string $correlationId;

    /**
     * Required input (scalar/array/object)
     *
     * @var null|mixed
     */
    private mixed $input = null;

    /**
     * Opt output (scalar/array/object)
     *
     * @var null|mixed
     */
    private mixed $output = null;

    /**
     * Opt work data, shared between actionClasses
     *
     * Key/value pairs using instance of KeyValueMgr
     * ValueTypes :
     *   key   : string
     *   value : scalar/array/object
     * Ex 'resource' => [ 'valueN'... ]
     *
     * @link https://github.com/iCalcreator/KeyValueMgr
     * @var KeyValueMgr
     */
    private KeyValueMgr $workData;

    /**
     * Opt (any) actionClass effect outcome
     *
     * Key/value pairs using instance of KeyValueMgr
     * ValueTypes :
     *   Key   : string
     *   Value : scalar/array/object
     * Ex 'actionClass-FQCN' => bool
     *
     * @link https://github.com/iCalcreator/KeyValueMgr
     * @var KeyValueMgr
     */
    private KeyValueMgr $resultLog;

    /**
     * Package constructor
     *
     * @param mixed $input
     * @throws Exception
     * @since 20220509 1.8.2
     */
    public function __construct( mixed $input = null )
    {
        $this->timestamp     = microtime( true );
        $this->correlationId = Util::getGuid();
        if( null !== $input ) {
            $this->input     = $input;
        }
        $this->workData      = new KeyValueMgr();
        $this->resultLog     = new KeyValueMgr();
    }

    /**
     * Package factory method
     *
     * @param mixed $input
     * @return Package
     * @throws Exception
     * @since 20220509 1.8.2
     */
    public static function init( mixed $input = null ) : Package
    {
        return new self( $input );
    }

    /**
     * Return (init) timestamp
     *
     * @return float
     */
    public function getTimestamp() : float
    {
        return $this->timestamp;
    }

    /**
     * Set (replace) timestamp
     *
     * @param float $timestamp
     * @return Package
     */
    public function setTimestamp( float $timestamp ) : Package
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * Return correlationId (guid)
     *
     * @return string guid
     */
    public function getCorrelationId() : string
    {
        return $this->correlationId;
    }

    /**
     * Set (replace) correlationId (guid)
     *
     * @param string $correlationId guid
     * @return Package
     */
    public function setCorrelationId( string $correlationId ) : Package
    {
        $this->correlationId = $correlationId;
        return $this;
    }

    /**
     * Return (scalar/array/object) input
     *
     * @return mixed
     */
    public function getInput() : mixed
    {
        return $this->input;
    }

    /**
     * Return bool, true if input is set, otherwise false
     *
     * @return bool
     */
    public function isInputSet() : bool
    {
        return ( null !== $this->input );
    }

    /**
     * Set (scalar/array/object) input
     *
     * @param mixed $input
     * @return Package
     */
    public function setInput( mixed $input ) : Package
    {
        $this->input = $input;
        return $this;
    }

    /**
     * Return (scalar/array/object) output
     *
     * @return mixed
     */
    public function getOutput() : mixed
    {
        return $this->output;
    }

    /**
     * Return bool, true if output is set, otherwise false
     *
     * @return bool
     */
    public function isOutputSet() : bool
    {
        return ( null !== $this->output );
    }

    /**
     * Set (scalar/array/object) output
     *
     * @param mixed $output  scalar/array/object
     * @return Package
     */
    public function setOutput( mixed $output ) : Package
    {
        $this->output = $output;
        return $this;
    }

    /**
     * Return workData KeyValueMgr, value for workData key or false if key not found
     *
     * Major KeyValueMgr methods
     * KeyValueMgr::exists( key ) : bool
     * KeyValueMgr::get( key ) : mixed
     * KeyValueMgr::set( key, value ) : KeyValueMgr
     *
     * @param null|string $key
     * @return mixed KeyValueMgr|mixed|bool
     */
    public function getWorkData( ? string $key = null ) : mixed
    {
        return match( true ) {
            empty( $key ) => $this->workData,
            $this->isWorkDataKeySet( $key ) => $this->workData->get( $key ),
            default       => false
        };
    }

    /**
     * Return workData keys keys
     *
     * @return string[]
     */
    public function getWorkDataKeys() : array
    {
        return $this->workData->getKeys();
    }

    /**
     * Return bool, true if workData key is set, otherwise false
     *
     * @param string $key
     * @return bool
     */
    public function isWorkDataKeySet( string $key ) : bool
    {
        return $this->workData->exists( $key );
    }

    /**
     * Return bool, true if workData has any key set, otherwise false
     *
     * @return bool
     */
    public function isWorkDataSet() : bool
    {
        return ( ! empty( $this->workData->getKeys()));
    }

    /**
     * Set workData, key with value
     *
     * @param string $key
     * @param mixed $value  scalar/array/object
     * @return Package
     */
    public function setWorkData( string $key, mixed $value ) : Package
    {
        $this->workData->set( $key, $value );
        return $this;
    }

    /**
     * Return resultLog KeyValueMgr, value for resultLog key or false if key not found
     *
     * Major KeyValueMgr methods
     * KeyValueMgr::exists( key ) : bool
     * KeyValueMgr::get( key ) : mixed
     * KeyValueMgr::set( key, value ) : KeyValueMgr
     *
     * @param null|string $key
     * @return mixed  KeyValueMgr|mixed|bool
     */
    public function getResultLog( ? string $key = null ) : mixed
    {
        return match( true ) {
            empty( $key ) => $this->resultLog,
            $this->isResultLogKeySet( $key ) => $this->resultLog->get( $key ),
            default       => false
        };
    }

    /**
     * Return resultLog keys
     *
     * @return string[]
     */
    public function getResultLogKeys() : array
    {
        return $this->resultLog->getKeys();
    }

    /**
     * Return bool, true if resultLog key is set, otherwise false
     *
     * @param string $key
     * @return bool
     */
    public function isResultLogKeySet( string $key ) : bool
    {
        return $this->resultLog->exists( $key );
    }

    /**
     * Return bool, true if resultLog has any key set, otherwise false
     *
     * @return bool
     */
    public function isResultLogSet() : bool
    {
        return ( ! empty( $this->resultLog->getKeys()));
    }

    /**
     * Set result log, key with value
     *
     * @param string $key
     * @param mixed $value   scalar/array/object
     * @return Package
     */
    public function setResultLog( string $key, mixed $value ) : Package
    {
        $this->resultLog->set( $key, $value );
        return $this;
    }

    /**
     * Return string, property load status (and input/output types), eol-separated (toString-method)
     *
     * @since 20220509 1.8.2
     */
    public function getLoadStatus() : string
    {
        static $PROPNAMES = [
            'timeStamp : ',
            'correlationId : ',
            'input type : ',
            'output type : ',
            'workdata keys : ',
            'resultLog keys : ',
        ];
        static $D = '-';
        $output  = $PROPNAMES[0] . number_format( $this->getTimestamp(), 6 ) . PHP_EOL;
        $output .= $PROPNAMES[1] . $this->getCorrelationId() . PHP_EOL;
        $output .= $PROPNAMES[2] . ( $this->isInputSet() ? gettype( $this->getInput()) : $D ) . PHP_EOL;
        $output .= $PROPNAMES[3] . ( $this->isOutputSet() ? gettype( $this->getOutput()) : $D ) . PHP_EOL;
        if( $this->isWorkDataSet()) {
            foreach( $this->getWorkDataKeys() as $key ) {
                $output .= $PROPNAMES[4] . $key . $D .  $this->getWorkData( $key ) . PHP_EOL;
            }
        }
        if( $this->isResultLogSet()) {
            foreach( $this->getResultLogKeys() as $key ) {
                $output .= $PROPNAMES[5] . $key . $D .  $this->getResultLog( $key ) . PHP_EOL;
            }
        }
        return $output;
    }
}

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
use Kigkonsult\KeyValueMgr\KeyValueMgr;

use function function_exists;
use function gettype;
use function implode;
use function microtime;
use function random_int;
use function number_format;
use function sprintf;
use function trim;

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
     * Opt any config
     *
     * @var mixed
     */
    private $config;

    /**
     * Opt any logger
     *
     * @var mixed
     */
    private $logger;

    /**
     * Required input (scalar/array/object)
     *
     * @var mixed
     */
    private $input;

    /**
     * Opt output (scalar/array/object)
     *
     * @var mixed
     */
    private $output;

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
     * @param mixed $config
     * @param mixed $logger
     * @param mixed $input
     * @throws Exception
     */
    public function __construct( $config = null, $logger = null, $input = null )
    {
        $this->timestamp     = microtime( true );
        $this->correlationId = self::getGuid();
        if( null !== $config ) {
            $this->config = $config;
        }
        if( null !== $logger ) {
            $this->logger = $logger;
        }
        if( null !== $input ) {
            $this->input = $input;
        }
        $this->workData  = new KeyValueMgr();
        $this->resultLog = new KeyValueMgr();
    }

    /**
     * @link https://stackoverflow.com/questions/21671179/how-to-generate-a-new-guid#26163679
     * @return string
     * @throws Exception
     */
    public static function getGuid() : string
    {
        static $FUNCTION = 'com_create_guid';
        static $EXCL     = '{}';
        static $FMTGUID  = '%04X%04X-%04X-%04X-%04X-%04X%04X%04X';
        return ( true === function_exists( $FUNCTION ))
            ? trim( $FUNCTION(), $EXCL )
            : sprintf(
                $FMTGUID,
                random_int( 0, 65535 ),
                random_int( 0, 65535 ),
                random_int( 0, 65535 ),
                random_int( 16384, 20479 ),
                random_int( 32768, 49151 ),
                random_int( 0, 65535 ),
                random_int( 0, 65535 ),
                random_int( 0, 65535 )
            );
    }

    /**
     * Package factory method
     *
     * @param mixed $config
     * @param mixed $logger
     * @param mixed $input
     * @return Package
     * @throws Exception
     */
    public static function init( $config = null, $logger = null, $input = null ) : Package
    {
        return new self( $config, $logger, $input );
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
     * Return (mixed) config
     *
     * @return mixed
     */
    public function getConfig()
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
     * @return Package
     */
    public function setConfig( $config ) : Package
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Return logger
     *
     * @return mixed
     */
    public function getLogger()
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
     * @return Package
     */
    public function setLogger( $logger ) : Package
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Return (scalar/array/object) input
     *
     * @return mixed
     */
    public function getInput()
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
    public function setInput( $input ) : Package
    {
        $this->input = $input;
        return $this;
    }

    /**
     * Return (scalar/array/object) output
     *
     * @return mixed
     */
    public function getOutput()
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
    public function setOutput( $output ) : Package
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
     * @param string|null $key
     * @return KeyValueMgr|mixed|bool
     */
    public function getWorkData( string $key = null )
    {
        switch( true ) {
            case empty( $key ) :
                return $this->workData;
            case $this->isWorkDataKeySet( $key ) :
                return $this->workData->get( $key );
        } // end switch
        return false;
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
    public function setWorkData( string $key, $value ) : Package
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
     * @param string|null $key
     * @return KeyValueMgr|mixed|bool
     */
    public function getResultLog( string $key = null )
    {
        switch( true ) {
            case empty( $key ) :
                return $this->resultLog;
            case $this->isResultLogKeySet( $key ) :
                return $this->resultLog->get( $key );
        } // end switch
        return false;
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
    public function setResultLog( string $key, $value ) : Package
    {
        $this->resultLog->set( $key, $value );
        return $this;
    }

    /**
     * Return string, property load status, eol-separated
     */
    public function getLoadStatus() : string
    {
        static $PROPNAMES = [
            'timeStamp : ',
            'correlationId : ',
            'config type : ',
            'logger type : ',
            'input type : ',
            'output type : ',
            'workdata keys : ',
            'resultLog keys : ',
        ];
        static $D = '-';
        static $C = ', ';
        $output  = $PROPNAMES[0] . number_format( $this->getTimestamp(), 6 ) . PHP_EOL;
        $output .= $PROPNAMES[1] . $this->getCorrelationId() . PHP_EOL;
        $output .= $PROPNAMES[2] . ( $this->isConfigSet() ? gettype( $this->getConfig()) : $D ) . PHP_EOL;
        $output .= $PROPNAMES[3] . ( $this->isLoggerSet() ? gettype( $this->getLogger()) : $D ) . PHP_EOL;
        $output .= $PROPNAMES[4] . ( $this->isInputSet() ? gettype( $this->getInput()) : $D ) . PHP_EOL;
        $output .= $PROPNAMES[5] . ( $this->isOutputSet() ? gettype( $this->getOutput()) : $D ) . PHP_EOL;
        $output .= $PROPNAMES[6] . implode( $C, $this->getWorkDataKeys()) . PHP_EOL;
        $output .= $PROPNAMES[7] . implode( $C, $this->getResultLogKeys()) . PHP_EOL;
        return $output;
    }
}

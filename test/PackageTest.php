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
use PHPUnit\Framework\TestCase;

class PackageTest extends TestCase
{
    private static string $VAR1 = 'var1';
    private static string $VAR2 = 'var2';
    private static string $VAR3 = 'var3';

    /**
     * Testing Package
     *
     * @test
     * @throws Exception
     */
    public function packageTest1() : void
    {
        $package = Package::init( self::$VAR1 );

        $this->assertIsFloat( $package->getTimestamp(), 'error 21' );
        $timeStamp = microtime( true );
        $package->setTimestamp( $timeStamp );
        $this->assertIsFloat( $package->getTimestamp(), 'error 22' );
        $this->assertEquals(
            number_format( $timeStamp, 6 ),
            number_format( $package->getTimestamp(), 6 ),
            'error 23'
        );

        $this->assertIsString( $package->getCorrelationId(), 'error 31' );
        $package->setCorrelationId( self::$VAR3 );
        $this->assertEquals( self::$VAR3, $package->getCorrelationId(), 'error 32');

        $this->assertTrue( $package->isInputSet(), 'error 61' );
        $this->assertEquals( self::$VAR1, $package->getInput(), 'error 63');
        $package->setInput( self::$VAR3 );
        $this->assertEquals( self::$VAR3, $package->getInput(), 'error 63');

        $this->assertFalse( $package->isOutputSet(), 'error 71' );
        $package->setOutput( self::$VAR3 );
        $this->assertTrue( $package->isOutputSet(), 'error 72' );
        $this->assertEquals( self::$VAR3, $package->getOutput(), 'error 73');

        $this->assertInstanceOf(KeyValueMgr::class, $package->getWorkData(), 'error 80' );
        $this->assertFalse( $package->isWorkDataSet(), 'error 81' );
        $this->assertEquals( [], $package->getWorkDataKeys(), 'error 82' );
        $this->assertFalse( $package->getWorkData( self::$VAR3 ), 'error 83' );
        $this->assertFalse( $package->isWorkDataKeySet( self::$VAR3 ), 'error 84' );
        $package->setWorkData( self::$VAR3, self::$VAR2 );
        $this->assertTrue( $package->isWorkDataKeySet( self::$VAR3 ), 'error 85' );
        $this->assertEquals( self::$VAR2, $package->getWorkData( self::$VAR3 ), 'error 86' );

        $packageToString = $package->getLoadStatus();
        $this->assertStringContainsString( self::$VAR3, $packageToString, 'error 87' );
        $this->assertStringContainsString( self::$VAR2, $packageToString, 'error 88' );
        $package->getWorkData()->remove( self::$VAR3 );

        $this->assertInstanceOf(KeyValueMgr::class, $package->getResultLog(), 'error 90' );
        $this->assertFalse( $package->isResultLogSet(), 'error 91' );
        $this->assertEquals( [], $package->getResultLogKeys(), 'error 92' );
        $this->assertFalse( $package->getResultLog( self::$VAR3 ), 'error 93' );
        $this->assertFalse( $package->isResultLogKeySet( self::$VAR3 ), 'error 94' );
        $package->setResultLog( self::$VAR3, self::$VAR2 );
        $this->assertTrue( $package->isResultLogKeySet( self::$VAR3 ), 'error 95' );
        $this->assertEquals( self::$VAR2, $package->getResultLog( self::$VAR3 ), 'error 96' );

        $packageToString = $package->getLoadStatus();
        $this->assertStringContainsString( self::$VAR3, $packageToString, 'error 99' );
        $this->assertStringContainsString( self::$VAR2, $packageToString, 'error 98' );
    }
}

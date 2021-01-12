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

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

include '../autoload.php';

class GectrlTest extends TestCase
{
    private static $VAR1 = 'var1';
    private static $VAR2 = 'var2';
    private static $VAR3 = 'var3';

    /**
     * Testing Gectrl and Package
     *
     * @test
     */
    public function gectrlTest1() {
        $gectrl   = Gectrl::init( self::$VAR1, self::$VAR2 );
        $package1 = $gectrl->getPackage();

        $this->assertInstanceOf(Package::class, $package1, 'error 111' );

        $this->assertTrue( $package1->isConfigSet(), 'error 41' );
        $this->assertEquals( self::$VAR1, $package1->getConfig(), 'error 142');

        $this->assertTrue( $package1->isLoggerSet(), 'error 51' );
        $this->assertEquals( self::$VAR2, $package1->getLogger(), 'error 152');

        $gectrl->setPackage( Package::init());
        $this->assertNotEquals(
            $package1->getTimestamp(),
            $gectrl->getPackage()->getTimestamp(),
            'error 161'
        );
        $this->assertNotEquals(
            $package1->getCorrelationId(),
            $gectrl->getPackage()->getCorrelationId(),
            'error 162'
        );

        $this->assertFalse(
            $gectrl->isActionClassSet(),
            'error 181'
        );

        $this->assertNotFalse(
            strpos(
                $package1->getLoadStatus(),
                $package1->getCorrelationId()
            ),
            'error 191'
        );
    }

    /**
     * Testing Gectrl::main
     *
     * @test
     */
    public function gectrlTest2() {
        $actionClasses = [
            AcSrc\OtherInterface::class,
            AcSrc\OtherTrait::class,
            AcSrc\Action0Base::class,
            AcSrc\Action1Test::class,
            AcSrc\ActionExampleTest::class,
            AcSrc\Action3Test::class,
            AcSrc\Action4Test::class
        ];
        $gectrl   = Gectrl::init(
            null,
            null,
            $actionClasses
        );

        $this->assertTrue(
            $gectrl->isActionClassSet(),
            'error 211'
        );
        $this->assertTrue(
            $gectrl->isActionClassSet( AcSrc\ActionExampleTest::class ),
            'error 212a'
        );
        $this->assertFalse(
            $gectrl->isActionClassSet( AcSrc\OtherInterface::class ),
            'error 212b'
        );
        $this->assertFalse(
            $gectrl->isActionClassSet( AcSrc\OtherTrait::class ),
            'error 212c'
        );

        $this->assertNotEquals(
            count(  $actionClasses ),
            count( $gectrl->getActionClasses()),
            'error 214'
        );

        $package = $gectrl->main( self::$VAR1 );

        $this->assertTrue( $package->isWorkDataKeySet( AcSrc\ActionExampleTest::class ), 'error 221' );
        $this->assertTrue( $package->isResultLogKeySet( AcSrc\ActionExampleTest::class ), 'error 222' );
        $this->assertEquals( $package->getInput(), $package->getOutput(), 'error 223' );
    }

    /**
     * Testing Gectrl:main assert 1, no input/no ectionClasses
     *
     * @test
     */
    public function gectrlTest3() {
        $detected = false;
        try {
            $package = Gectrl::init()->main();
        }
        catch( RuntimeException $re ) {
            $detected = true;
        }
        $this->assertTrue( $detected, 'error 311' );

        $detected = false;
        try {
            $package = Gectrl::init()->main( self::$VAR1 );
        }
        catch( RuntimeException $re ) {
            $detected = true;
        }
        $this->assertTrue( $detected, 'error 321' );
    }

    /**
     * Testing Gectrl, actionClass assert 2, invalid actionClasses
     *
     * @test
     */
    public function gectrlTest4() {
        $detected = false;
        try {
            $gectrl = Gectrl::init(
                null,
                null,
                [ self::$VAR1 ]
            );
        }
        catch( InvalidArgumentException $ie ) {
                $detected = true;
        }
        $this->assertTrue( $detected, 'error 411' );

        $detected = false;
        try {
            $gectrl = Gectrl::init(
                null,
                null,
                [ AcSrc\OtherTest::class ]
            );
        }
        catch( InvalidArgumentException $ie ) {
            $detected = true;
        }
        $this->assertTrue( $detected, 'error 421' );
    }

    /**
     * Testing Gectrl, main, package input
     *
     * @test
     */
    public function gectrlTest5() {
        $package1 = Package::init( self::$VAR1, self::$VAR2, self::$VAR3 );

        $package2 = Gectrl::init(
            null,
            null,
            [ AcSrc\ActionExampleTest::class ]
        )
            ->main( $package1 );

        $this->assertEquals( self::$VAR3, $package2->getOutput(), 'error 511');
    }
}

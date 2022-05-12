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
use PHPUnit\Framework\TestCase;
use RuntimeException;

class GectrlTest extends TestCase
{
    private static string $VAR1 = 'var1';
    private static string $VAR2 = 'var2';
    private static string $VAR3 = 'var3';

    /**
     * Testing Gectrl and Package
     *
     * @test
     * @throws Exception
     */
    public function gectrlTest1() : void
    {
        $gectrl   = Gectrl::init( self::$VAR1, self::$VAR2 );
        $package1 = new Package();

        $this->assertTrue( $gectrl->isConfigSet(), 'error 41' );
        $this->assertEquals( self::$VAR1, $gectrl->getConfig(), 'error 142');

        $gectrl->setConfig( self::$VAR3 );
        $this->assertEquals( self::$VAR3, $gectrl->getConfig(), 'error 143');

        $this->assertTrue( $gectrl->isLoggerSet(), 'error 51' );
        $this->assertEquals( self::$VAR2, $gectrl->getLogger(), 'error 152');

        $gectrl->setLogger( self::$VAR3 );
        $this->assertEquals( self::$VAR3, $gectrl->getLogger(), 'error 153');

        $gectrl->setPackage( Package::init( self::$VAR3 ));
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
     * Testing Gectrl, main, Package with no input
     *
     * @test
     * @throws Exception
     */
    public function gectrlTest2() : void
    {
        $detected = false;
        try {
            Gectrl::init()->setPackage( Package::init());
        }
        catch( InvalidArgumentException $ie ) {
            $detected = true;
        }
        $this->assertTrue( $detected, 'error 211' );
    }

    /**
     * Testing Gectrl::main
     *
     * @test
     * @throws Exception
     */
    public function gectrlTest3() : void
    {
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
            'error 311'
        );
        $this->assertTrue(
            $gectrl->isActionClassSet( AcSrc\ActionExampleTest::class ),
            'error 312a'
        );
        $this->assertFalse(
            $gectrl->isActionClassSet( AcSrc\OtherInterface::class ),
            'error 312b'
        );
        $this->assertFalse(
            $gectrl->isActionClassSet( AcSrc\OtherTrait::class ),
            'error 312c'
        );

        $this->assertNotCount(
            count( $actionClasses ), $gectrl->getActionClasses(), 'error 321'
        );

        $package = $gectrl->main( self::$VAR1 );

        $this->assertTrue( $package->isWorkDataKeySet( AcSrc\ActionExampleTest::class ), 'error 221' );
        $this->assertTrue( $package->isResultLogKeySet( AcSrc\ActionExampleTest::class ), 'error 222' );
        $this->assertEquals( $package->getInput(), $package->getOutput(), 'error 331' );
    }

    /**
     * Testing Gectrl:main assert 1, no input/no ActionClasses
     *
     * @test
     * @throws Exception
     * @throws Exception
     */
    public function gectrlTest4() : void
    {
        $detected = false;
        try {
            $package = Gectrl::init()->main();
        }
        catch( RuntimeException $re ) {
            $detected = true;
        }
        $this->assertTrue( $detected, 'error 411' );

        $detected = false;
        try {
            $package = Gectrl::init()->main( self::$VAR1 );
        }
        catch( RuntimeException $re ) {
            $detected = true;
        }
        $this->assertTrue( $detected, 'error 421' );
    }

    /**
     * Testing Gectrl, actionClass assert 2, invalid actionClasses
     *
     * @test
     * @throws Exception
     */
    public function gectrlTest5() : void
    {
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
        $this->assertTrue( $detected, 'error 511' );

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
        $this->assertTrue( $detected, 'error 521' );
    }

    /**
     * Testing Gectrl, processOne (main)
     *
     * @test
     * @throws Exception
     */
    public function gectrlTest6() : void
    {
        $package1 = Package::init( self::$VAR1 );

        $package2 = Gectrl::init(
            null,
            null,
            [ AcSrc\ActionExampleTest::class ]
        )
            ->processOne( $package1 );

        $this->assertEquals( self::$VAR1, $package2->getOutput(), 'error 611');
    }

    /**
     * Testing Gectrl, processMany
     *
     * @test
     * @throws Exception
     */
    public function gectrlTest7() : void
    {
        $transactions = [
            self::$VAR1,
            self::$VAR2,
            self::$VAR3
        ];
        $input = [];
        foreach( $transactions as $trans ) {
            $input[] = Package::init( $trans );
        }

        $output = Gectrl::init(
            null,
            null,
            [ AcSrc\ActionExampleTest::class ]
        )
            ->processMany( $input );

        foreach( $transactions as $tix => $trans ) {
            $this->assertEquals( $trans, $output[$tix]->getOutput(), 'error 61' . $tix );
        }
    }
}

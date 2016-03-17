<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Tests;

use PhpCsFixer\ReportFactory;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @internal
 */
final class ReportFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testInterfaceIsFluent()
    {
        $factory = new ReportFactory();

        $testInstance = $factory->registerBuiltInReports();
        $this->assertSame($factory, $testInstance);

        $mock = $this->createReportMock('r1');
        $testInstance = $factory->registerReport($mock);
        $this->assertSame($factory, $testInstance);
    }

    /**
     * @covers PhpCsFixer\ReportFactory::registerBuiltInReports
     */
    public function testRegisterBuiltInReports()
    {
        $factory = new ReportFactory();
        $factory->registerBuiltInReports();

        $refObject = new \ReflectionObject($factory);
        $refProperty = $refObject->getProperty('reports');
        $refProperty->setAccessible(true);

        $this->assertGreaterThan(0, count($refProperty->getValue($factory)));

        $refProperty->setAccessible(false);
    }

    /**
     * @covers PhpCsFixer\ReportFactory::getReportByFormat
     * @covers PhpCsFixer\ReportFactory::registerReport
     */
    public function testThatCanRegisterAndGetReports()
    {
        $factory = new ReportFactory();

        $r1 = $this->createReportMock('r1');
        $r2 = $this->createReportMock('r2');
        $r3 = $this->createReportMock('r3');

        $factory->registerReport($r1);
        $factory->registerReport($r2);
        $factory->registerReport($r3);

        $this->assertSame($r1, $factory->getReportByFormat('r1'));
        $this->assertSame($r2, $factory->getReportByFormat('r2'));
        $this->assertSame($r3, $factory->getReportByFormat('r3'));
    }

    /**
     * @covers PhpCsFixer\ReportFactory::registerReport
     * @expectedException        \UnexpectedValueException
     * @expectedExceptionMessage Report for format "non_unique_name" is already registered.
     */
    public function testRegisterReportWithOccupiedFormat()
    {
        $factory = new ReportFactory();

        $r1 = $this->createReportMock('non_unique_name');
        $r2 = $this->createReportMock('non_unique_name');
        $factory->registerReport($r1);
        $factory->registerReport($r2);
    }

    /**
     * @covers PhpCsFixer\ReportFactory::getReportByFormat
     * @expectedException        \UnexpectedValueException
     * @expectedExceptionMessage Report for format "non_registered_format" does not registered.
     */
    public function testGetNonRegisteredReport()
    {
        $factory = new ReportFactory();

        $factory->getReportByFormat('non_registered_format');
    }

    /**
     * @covers PhpCsFixer\ReportFactory::setIsDryRun
     * @covers PhpCsFixer\ReportFactory::setIsDecoratedOutput
     * @covers PhpCsFixer\ReportFactory::setAddAppliedFixers
     * @covers PhpCsFixer\ReportFactory::setStopwatch
     */
    public function testSetters()
    {
        $factory = new ReportFactory();
        $factory->registerBuiltInReports();

        $refObject = new \ReflectionObject($factory);
        $refProperty = $refObject->getProperty('options');
        $refProperty->setAccessible(true);

        $stopwatch = new Stopwatch();

        $factory->setIsDryRun(true);
        $factory->setIsDecoratedOutput(true);
        $factory->setAddAppliedFixers(true);
        $factory->setStopwatch($stopwatch);

        $expectedOptions = array(
            'dry-run' => true,
            'decorated-output' => true,
            'add-applied-fixers' => true,
            'stopwatch' => $stopwatch,
        );

        $this->assertSame($expectedOptions, $refProperty->getValue($factory));

        $refProperty->setAccessible(false);
    }

    private function createReportMock($format)
    {
        $report = $this->getMock('PhpCsFixer\ReportInterface');
        $report->expects($this->any())->method('getFormat')->willReturn($format);

        return $report;
    }
}

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

namespace PhpCsFixer\Tests\Report;

use PhpCsFixer\Report\JsonReport;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

/**
 * @author Boris Gorbylev <ekho@ekho.name>
 *
 * @internal
 */
final class JsonReportTest extends \PHPUnit_Framework_TestCase
{
    /** @var Stopwatch|\PHPUnit_Framework_MockObject_MockObject */
    private $mockStopwatch;

    /** @var JsonReport */
    private $report;

    public function testProcessSimple()
    {
        $this->report->setDecoratedOutput(false);
        $this->report->setShowAppliedFixers(false);
        $this->report->setShowDiff(false);
        $this->report->setDryRun(false);

        $expectedJson = '{"files":[{"name":"someFile.php"}],"memory":2.5,"time":{"total":1.234}}';

        $actualJson = $this->report->process(
            array(
                'someFile.php' => array(
                    'appliedFixers' => array('some_fixer_name_here'),
                ),
            )
        );

        $this->assertJsonStringEqualsJsonString($expectedJson, $actualJson);
    }

    public function testProcessComplex()
    {
        $this->report->setDecoratedOutput(true);
        $this->report->setShowAppliedFixers(true);
        $this->report->setShowDiff(true);
        $this->report->setDryRun(true);

        $expectedJson = '{"files":[{"name":"someFile.php","appliedFixers":["some_fixer_name_here"],"diff":"this text is a diff ;)"}],"memory":2.5,"time":{"total":1.234}}';

        $actualJson = $this->report->process(
            array(
                'someFile.php' => array(
                    'appliedFixers' => array('some_fixer_name_here'),
                    'diff' => 'this text is a diff ;)',
                ),
            )
        );

        $this->assertJsonStringEqualsJsonString($expectedJson, $actualJson);
    }

    protected function setUp()
    {
        /* @var StopwatchEvent|\PHPUnit_Framework_MockObject_MockObject */
        $mockEvent = $this->getMockBuilder('Symfony\Component\Stopwatch\StopwatchEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $mockEvent
            ->expects($this->once())
            ->method('getMemory')
            ->willReturn(2.5 * 1024 * 1024);
        $mockEvent
            ->expects($this->once())
            ->method('getDuration')
            ->willReturn(1234);

        $this->mockStopwatch = $this->getMock('Symfony\Component\Stopwatch\Stopwatch');
        $this->mockStopwatch
            ->expects($this->once())
            ->method('getEvent')
            ->with($this->equalTo('fixFiles'))
            ->willReturn($mockEvent);

        $this->report = new JsonReport();
        $this->report->setStopwatch($this->mockStopwatch);
    }
}

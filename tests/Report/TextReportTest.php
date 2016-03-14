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

use PhpCsFixer\Report\TextReport;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

/**
 * @author Boris Gorbylev <ekho@ekho.name>
 *
 * @internal
 */
final class TextReportTest extends \PHPUnit_Framework_TestCase
{
    /** @var Stopwatch|\PHPUnit_Framework_MockObject_MockObject */
    private $mockStopwatch;

    /** @var TextReport */
    private $report;

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

        $this->report = new TextReport();
        $this->report->setStopwatch($this->mockStopwatch);
    }

    public function testProcessSimple()
    {
        $this->report->setDecoratedOutput(false);
        $this->report->setShowAppliedFixers(false);
        $this->report->setShowDiff(false);
        $this->report->setDryRun(false);

        $expectedtext = <<<'TEXT'
   1) someFile.php
Fixed all files in 1.234 seconds, 2.500 MB memory used

TEXT;

        $actualText = $this->report->process(
            array(
                'someFile.php' => array(
                    'appliedFixers' => array('some_fixer_name_here'),
                ),
            )
        );

        $this->assertSame($expectedtext, $actualText);
    }

    public function testProcessComplex()
    {
        $this->report->setDecoratedOutput(true);
        $this->report->setShowAppliedFixers(true);
        $this->report->setShowDiff(true);
        $this->report->setDryRun(true);

        $expectedtext = <<<'TEXT'
   1) someFile.php (<comment>some_fixer_name_here</comment>)
<comment>      ---------- begin diff ----------</comment>
this text is a diff ;)
<comment>      ---------- end diff ----------</comment>

Checked all files in 1.234 seconds, 2.500 MB memory used

TEXT;

        $actualText = $this->report->process(
            array(
                'someFile.php' => array(
                    'appliedFixers' => array('some_fixer_name_here'),
                    'diff' => 'this text is a diff ;)',
                ),
            )
        );

        $this->assertSame($expectedtext, $actualText);
    }
}

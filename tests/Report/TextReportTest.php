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
    /** @var TextReport */
    private $report;

    protected function setUp()
    {
        $this->report = new TextReport();
    }

    /**
     * @covers PhpCsFixer\Report\TextReport::getFormat
     */
    public function testGetFormat()
    {
        $this->assertSame('txt', $this->report->getFormat());
    }

    /**
     * @covers PhpCsFixer\Report\TextReport::process
     */
    public function testProcessSimple()
    {
        $expectedtext = str_replace("\n", PHP_EOL, <<<'TEXT'

   1) someFile.php

TEXT
        );

        $actualText = $this->report->process(
            array(
                'someFile.php' => array(
                    'appliedFixers' => array('some_fixer_name_here'),
                ),
            )
        );

        $this->assertSame($expectedtext, $actualText);
    }

    public function testProcessWithDiff()
    {
        $expectedtext = str_replace("\n", PHP_EOL, <<<'TEXT'

   1) someFile.php
      ---------- begin diff ----------
this text is a diff ;)
      ----------- end diff -----------

TEXT
        );

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

    public function testProcessWithAppliedFixers()
    {
        $this->report->configure(array('add-applied-fixers' => true));

        $expectedtext = str_replace("\n", PHP_EOL, <<<'TEXT'

   1) someFile.php (some_fixer_name_here)

TEXT
        );

        $actualText = $this->report->process(
            array(
                'someFile.php' => array(
                    'appliedFixers' => array('some_fixer_name_here'),
                ),
            )
        );

        $this->assertSame($expectedtext, $actualText);
    }

    public function testProcessWithStopwatch()
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

        /* @var Stopwatch|\PHPUnit_Framework_MockObject_MockObject */
        $mockStopwatch = $this->getMock('Symfony\Component\Stopwatch\Stopwatch');
        $mockStopwatch
            ->expects($this->once())
            ->method('getEvent')
            ->with($this->equalTo('fixFiles'))
            ->willReturn($mockEvent);

        $this->report->configure(array('stopwatch' => $mockStopwatch));

        $expectedtext = str_replace("\n", PHP_EOL, <<<'TEXT'

   1) someFile.php

Fixed all files in 1.234 seconds, 2.500 MB memory used

TEXT
        );

        $actualText = $this->report->process(
            array(
                'someFile.php' => array(
                    'appliedFixers' => array('some_fixer_name_here'),
                ),
            )
        );

        $this->assertSame($expectedtext, $actualText);
    }

    public function testProcessComplexWithDecoratedOutput()
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

        /* @var Stopwatch|\PHPUnit_Framework_MockObject_MockObject */
        $mockStopwatch = $this->getMock('Symfony\Component\Stopwatch\Stopwatch');
        $mockStopwatch
            ->expects($this->once())
            ->method('getEvent')
            ->with($this->equalTo('fixFiles'))
            ->willReturn($mockEvent);

        $this->report->configure(array(
            'add-applied-fixers' => true,
            'dry-run' => true,
            'decorated-output' => true,
            'stopwatch' => $mockStopwatch,
        ));

        $expectedtext = str_replace("\n", PHP_EOL, <<<'TEXT'

   1) someFile.php (<comment>some_fixer_name_here</comment>)
<comment>      ---------- begin diff ----------</comment>
this text is a diff ;)
<comment>      ----------- end diff -----------</comment>

   2) anotherFile.php (<comment>another_fixer_name_here</comment>)
<comment>      ---------- begin diff ----------</comment>
another diff here ;)
<comment>      ----------- end diff -----------</comment>

Checked all files in 1.234 seconds, 2.500 MB memory used

TEXT
        );

        $actualText = $this->report->process(
            array(
                'someFile.php' => array(
                    'appliedFixers' => array('some_fixer_name_here'),
                    'diff' => 'this text is a diff ;)',
                ),
                'anotherFile.php' => array(
                    'appliedFixers' => array('another_fixer_name_here'),
                    'diff' => 'another diff here ;)',
                ),
            )
        );

        $this->assertSame($expectedtext, $actualText);
    }
}

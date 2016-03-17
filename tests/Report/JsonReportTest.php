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
    /** @var JsonReport */
    private $report;

    protected function setUp()
    {
        $this->report = new JsonReport();
    }

    /**
     * @covers PhpCsFixer\Report\JsonReport::getFormat
     */
    public function testGetFormat()
    {
        $this->assertSame('json', $this->report->getFormat());
    }

    public function testProcessSimple()
    {
        $expectedJson = <<<'JSON'
{
    "files":[
        {
            "name": "someFile.php"
        }
    ]
}
JSON;
        $actualJson = $this->report->process(
            array(
                'someFile.php' => array(
                    'appliedFixers' => array('some_fixer_name_here'),
                ),
            )
        );

        $this->assertJsonStringEqualsJsonString($expectedJson, $actualJson);
    }

    public function testProcessWithDiff()
    {
        $expectedJson = <<<'JSON'
{
    "files":[
        {
            "name": "someFile.php",
            "diff": "this text is a diff ;)"
        }
    ]
}
JSON;
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

    public function testProcessWithAppliedFixers()
    {
        $this->report->configure(array('add-applied-fixers' => true));

        $expectedJson = <<<'JSON'
{
    "files":[
        {
            "name": "someFile.php",
            "appliedFixers":["some_fixer_name_here"]
        }
    ]
}
JSON;
        $actualJson = $this->report->process(
            array(
                'someFile.php' => array(
                    'appliedFixers' => array('some_fixer_name_here'),
                ),
            )
        );

        $this->assertJsonStringEqualsJsonString($expectedJson, $actualJson);
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

        $expectedJson = <<<'JSON'
{
    "files":[
        {
            "name": "someFile.php"
        }
    ],
    "memory": 2.5,
    "time": {
        "total": 1.234
    }
}
JSON;
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
            'stopwatch' => $mockStopwatch,
        ));

        $expectedJson = <<<'JSON'
{
    "files":[
        {
            "name": "someFile.php",
            "appliedFixers":["some_fixer_name_here"],
            "diff": "this text is a diff ;)"
        },
        {
            "name": "anotherFile.php",
            "appliedFixers":["another_fixer_name_here"],
            "diff": "another diff here ;)"
        }
    ],
    "memory": 2.5,
    "time": {
        "total": 1.234
    }
}
JSON;
        $actualJson = $this->report->process(
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

        $this->assertJsonStringEqualsJsonString($expectedJson, $actualJson);
    }
}

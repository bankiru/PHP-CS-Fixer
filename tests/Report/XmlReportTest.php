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

use PhpCsFixer\Report\XmlReport;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

/**
 * @author Boris Gorbylev <ekho@ekho.name>
 *
 * @internal
 */
final class XmlReportTest extends \PHPUnit_Framework_TestCase
{
    /** @var Stopwatch|\PHPUnit_Framework_MockObject_MockObject */
    private $mockStopwatch;

    /** @var XmlReport */
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

        $this->report = new XmlReport();
        $this->report->setStopwatch($this->mockStopwatch);
    }

    public function testProcessSimple()
    {
        $this->report->setDecoratedOutput(false);
        $this->report->setShowAppliedFixers(false);
        $this->report->setShowDiff(false);
        $this->report->setDryRun(false);

        $expectedXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<report>
  <files>
    <file id="1" name="someFile.php"/>
  </files>
  <time unit="s">
    <total value="1.234"/>
  </time>
  <memory value="2.5" unit="MB"/>
</report>
XML;

        $actualXml = $this->report->process(
            array(
                'someFile.php' => array(
                    'appliedFixers' => array('some_fixer_name_here'),
                ),
            )
        );

        $this->assertXmlStringEqualsXmlString($expectedXml, $actualXml);
    }

    public function testProcessComplex()
    {
        $this->report->setDecoratedOutput(true);
        $this->report->setShowAppliedFixers(true);
        $this->report->setShowDiff(true);
        $this->report->setDryRun(true);

        $expectedXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<report>
  <files>
    <file id="1" name="someFile.php">
      <applied_fixers>
        <applied_fixer name="some_fixer_name_here"/>
      </applied_fixers>
      <diff><![CDATA[this text is a diff ;)]]></diff>
    </file>
  </files>
  <time unit="s">
    <total value="1.234"/>
  </time>
  <memory value="2.5" unit="MB"/>
</report>
XML;

        $actualXml = $this->report->process(
            array(
                'someFile.php' => array(
                    'appliedFixers' => array('some_fixer_name_here'),
                    'diff' => 'this text is a diff ;)',
                ),
            )
        );

        $this->assertXmlStringEqualsXmlString($expectedXml, $actualXml);
    }
}

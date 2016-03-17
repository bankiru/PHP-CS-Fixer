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
    /** @var XmlReport */
    private $report;

    protected function setUp()
    {
        $this->report = new XmlReport();
    }

    /**
     * @covers PhpCsFixer\Report\XmlReport::getFormat
     */
    public function testGetFormat()
    {
        $this->assertSame('xml', $this->report->getFormat());
    }

    public function testProcessSimple()
    {
        $expectedXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<report>
  <files>
    <file id="1" name="someFile.php"/>
  </files>
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

    public function testProcessWithDiff()
    {
        $expectedXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<report>
  <files>
    <file id="1" name="someFile.php">
      <diff><![CDATA[this text is a diff ;)]]></diff>
    </file>
  </files>
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

    public function testProcessWithAppliedFixers()
    {
        $this->report->configure(array('add-applied-fixers' => true));

        $expectedXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<report>
  <files>
    <file id="1" name="someFile.php">
      <applied_fixers>
        <applied_fixer name="some_fixer_name_here"/>
      </applied_fixers>
    </file>
  </files>
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
            ->expects($this->exactly(2))
            ->method('getEvent')
            ->with($this->equalTo('fixFiles'))
            ->willReturn($mockEvent);

        $this->report->configure(array('stopwatch' => $mockStopwatch));

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
            ->expects($this->exactly(2))
            ->method('getEvent')
            ->with($this->equalTo('fixFiles'))
            ->willReturn($mockEvent);

        $this->report->configure(array(
            'add-applied-fixers' => true,
            'stopwatch' => $mockStopwatch,
        ));

        $expectedXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<report>
  <files>
    <file id="1" name="someFile.php">
      <applied_fixers>
        <applied_fixer name="some_fixer_name_here"/>
      </applied_fixers>
      <diff>this text is a diff ;)</diff>
    </file>
    <file id="2" name="anotherFile.php">
      <applied_fixers>
        <applied_fixer name="another_fixer_name_here"/>
      </applied_fixers>
      <diff>another diff here ;)</diff>
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
                'anotherFile.php' => array(
                    'appliedFixers' => array('another_fixer_name_here'),
                    'diff' => 'another diff here ;)',
                ),
            )
        );

        $this->assertXmlStringEqualsXmlString($expectedXml, $actualXml);
    }
}

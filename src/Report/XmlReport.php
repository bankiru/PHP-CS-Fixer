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

namespace PhpCsFixer\Report;

use PhpCsFixer\ReportInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @internal
 */
final class XmlReport implements ReportInterface
{
    /** @var bool */
    private $addAppliedFixers = false;

    /** @var Stopwatch */
    private $stopwatch;

    /**
     * {@inheritdoc}
     */
    public function getFormat()
    {
        return 'xml';
    }

    /**
     * {@inheritdoc}
     */
    public function configure(array $options)
    {
        $this->addAppliedFixers = isset($options['add-applied-fixers']) && $options['add-applied-fixers'];
        if (isset($options['stopwatch'])) {
            $this->stopwatch = $options['stopwatch'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $changed)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        // new nodes should be added to this or existing children
        $root = $dom->createElement('report');
        $dom->appendChild($root);

        $filesXML = $dom->createElement('files');
        $root->appendChild($filesXML);

        $i = 1;
        foreach ($changed as $file => $fixResult) {
            $fileXML = $dom->createElement('file');
            $fileXML->setAttribute('id', $i++);
            $fileXML->setAttribute('name', $file);
            $filesXML->appendChild($fileXML);

            if ($this->addAppliedFixers) {
                $fileXML->appendChild($this->createAppliedFixersElement($dom, $fixResult));
            }

            if (!empty($fixResult['diff'])) {
                $fileXML->appendChild($this->createDiffElement($dom, $fixResult));
            }
        }

        if ($this->stopwatch) {
            $root->appendChild($this->createTimeElement($dom));
            $root->appendChild($this->createMemoryElement($dom));
        }

        $dom->formatOutput = true;

        return $dom->saveXML();
    }

    /**
     * @param \DOMDocument $dom
     *
     * @return \DOMElement
     */
    private function createTimeElement(\DOMDocument $dom)
    {
        $time = round($this->stopwatch->getEvent('fixFiles')->getDuration() / 1000, 3);

        $timeXML = $dom->createElement('time');
        $timeXML->setAttribute('unit', 's');
        $timeTotalXML = $dom->createElement('total');
        $timeTotalXML->setAttribute('value', $time);
        $timeXML->appendChild($timeTotalXML);

        return $timeXML;
    }

    /**
     * @param \DOMDocument $dom
     *
     * @return \DOMElement
     */
    private function createMemoryElement(\DOMDocument $dom)
    {
        $memory = round($this->stopwatch->getEvent('fixFiles')->getMemory() / 1024 / 1024, 3);

        $memoryXML = $dom->createElement('memory');
        $memoryXML->setAttribute('value', $memory);
        $memoryXML->setAttribute('unit', 'MB');

        return $memoryXML;
    }

    /**
     * @param \DOMDocument $dom
     * @param array        $fixResult
     *
     * @return \DOMElement
     */
    private function createDiffElement(\DOMDocument $dom, array $fixResult)
    {
        $diffXML = $dom->createElement('diff');
        $diffXML->appendChild($dom->createCDATASection($fixResult['diff']));

        return $diffXML;
    }

    /**
     * @param \DOMDocument $dom
     * @param array        $fixResult
     *
     * @return \DOMElement
     */
    private function createAppliedFixersElement($dom, $fixResult)
    {
        $appliedFixersXML = $dom->createElement('applied_fixers');

        foreach ($fixResult['appliedFixers'] as $appliedFixer) {
            $appliedFixerXML = $dom->createElement('applied_fixer');
            $appliedFixerXML->setAttribute('name', $appliedFixer);
            $appliedFixersXML->appendChild($appliedFixerXML);
        }

        return $appliedFixersXML;
    }
}

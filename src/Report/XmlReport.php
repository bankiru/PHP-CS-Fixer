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

/**
 * @internal
 */
final class XmlReport extends AbstractReport
{
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

            if ($this->showAppliedFixers) {
                $appliedFixersXML = $dom->createElement('applied_fixers');
                $fileXML->appendChild($appliedFixersXML);

                foreach ($fixResult['appliedFixers'] as $appliedFixer) {
                    $appliedFixerXML = $dom->createElement('applied_fixer');
                    $appliedFixerXML->setAttribute('name', $appliedFixer);
                    $appliedFixersXML->appendChild($appliedFixerXML);
                }
            }

            if ($this->showDiff) {
                $diffXML = $dom->createElement('diff');
                $diffXML->appendChild($dom->createCDATASection($fixResult['diff']));
                $fileXML->appendChild($diffXML);
            }
        }

        $fixEvent = $this->stopwatch->getEvent('fixFiles');

        $timeXML = $dom->createElement('time');
        $memoryXML = $dom->createElement('memory');
        $root->appendChild($timeXML);
        $root->appendChild($memoryXML);

        $memoryXML->setAttribute('value', round($fixEvent->getMemory() / 1024 / 1024, 3));
        $memoryXML->setAttribute('unit', 'MB');

        $timeXML->setAttribute('unit', 's');
        $timeTotalXML = $dom->createElement('total');
        $timeTotalXML->setAttribute('value', round($fixEvent->getDuration() / 1000, 3));
        $timeXML->appendChild($timeTotalXML);

        $dom->formatOutput = true;

        return $dom->saveXML();
    }
}

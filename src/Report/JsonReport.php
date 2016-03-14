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
final class JsonReport extends AbstractReport
{
    /**
     * {@inheritdoc}
     */
    public function process(array $changed)
    {
        $jFiles = array();

        foreach ($changed as $file => $fixResult) {
            $jfile = array('name' => $file);

            if ($this->showAppliedFixers) {
                $jfile['appliedFixers'] = $fixResult['appliedFixers'];
            }

            if ($this->showDiff) {
                $jfile['diff'] = $fixResult['diff'];
            }

            $jFiles[] = $jfile;
        }

        $fixEvent = $this->stopwatch->getEvent('fixFiles');

        $json = array(
            'files' => $jFiles,
            'memory' => round($fixEvent->getMemory() / 1024 / 1024, 3),
            'time' => array(
                'total' => round($fixEvent->getDuration() / 1000, 3),
            ),
        );

        return json_encode($json);
    }
}

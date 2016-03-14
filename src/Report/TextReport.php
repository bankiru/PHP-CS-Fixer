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
final class TextReport extends AbstractReport
{
    /**
     * {@inheritdoc}
     */
    public function process(array $changed)
    {
        $output = '';

        $fixerDetailLine = $this->decoratedOutput ? ' (<comment>%s</comment>)' : ' %s';

        $i = 1;
        foreach ($changed as $file => $fixResult) {
            $output .= sprintf('%4d) %s', $i++, $file);

            if ($this->showAppliedFixers) {
                $output .= sprintf($fixerDetailLine, implode(', ', $fixResult['appliedFixers']));
            }

            if ($this->showDiff) {
                $output .= PHP_EOL;
                $output .= '<comment>      ---------- begin diff ----------</comment>'.PHP_EOL;
                $output .= $fixResult['diff'].PHP_EOL;
                $output .= '<comment>      ---------- end diff ----------</comment>'.PHP_EOL;
            }

            $output .= PHP_EOL;
        }

        $fixEvent = $this->stopwatch->getEvent('fixFiles');

        $output .= sprintf(
                '%s all files in %.3f seconds, %.3f MB memory used',
                $this->dryRun ? 'Checked' : 'Fixed',
                $fixEvent->getDuration() / 1000,
                $fixEvent->getMemory() / 1024 / 1024
            ).PHP_EOL;

        return $output;
    }
}

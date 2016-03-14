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

use Symfony\Component\Stopwatch\Stopwatch;

interface ReportInterface
{
    /**
     * @param int $showAppliedFixers
     */
    public function setShowAppliedFixers($showAppliedFixers);

    /**
     * @param bool $needDiff
     */
    public function setShowDiff($needDiff);

    /**
     * @param bool $dryRun
     */
    public function setDryRun($dryRun);

    /**
     * @param bool $decoratedOutput
     */
    public function setDecoratedOutput($decoratedOutput);

    /**
     * Sets the Stopwatch.
     *
     * @param Stopwatch $stopwatch
     */
    public function setStopwatch(Stopwatch $stopwatch);

    /**
     * Process changed files array. Returns generated report.
     *
     * @param array $changed
     *
     * @return string
     */
    public function process(array $changed);
}

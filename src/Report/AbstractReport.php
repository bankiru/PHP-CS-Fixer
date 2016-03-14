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

abstract class AbstractReport implements ReportInterface
{
    /** @var bool */
    protected $showAppliedFixers = false;

    /** @var bool */
    protected $showDiff = false;

    /** @var bool */
    protected $dryRun = false;

    /** @var bool */
    protected $decoratedOutput = false;

    /** @var Stopwatch */
    protected $stopwatch;

    /**
     * {@inheritdoc}
     */
    public function setShowAppliedFixers($showAppliedFixers)
    {
        $this->showAppliedFixers = $showAppliedFixers;
    }

    /**
     * {@inheritdoc}
     */
    public function setShowDiff($showDiff)
    {
        $this->showDiff = $showDiff;
    }

    /**
     * {@inheritdoc}
     */
    public function setDryRun($dryRun)
    {
        $this->dryRun = $dryRun;
    }

    /**
     * {@inheritdoc}
     */
    public function setDecoratedOutput($decoratedOutput)
    {
        $this->decoratedOutput = $decoratedOutput;
    }

    /**
     * {@inheritdoc}
     */
    public function setStopwatch(Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }
}

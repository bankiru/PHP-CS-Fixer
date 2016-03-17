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

namespace PhpCsFixer;

use Symfony\Component\Finder\Finder as SymfonyFinder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @internal
 */
final class ReportFactory
{
    /** @var ReportInterface[] */
    private $reports = array();

    private $options = array(
        'dry-run' => false,
        'decorated-output' => false,
        'add-applied-fixers' => false,
        'stopwatch' => null,
    );

    public function registerBuiltInReports()
    {
        static $builtInReports = null;

        if (null === $builtInReports) {
            $builtInReports = array();

            /** @var SplFileInfo $file */
            foreach (SymfonyFinder::create()->files()->name('*Report.php')->in(__DIR__.'/Report') as $file) {
                $relativeNamespace = $file->getRelativePath();
                $builtInReports[] = 'PhpCsFixer\\Report\\'.($relativeNamespace ? $relativeNamespace.'\\' : '').$file->getBasename('.php');
            }
        }

        foreach ($builtInReports as $class) {
            $this->registerReport(new $class());
        }

        return $this;
    }

    /**
     * @param ReportInterface $report
     *
     * @return $this
     */
    public function registerReport(ReportInterface $report)
    {
        $format = $report->getFormat();

        if (isset($this->reports[$format])) {
            throw new \UnexpectedValueException(sprintf('Report for format "%s" is already registered.', $format));
        }

        $this->reports[$format] = $report;

        return $this;
    }

    /**
     * @param bool $isDryRun
     *
     * @return $this
     */
    public function setIsDryRun($isDryRun)
    {
        $this->options['dry-run'] = $isDryRun;

        return $this;
    }

    /**
     * @param bool $isDecoratedOutput
     *
     * @return $this
     */
    public function setIsDecoratedOutput($isDecoratedOutput)
    {
        $this->options['decorated-output'] = $isDecoratedOutput;

        return $this;
    }

    /**
     * @param bool $addAppliedFixers
     *
     * @return $this
     */
    public function setAddAppliedFixers($addAppliedFixers)
    {
        $this->options['add-applied-fixers'] = $addAppliedFixers;

        return $this;
    }

    /**
     * @param Stopwatch $stopwatch
     *
     * @return $this
     */
    public function setStopwatch(Stopwatch $stopwatch)
    {
        $this->options['stopwatch'] = $stopwatch;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return ReportInterface
     */
    public function getReportByFormat($format)
    {
        if (!isset($this->reports[$format])) {
            throw new \UnexpectedValueException(sprintf('Report for format "%s" does not registered.', $format));
        }

        $report = $this->reports[$format];

        $report->configure($this->options);

        return $report;
    }
}

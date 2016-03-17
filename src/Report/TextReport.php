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
class TextReport implements ReportInterface
{
    /** @var bool */
    private $addAppliedFixers = false;

    /** @var bool */
    private $isDryRun = false;

    /** @var bool */
    private $isDecoratedOutput = false;

    /** @var Stopwatch */
    private $stopwatch;

    /**
     * {@inheritdoc}
     */
    public function getFormat()
    {
        return 'txt';
    }

    /**
     * {@inheritdoc}
     */
    public function configure(array $options)
    {
        $this->addAppliedFixers = isset($options['add-applied-fixers']) && $options['add-applied-fixers'];
        $this->isDryRun = isset($options['dry-run']) && $options['dry-run'];
        $this->isDecoratedOutput = isset($options['decorated-output']) && $options['decorated-output'];
        if (isset($options['stopwatch'])) {
            $this->stopwatch = $options['stopwatch'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $changed)
    {
        $output = '';

        $i = 1;
        foreach ($changed as $file => $fixResult) {
            $output .= $this->getFile($file, $i++);
            $output .= $this->getAppliedFixers($fixResult);
            $output .= $this->getDiff($fixResult);
            $output .= PHP_EOL;
        }

        $output .= $this->getFooter();

        return $output;
    }

    /**
     * @param array $fixResult
     *
     * @return string
     */
    private function getFile($file, $i)
    {
        return PHP_EOL.sprintf('%4d) %s', $i, $file);
    }

    /**
     * @param array $fixResult
     *
     * @return string
     */
    private function getAppliedFixers($fixResult)
    {
        if (!$this->addAppliedFixers || empty($fixResult['appliedFixers'])) {
            return '';
        }

        $template = $this->isDecoratedOutput ? ' (<comment>%s</comment>)' : ' (%s)';

        return sprintf(
            $template,
            implode(', ', $fixResult['appliedFixers'])
        );
    }

    /**
     * @param array $fixResult
     *
     * @return string
     */
    private function getDiff($fixResult)
    {
        if (empty($fixResult['diff'])) {
            return '';
        }

        $template = '';

        if ($this->isDecoratedOutput) {
            $template .= '<comment>      ---------- begin diff ----------</comment>';
            $template .= PHP_EOL.'%s'.PHP_EOL;
            $template .= '<comment>      ----------- end diff -----------</comment>';
        } else {
            $template .= '      ---------- begin diff ----------';
            $template .= PHP_EOL.'%s'.PHP_EOL;
            $template .= '      ----------- end diff -----------';
        }

        return PHP_EOL.sprintf(
            $template,
            trim($fixResult['diff'])
        );
    }

    /**
     * @return string
     */
    private function getFooter()
    {
        if (!$this->stopwatch) {
            return '';
        }

        $fixEvent = $this->stopwatch->getEvent('fixFiles');

        return PHP_EOL.sprintf(
            '%s all files in %.3f seconds, %.3f MB memory used'.PHP_EOL,
            $this->isDryRun ? 'Checked' : 'Fixed',
            $fixEvent->getDuration() / 1000,
            $fixEvent->getMemory() / 1024 / 1024
        );
    }
}

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
final class JsonReport implements ReportInterface
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
        return 'json';
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
        $jFiles = array();

        foreach ($changed as $file => $fixResult) {
            $jfile = array('name' => $file);

            if ($this->addAppliedFixers) {
                $jfile['appliedFixers'] = $fixResult['appliedFixers'];
            }

            if (!empty($fixResult['diff'])) {
                $jfile['diff'] = $fixResult['diff'];
            }

            $jFiles[] = $jfile;
        }

        $json = array(
            'files' => $jFiles,
        );

        if ($this->stopwatch) {
            $fixEvent = $this->stopwatch->getEvent('fixFiles');
            $json['memory'] = round($fixEvent->getMemory() / 1024 / 1024, 3);
            $json['time'] = array(
                'total' => round($fixEvent->getDuration() / 1000, 3),
            );
        }

        return json_encode($json);
    }
}

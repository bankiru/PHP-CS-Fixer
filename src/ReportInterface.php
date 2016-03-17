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

interface ReportInterface
{
    /**
     * @return string
     */
    public function getFormat();

    /**
     * @param array $options
     */
    public function configure(array $options);

    /**
     * Process changed files array. Returns generated report.
     *
     * @param array $changed
     *
     * @return string
     */
    public function process(array $changed);
}

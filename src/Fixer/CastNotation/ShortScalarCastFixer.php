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

namespace PhpCsFixer\Fixer\CastNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author SpacePossum
 */
final class ShortScalarCastFixer extends AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function isCandidate(Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound(Token::getCastTokenKinds());
    }

    /**
     * {@inheritdoc}
     */
    public function fix(\SplFileInfo $file, Tokens $tokens)
    {
        static $castMap = array(
            'boolean' => 'bool',
            'integer' => 'int',
            'double' => 'float',
            'real' => 'float',
        );

        for ($index = 0, $count = $tokens->count(); $index  < $count; ++$index) {
            if (!$tokens[$index]->isCast()) {
                continue;
            }

            $castFrom = trim(substr($tokens[$index]->getContent(), 1, -1));
            $castFromLowered = strtolower($castFrom);

            if (!array_key_exists($castFromLowered, $castMap)) {
                continue;
            }

            $tokens[$index]->setContent(str_replace($castFrom, $castMap[$castFromLowered], $tokens[$index]->getContent()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Cast "(boolean)" and "(integer)" should be written as "(bool)" and "(int)". "(double)" and "(real)" as "(float)".';
    }
}

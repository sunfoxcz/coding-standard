<?php

declare(strict_types=1);

namespace Sunfox\CodingStandard\Fixer\Casing;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Fixer for rules defined in PSR2 ¶2.5.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @author Tomas Jacik <tomas.jacik@sunfox.cz>
 */
final class UppercaseConstantsFixer extends AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new FixerDefinition(
            'The PHP constants `TRUE`, `FALSE`, and `NULL` MUST be in upper case.',
            [new CodeSample("<?php\n\$a = false;\n\$b = True;\n\$c = nuLL;\n")]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isCandidate(Tokens $tokens)
    {
        return $tokens->isTokenKindFound(T_STRING);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isNativeConstant()) {
                continue;
            }

            if (
                $this->isNeighbourAccepted($tokens, $tokens->getPrevMeaningfulToken($index)) &&
                $this->isNeighbourAccepted($tokens, $tokens->getNextMeaningfulToken($index))
            ) {
                $tokens[$index] = new Token([$token->getId(), strtoupper($token->getContent())]);
            }
        }
    }

    /**
     * @param Tokens $tokens
     * @param int    $index
     *
     * @return bool
     */
    private function isNeighbourAccepted(Tokens $tokens, $index)
    {
        static $forbiddenTokens = [
            T_AS,
            T_CLASS,
            T_CONST,
            T_EXTENDS,
            T_IMPLEMENTS,
            T_INSTANCEOF,
            T_INSTEADOF,
            T_INTERFACE,
            T_NEW,
            T_NS_SEPARATOR,
            T_PAAMAYIM_NEKUDOTAYIM,
            T_TRAIT,
            T_USE,
            CT::T_USE_TRAIT,
            CT::T_USE_LAMBDA,
        ];

        $token = $tokens[$index];

        if ($token->equalsAny(['{', '}'])) {
            return FALSE;
        }

        return !$token->isGivenKind($forbiddenTokens);
    }
}

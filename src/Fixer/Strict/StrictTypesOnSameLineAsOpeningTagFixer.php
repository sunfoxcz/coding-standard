<?php declare(strict_types=1);

namespace Sunfox\CodingStandard\Fixer\Strict;

use PhpCsFixer\Fixer\DefinedFixerInterface;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class StrictTypesOnSameLineAsOpeningTagFixer implements FixerInterface, DefinedFixerInterface
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Strict type declaration has to be on same line as opening tag',
            [
                new CodeSample('
<?php

declare(strict_types=1);

namespace SomeNamespace;'),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAllTokenKindsFound([T_OPEN_TAG, T_WHITESPACE, T_DECLARE, T_STRING, '=', T_LNUMBER, ';']);
    }

    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        // ignore files with short open tag and ignore non-monolithic files
        if (!$tokens[0]->isGivenKind(T_OPEN_TAG) || !$tokens->isMonolithicPhp()) {
            return;
        }

        $declareIndex = $tokens->getNextMeaningfulToken(0);
        $sequence = $this->getDeclareStrictTypeSequence();
        $sequenceLocation = $tokens->findSequence($sequence, $declareIndex, null, false);
        if ($sequenceLocation === null) {
            // strict_types declaration not found

            return;
        }

        if ($tokens[0]->getContent() !== '<?php ') {
            $tokens[0] = new Token([T_OPEN_TAG, '<?php ']);
        }

        $tokens->clearRange(1, $declareIndex - 1);
    }

    /**
     * Must run after @see \PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer
     */
    public function getPriority(): int
    {
        return 0;
    }

    public function getName(): string
    {
        return self::class;
    }

    public function supports(SplFileInfo $file): bool
    {
        return true;
    }

    public function isRisky(): bool
    {
        return false;
    }

    /**
     * @return Token[]
     */
    private function getDeclareStrictTypeSequence()
    {
        static $sequence = null;

        // do not look for open tag, closing semicolon or empty lines;
        // - open tag is tested by isCandidate
        // - semicolon or end tag must be there to be valid PHP
        // - empty tokens and comments are dealt with later
        if ($sequence === null) {
            $sequence = [
                new Token([T_DECLARE, 'declare']),
                new Token('('),
                new Token([T_STRING, 'strict_types']),
                new Token('='),
                new Token([T_LNUMBER, '1']),
                new Token(')'),
            ];
        }

        return $sequence;
    }
}

<?php

declare(strict_types=1);

namespace Sunfox\CodingStandard\Fixer\Phpdoc;

use PhpCsFixer\AbstractPhpdocTypesFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;

/**
 * @author Tomas Jacik <tomas.jacik@sunfox.cz>
 */
final class PhpdocConstantFixer extends AbstractPhpdocTypesFixer
{
    /**
     * The types to process.
     *
     * @var string[]
     */
    private static $types = [
        'FALSE',
        'NULL',
        'TRUE',
    ];

    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new FixerDefinition(
            'The uppercase MUST be used for PHP constants in phpdoc.',
            [
                new CodeSample(
                    '<?php
/**
 * @param string|String[] $bar
 *
 * @return inT[]
 */
'
                ),
            ]
        );
    }

    public function getPriority()
    {
        /*
         * Should be run before all other docblock fixers apart from the
         * phpdoc_to_comment and phpdoc_indent fixer to make sure all fixers
         * apply correct indentation to new code they add. This should run
         * before alignment of params is done since this fixer might change
         * the type and thereby un-aligning the params. We also must run before
         * the phpdoc_scalar_fixer so that it can make changes after us.
         */
        return 16;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalize($type)
    {
        $upper = strtoupper($type);

        if (in_array($upper, self::$types, TRUE)) {
            return $upper;
        }

        return $type;
    }
}

<?php declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;

return function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/../vendor/symplify/easy-coding-standard/config/set/psr12.php');
    $containerConfigurator->import(__DIR__ . '/../vendor/symplify/easy-coding-standard/config/set/php71.php');
    $containerConfigurator->import(__DIR__ . '/../vendor/nette/coding-standard/preset/php71.php');

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::INDENTATION, Option::INDENTATION_SPACES);
    $parameters->set(Option::SKIP, [
        Nette\CodingStandard\Sniffs\WhiteSpace\FunctionSpacingSniff::class => null,
        PHP_CodeSniffer\Standards\Generic\Sniffs\WhiteSpace\DisallowSpaceIndentSniff::class => null,
        PHP_CodeSniffer\Standards\PSR2\Sniffs\ControlStructures\ControlStructureSpacingSniff::class . 'SpacingAfterOpenBrace' => null,
        PHP_CodeSniffer\Standards\PSR2\Sniffs\Methods\FunctionCallSignatureSniff::class . 'MultipleArguments' => null,
        PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\ControlStructureSpacingSniff::class . 'SpacingAfterOpenBrace' => null,
        PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\ControlStructureSpacingSniff::class . 'SpacingBeforeClose' => null,
        PhpCsFixer\Fixer\Operator\NewWithBracesFixer::class => null,
        PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer::class => null,
        SlevomatCodingStandard\Sniffs\Commenting\RequireOneLinePropertyDocCommentSniff::class => null,
    ]);

    $services = $containerConfigurator->services();

    // Detect dead code
    $services->set(SlevomatCodingStandard\Sniffs\Classes\UnusedPrivateElementsSniff::class);
    // Drop dead use namespaces
    $services->set(PhpCsFixer\Fixer\Import\NoUnusedImportsFixer::class);
    // Empty line after declare(strict_types=1);
    $services->set(Symplify\CodingStandard\Fixer\Strict\BlankLineAfterStrictTypesFixer::class);
    // Ensure declare(strict_types=1); is on the same line as <?php
    $services->set(Sunfox\CodingStandard\Fixer\Strict\StrictTypesOnSameLineAsOpeningTagFixer::class);
    // There MUST NOT be more than one constant or property declared per statement.
    $services->set(PhpCsFixer\Fixer\ClassNotation\SingleClassElementPerStatementFixer::class)
        ->call('configure', [[
            'elements' => ['const', 'property']
        ]]);
    // Final classes MUST NOT have protected properties
    $services->set(PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer::class);
    # Last property and 1st method should be separated by 1 space
    $services->set(PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer::class)
        ->call('configure', [[
            'elements' => ['method', 'property']
        ]]);
    // Line length
    $services->set(Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer::class)
        ->call('configure', [[
            'line_length' => 120,
            'break_long_lines' => true,
            'inline_short_lines' => true,
        ]]);
    // Import namespaces
    $services->set(SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff::class)
        ->property('allowFullyQualifiedGlobalClasses', true);
    // Looks for unused imports from other namespaces.
    $services->set(SlevomatCodingStandard\Sniffs\Namespaces\UnusedUsesSniff::class)
        ->property('searchAnnotations', true);
    // Remove useless doc block comments
    $services->set(SlevomatCodingStandard\Sniffs\Commenting\UselessFunctionDocCommentSniff::class);
    $services->set(Symplify\CodingStandard\Fixer\Commenting\RemoveSuperfluousDocBlockWhitespaceFixer::class);
    $services->set(PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer::class);
};

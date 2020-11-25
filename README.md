# Sunfox Coding Standard

This is set of sniff and fixers combined under [EasyCodingStandard](https://github.com/symplify/easy-coding-standard) that **checks and fixes** your PHP code.

## Versions

| Sunfox Coding Standard        | ^1.0   | ^2.0 |   dev-master |
|:------------------------------|-------:|-----:|-------------:|
| PHP                           | ^7.1   | ^7.2 | ^7.4 \|\| ^8.0 |
| symplify/easy-coding-standard | ^6.0.4 | ^7.2 |           ^8.3 |
| nette/coding-standard         | ^2.2   | ^2.3 |         ^3.0.1 |
| slevomat/coding-standard      | 5.0.4  | ^6.0 |             ^6 |

## Installation

```bash
composer require sunfoxcz/coding-standard:^2.0
```

## Example `ecs.yml`

```yaml
imports:
    - { resource: 'vendor/sunfoxcz/coding-standard/config/sunfox.yml' }

parameters:
    cache_directory: .ecs_cache
    indentation: spaces
    exclude_files:
        - 'projects/*/temp/cache/*'
        - 'projects/*/temp/proxies/*'
    exclude_checkers:
        - 'SlevomatCodingStandard\Sniffs\TypeHints\TypeHintDeclarationSniff'
        - 'Symplify\CodingStandard\Fixer\Commenting\RemoveUselessDocBlockFixer'
    skip:
        PHP_CodeSniffer\Standards\PSR1\Sniffs\Methods\CamelCapsMethodNameSniff.NotCamelCaps: ~
        SlevomatCodingStandard\Sniffs\Classes\UnusedPrivateElementsSniff.WriteOnlyProperty: ~
        SlevomatCodingStandard\Sniffs\TypeHints\TypeHintDeclarationSniff.MissingTraversableParameterTypeHintSpecification: ~
        SlevomatCodingStandard\Sniffs\Classes\UnusedPrivateElementsSniff.UnusedProperty:
            - 'app/Model/Entities/Pay.php'
```

# Sunfox Coding Standard

This is set of sniff and fixers combined under [EasyCodingStandard](https://github.com/symplify/easy-coding-standard) that **checks and fixes** your PHP code.

## Versions

| Sunfox Coding Standard | [^1.0](https://github.com/sunfoxcz/coding-standard/tree/1.0.0) | [^2.0](https://github.com/sunfoxcz/coding-standard/tree/2.0.0) | dev-master |
|:------------------------------|-------:|-----:|---------------:|
| PHP                           | ^7.1   | ^7.2 | ^7.4 \|\| ^8.0 |
| symplify/easy-coding-standard | ^6.0.4 | ^7.2 |           ^8.3 |
| nette/coding-standard         | ^2.2   | ^2.3 |         ^3.0.1 |
| slevomat/coding-standard      | 5.0.4  | ^6.0 |             ^6 |

## Installation

```bash
composer require sunfoxcz/coding-standard:dev-master
```

## Example `ecs.php`

```php
<?php declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;

return function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/vendor/sunfoxcz/coding-standard/config/sunfox.php');

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::FILE_EXTENSIONS, ['php', 'phpt']);
    $parameters->set(Option::CACHE_DIRECTORY, __DIR__ . '/.ecs_cache');
};

```

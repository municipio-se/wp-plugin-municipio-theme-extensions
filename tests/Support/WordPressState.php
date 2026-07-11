<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Support;

final class WordPressState
{
    /** @var array<string, mixed> */
    public static array $themeMods = [];

    /** @var array<string, mixed> */
    public static array $options = [];

    public static function reset(): void
    {
        self::$themeMods = [];
        self::$options = [];
    }
}

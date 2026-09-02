<?php

declare(strict_types=1);

namespace MunicipioThemeExtensions\Tests\Support;

final class WordPressState
{
    /** @var array<string, mixed> */
    public static array $themeMods = [];

    /** @var array<string, mixed> */
    public static array $options = [];

    /** @var array<string, array<string, mixed>> */
    public static array $enqueuedScripts = [];

    /** @var array<string, array<string, mixed>> */
    public static array $enqueuedStyles = [];

    /** @var array<string, mixed> */
    public static array $runtime = [];

    public static function reset(): void
    {
        self::$themeMods = [];
        self::$options = [];
        self::$enqueuedScripts = [];
        self::$enqueuedStyles = [];
        self::$runtime = [
            'singular' => false,
            'queriedObject' => null,
            'pageTemplate' => false,
            'postTypes' => [],
            'posts' => [],
            'lastGetPostsArguments' => [],
            'titles' => [],
            'permalinks' => [],
            'fields' => [],
            'acfFields' => [],
            'uniqueIdCounter' => 0,
        ];
    }
}

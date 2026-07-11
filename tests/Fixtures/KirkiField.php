<?php

declare(strict_types=1);

namespace Municipio\Customizer;

final class KirkiField
{
    /** @var array<int, array<string, mixed>> */
    public static array $fields = [];

    /**
     * @param array<string, mixed> $field
     */
    public static function addField(array $field): void
    {
        self::$fields[] = $field;
    }
}

<?php

declare(strict_types=1);

namespace ZaimeaLabs\Sluggable\Exceptions;

use Exception;

class InvalidOption extends Exception
{
    public static function missingFromField(): static
    {
        return new static('Could not determine which fields should be sluggified');
    }

    public static function missingSlugField(): static
    {
        return new static('Could not determine in which field the slug should be saved');
    }

    public static function invalidMaximumLength(): static
    {
        return new static('Maximum length should be greater than zero');
    }
}

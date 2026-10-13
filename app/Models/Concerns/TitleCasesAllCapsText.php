<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Support\TitleCase;
use Illuminate\Database\Eloquent\Model;

/**
 * Saves ALL-CAPS values of the model's $titleCaseAttributes in title case, whether
 * they come from a form, an import or the AI lookup.
 *
 * @property-read list<string> $titleCaseAttributes
 */
trait TitleCasesAllCapsText
{
    protected static function bootTitleCasesAllCapsText(): void
    {
        static::saving(function (Model $model): void {
            foreach ($model->titleCaseAttributes as $attribute) {
                $value = $model->getAttribute($attribute);

                if (is_string($value)) {
                    $model->setAttribute($attribute, TitleCase::fromAllCaps($value));
                }
            }
        });
    }
}

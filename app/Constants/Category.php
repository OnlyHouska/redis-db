<?php

namespace App\Constants;

enum Category: string
{
    case Mathematics = "Mathematics";
    case Programming = "Programming";
    case CzechLanguage = "Czech Language";
    case EnglishLanguage = "English Language";
    case Physics = "Physics";
    case Chemistry = "Chemistry";
    case Other = "Other";

    public static function values(): array
    {
        $values = [];

        foreach (Category::cases() as $case)
        {
            $values[] = $case->value;
        }

        return $values;
    }
}

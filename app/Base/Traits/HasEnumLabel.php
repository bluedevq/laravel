<?php

namespace App\Base\Traits;

trait HasEnumLabel
{
    abstract public function label(): string;

    public static function fromLabel(string $label)
    {
        return collect(self::cases())->first(function (self $enum) use ($label) {
            return $enum->label() === $label;
        });
    }

    public static function getLabelsList($nullable = false): array
    {
        $label = array_map(fn (self $enum) => $enum->label(), self::cases());

        if ($nullable) {
            $label[] = "";
        }

        return $label;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getValuesLabelsList(): array
    {
        $array = [];

        foreach (self::cases() as $case) {
            $array[$case->value] = $case->label();
        }

        return $array;
    }
}

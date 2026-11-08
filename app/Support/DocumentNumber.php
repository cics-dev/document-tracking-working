<?php

namespace App\Support;

use App\Models\DocumentType;
use App\Models\Office;

class DocumentNumber
{
    public static function format(?DocumentType $type, Office $office, string|int $number, string|int $year): string
    {
        return strtr(self::template($type), self::prefixes($type, $office) + [
            '{number}' => (string) $number,
            '{year}' => (string) $year,
        ]);
    }

    public static function parts(?DocumentType $type, Office $office): array
    {
        $parts = preg_split('/(\{number\}|\{year\})/', self::template($type), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $prefixes = self::prefixes($type, $office);

        return array_map(fn (string $part) => match ($part) {
            '{number}' => ['type' => 'number'],
            '{year}' => ['type' => 'year'],
            default => ['type' => 'literal', 'value' => strtr($part, $prefixes)],
        }, $parts);
    }

    public static function pattern(?DocumentType $type, Office $office): string
    {
        $parts = array_map(fn (array $part) => match ($part['type']) {
            'number' => '(?P<number>\d+)',
            'year' => '(?P<year>\d{4})',
            'literal' => preg_quote($part['value'], '~'),
        }, self::parts($type, $office));

        return '~^'.implode('', $parts).'$~u';
    }

    private static function template(?DocumentType $type): string
    {
        $template = $type?->number_prefix ?: '{office_with_type}-{type}';

        if (! str_contains($template, '{number}')) {
            $template .= '-{number}';
        }

        if (! str_contains($template, '{year}')) {
            $template .= '-{year}';
        }

        return $template;
    }

    private static function prefixes(?DocumentType $type, Office $office): array
    {
        return [
            '{office}' => $office->abbreviation,
            '{office_with_type}' => $office->abbreviation.($office->office_type ? '('.$office->office_type.')' : ''),
            '{type}' => (string) $type?->abbreviation,
        ];
    }
}

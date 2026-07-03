<?php

namespace OccTherapist\AdvancedTableExportForFilament\Support;

use Illuminate\Support\Str;

class XmlColumnTagResolver
{
    /** @var array<string, true> */
    protected array $usedTags = [];

    /**
     * @return array{element: string, attribute: string|null}
     */
    public function resolve(string $columnName, string $label): array
    {
        $tag = $this->sanitizeTag(Str::snake($columnName));

        if ($tag !== '' && ! isset($this->usedTags[$tag])) {
            $this->usedTags[$tag] = true;

            return [
                'element' => $tag,
                'attribute' => null,
            ];
        }

        $fallbackTag = $this->sanitizeTag(Str::snake($label));

        if ($tag === '' && $fallbackTag !== '' && ! isset($this->usedTags[$fallbackTag])) {
            $this->usedTags[$fallbackTag] = true;

            return [
                'element' => $fallbackTag,
                'attribute' => null,
            ];
        }

        return [
            'element' => 'field',
            'attribute' => $columnName !== '' ? $columnName : $label,
        ];
    }

    protected function sanitizeTag(string $value): string
    {
        $tag = preg_replace('/[^a-zA-Z0-9_-]/', '_', $value) ?? '';
        $tag = trim($tag, '_');

        if ($tag === '') {
            return '';
        }

        if (preg_match('/^[0-9]/', $tag)) {
            $tag = '_'.$tag;
        }

        return $tag;
    }
}

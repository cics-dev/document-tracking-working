<?php

namespace App\Support;

use App\Models\Document;
use App\Models\DocumentType;
use Carbon\Carbon;

final class IndorsementHeader
{
    public static function applies(array $data): bool
    {
        if (($data['printLayout'] ?? null) !== 'indorsement') {
            return false;
        }
        $typeId = $data['document']['document_type_id'] ?? null;

        return DocumentType::when($typeId, fn ($query) => $query->whereKey($typeId),
            fn ($query) => $query->where('name', $data['documentType'] ?? ''))
            ->whereRaw('LOWER(abbreviation) = ?', ['il'])->exists();
    }

    public static function data(array $data): array
    {
        $saved = isset($data['document']['id']) ? Document::find($data['document']['id']) : null;
        $source = ! empty($data['indorsementRevisionSourceId'])
            ? Document::find($data['indorsementRevisionSourceId']) : null;
        $root = ($source ?? $saved)?->revisionRoot();
        $date = $data['date_sent'] ?? now();
        $revision = 0;
        if ($source) {
            $revision = $root->revisions()->count() + 1;
        } elseif ($saved?->is_revision) {
            $revision = $root->revisions()->where('id', '<=', $saved->id)->count();
        }

        return [
            'ilEffectiveDate' => Carbon::parse($root?->date_sent ?? $date)->format('M d, Y'),
            'ilRevisionCode' => sprintf('%02d', $revision),
            'ilRevisionDate' => $revision > 0 ? Carbon::parse($date)->format('M d, Y') : 'N/A',
        ];
    }
}

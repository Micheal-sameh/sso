<?php

namespace App\Repositories\Eloquent;

use App\Models\SpiritualNoteLabel;
use App\Repositories\Contracts\SpiritualNoteLabelRepositoryContract;
use Illuminate\Support\Collection;

class SpiritualNoteLabelRepository implements SpiritualNoteLabelRepositoryContract
{
    public function all(): Collection
    {
        return SpiritualNoteLabel::orderBy('sort_order')->orderBy('name')->get();
    }

    public function active(): Collection
    {
        return SpiritualNoteLabel::active()->orderBy('sort_order')->orderBy('name')->get();
    }

    public function create(array $data): SpiritualNoteLabel
    {
        return SpiritualNoteLabel::create($data);
    }

    public function update(SpiritualNoteLabel $label, array $data): SpiritualNoteLabel
    {
        $label->update($data);

        return $label->fresh();
    }

    public function delete(SpiritualNoteLabel $label): bool
    {
        return (bool) $label->delete();
    }
}

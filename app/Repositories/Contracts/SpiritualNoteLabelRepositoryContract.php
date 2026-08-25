<?php

namespace App\Repositories\Contracts;

use App\Models\SpiritualNoteLabel;
use Illuminate\Support\Collection;

interface SpiritualNoteLabelRepositoryContract
{
    public function all(): Collection;

    public function active(): Collection;

    public function create(array $data): SpiritualNoteLabel;

    public function update(SpiritualNoteLabel $label, array $data): SpiritualNoteLabel;

    public function delete(SpiritualNoteLabel $label): bool;
}

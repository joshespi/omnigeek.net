<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Series;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

class AdminSeries extends Component
{
    public ?int $editingId = null;

    public string $editingName = '';

    public string $editingDescription = '';

    // Ids, not model binding: Series resolves route bindings by slug, and the buttons pass ids.
    public function edit(int $id): void
    {
        $this->authorize('admin');

        $series = Series::findOrFail($id);

        $this->editingId = $series->id;
        $this->editingName = $series->name;
        $this->editingDescription = $series->description ?? '';
    }

    public function update(): void
    {
        $this->authorize('admin');

        $series = Series::findOrFail($this->editingId);

        $this->validate([
            'editingName' => ['required', 'string', 'max:255'],
            'editingDescription' => ['nullable', 'string', 'max:5000'],
        ]);

        // The slug is derived from the name, so two names that slugify the same would
        // collide on the unique index. Catch it here with a readable message.
        $slug = Str::slug($this->editingName);

        if ($slug === '' || Series::where('slug', $slug)->whereKeyNot($series->id)->exists()) {
            $this->addError('editingName', 'Another series already uses that name.');

            return;
        }

        $series->update([
            'name' => trim($this->editingName),
            'description' => trim($this->editingDescription) ?: null,
        ]);

        ActivityLog::record('series.update', 'series', $series->id, $series->name);

        $this->reset('editingId', 'editingName', 'editingDescription');
    }

    public function delete(int $id): void
    {
        $this->authorize('admin');

        $series = Series::findOrFail($id);

        ActivityLog::record('series.delete', 'series', $series->id, $series->name);

        // posts.series_id is nullOnDelete: the posts stay, they just leave the series.
        $series->delete();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $this->authorize('admin');

        return view('livewire.admin-series', [
            'allSeries' => Series::withCount('posts')->orderBy('name')->get(),
        ]);
    }
}

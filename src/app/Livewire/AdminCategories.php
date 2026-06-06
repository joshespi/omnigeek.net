<?php

namespace App\Livewire;

use App\Models\Category;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

class AdminCategories extends Component
{
    public string $name = '';

    public ?int $editingId = null;

    public string $editingName = '';

    public function create(): void
    {
        $this->authorize('admin');

        $this->validate(['name' => ['required', 'string', 'max:255', 'unique:categories,name']]);

        Category::create(['name' => $this->name]);

        $this->reset('name');
    }

    public function edit(Category $category): void
    {
        $this->authorize('admin');

        $this->editingId = $category->id;
        $this->editingName = $category->name;
    }

    public function update(): void
    {
        $this->authorize('admin');

        $category = Category::findOrFail($this->editingId);

        $this->validate(['editingName' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category)]]);

        $category->update(['name' => $this->editingName]);

        $this->reset('editingId', 'editingName');
    }

    public function delete(Category $category): void
    {
        $this->authorize('admin');

        $category->delete();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $this->authorize('admin');

        return view('livewire.admin-categories', [
            'categories' => Category::withCount('posts')->orderBy('name')->get(),
        ]);
    }
}

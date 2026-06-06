<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Subscription;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\ConfirmSubscription;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Subscribe extends Component
{
    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('array')]
    public array $categories = [];

    #[Validate('array')]
    public array $geeks = [];

    #[Validate('nullable|string|max:255')]
    public string $tags = '';

    public bool $done = false;

    public function save(): void
    {
        $this->validate();

        $tagSlugs = Tag::fromText($this->tags)->pluck('slug')->all();

        $subscription = Subscription::updateOrCreate(
            ['email' => $this->email],
            [
                'filters' => array_filter([
                    'categories' => array_values($this->categories),
                    'geeks' => array_map('intval', $this->geeks),
                    'tags' => $tagSlugs,
                ]),
            ],
        );

        if (! $subscription->isConfirmed()) {
            Notification::route('mail', $subscription->email)
                ->notify(new ConfirmSubscription($subscription));
        }

        $this->done = true;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.subscribe', [
            'allCategories' => Category::orderBy('name')->get(),
            'allGeeks' => User::has('posts')->orderBy('name')->get(['id', 'name']),
        ]);
    }
}

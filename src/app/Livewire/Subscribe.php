<?php

namespace App\Livewire;

use App\Enums\DigestCadence;
use App\Enums\SubscriptionFrequency;
use App\Models\Category;
use App\Models\Subscription;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\ConfirmSubscription;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Subscribe extends Component
{
    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate(['categories' => 'array', 'categories.*' => 'string|exists:categories,slug'])]
    public array $categories = [];

    #[Validate(['geeks' => 'array', 'geeks.*' => 'integer|exists:users,id'])]
    public array $geeks = [];

    #[Validate('nullable|string|max:255')]
    public string $tags = '';

    public string $frequency = SubscriptionFrequency::Instant->value;

    public array $frequencyOptions = [];

    public bool $done = false;

    public function mount(): void
    {
        $this->frequencyOptions = [
            SubscriptionFrequency::Instant->value => SubscriptionFrequency::Instant->label(),
            SubscriptionFrequency::Digest->value => SubscriptionFrequency::Digest->label().' — '.DigestCadence::current()->label(),
        ];
    }

    protected function rules(): array
    {
        return ['frequency' => ['required', new Enum(SubscriptionFrequency::class)]];
    }

    public function save(): void
    {
        $this->validate();

        $tagSlugs = Tag::fromText($this->tags)->pluck('slug')->all();

        $attributes = [
            'frequency' => $this->frequency,
            'filters' => array_filter([
                'categories' => array_values($this->categories),
                'geeks' => array_map('intval', $this->geeks),
                'tags' => $tagSlugs,
            ]),
        ];

        // Anyone can submit any email here — if it already belongs to a confirmed
        // subscription, don't let the new preferences take effect until whoever
        // owns the inbox confirms them again.
        if (Subscription::where('email', $this->email)->whereNotNull('confirmed_at')->exists()) {
            $attributes['confirmed_at'] = null;
        }

        $subscription = Subscription::updateOrCreate(['email' => $this->email], $attributes);

        if (! $subscription->isConfirmed()) {
            Notification::route('mail', $subscription->email)
                ->notify(new ConfirmSubscription($subscription));
        }

        $this->done = true;
    }

    public function render()
    {
        return view('livewire.subscribe', [
            'allCategories' => Category::orderBy('name')->get(),
            'allGeeks' => User::has('posts')->orderBy('name')->get(['id', 'name']),
        ])->layout('layouts.app', [
            'ogTitle' => 'Subscribe',
            'ogDescription' => 'Get new '.config('app.name').' posts by email — instantly or as a digest.',
        ]);
    }
}

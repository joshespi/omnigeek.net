<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\RemovalRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RequestRemoval extends Component
{
    // Set only in mount(): a client-supplied id would skip the canView check.
    #[Locked]
    public ?int $postId = null;

    public string $name = '';

    public string $email = '';

    public string $kind = 'name_in_image';

    public string $details = '';

    // Honeypot: hidden from people, filled in by form-stuffing bots.
    public string $website = '';

    public bool $done = false;

    public function mount(): void
    {
        // Arriving from a post's "request a removal" link. Only a post the visitor could
        // already see is accepted, so the form can't be used to probe for scheduled posts.
        $post = Post::anyFeed()->find((int) request()->query('post'));

        if ($post && $post->canView(auth()->user())) {
            $this->postId = $post->id;
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'kind' => ['required', Rule::in(array_keys(RemovalRequest::KINDS))],
            'details' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    public function save(): void
    {
        // Pretend it worked: a bot that learns the honeypot failed just adapts.
        if ($this->website !== '') {
            $this->done = true;

            return;
        }

        $this->validate();

        $key = 'removal-request:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('details', 'Too many requests from this address. Please try again in an hour.');

            return;
        }

        RateLimiter::hit($key, 3600);

        RemovalRequest::create([
            'post_id' => $this->postId,
            'name' => trim($this->name) ?: null,
            'email' => trim($this->email) ?: null,
            'kind' => $this->kind,
            'details' => trim($this->details),
        ]);

        $this->done = true;
    }

    public function render()
    {
        return view('livewire.request-removal', [
            'post' => $this->postId ? Post::anyFeed()->find($this->postId) : null,
            'kinds' => RemovalRequest::KINDS,
        ])->layout('layouts.app', [
            'ogTitle' => 'Request a removal',
            'ogDescription' => 'Ask for your name, your words or an image of you to be removed or changed on '.config('app.name').'.',
            'robots' => 'noindex,follow',
        ]);
    }
}

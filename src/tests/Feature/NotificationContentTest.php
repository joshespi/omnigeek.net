<?php

namespace Tests\Feature;

use App\Enums\DigestCadence;
use App\Models\Post;
use App\Models\Subscription;
use App\Notifications\ConfirmSubscription;
use App\Notifications\DigestNotification;
use App\Notifications\NewPostPublished;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Tests\TestCase;

/**
 * Notifications are sent via Notification::route('mail', ...), so the notifiable
 * is an AnonymousNotifiable. These render toMail() directly to guard the email
 * body — subject, action URL, and the per-post/preview lines.
 */
class NotificationContentTest extends TestCase
{
    use RefreshDatabase;

    private AnonymousNotifiable $notifiable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notifiable = (new AnonymousNotifiable)->route('mail', 'reader@example.test');
    }

    public function test_confirm_subscription_mail_has_subject_and_confirm_action(): void
    {
        $sub = Subscription::factory()->create();

        $mail = (new ConfirmSubscription($sub))->toMail($this->notifiable);

        $this->assertStringContainsString('Confirm your', $mail->subject);
        $this->assertSame(route('subscriptions.confirm', $sub->token), $mail->actionUrl);
    }

    public function test_new_post_mail_previews_the_post_and_links_to_it(): void
    {
        $sub = Subscription::factory()->create();
        $post = Post::factory()->create(['title' => 'Recapped a motherboard', 'body' => null]);

        $mail = (new NewPostPublished($post, $sub))->toMail($this->notifiable);

        $this->assertStringContainsString('New on', $mail->subject);
        $this->assertSame(route('posts.show', $post), $mail->actionUrl);
        $this->assertContains('Recapped a motherboard', $mail->introLines);
    }

    public function test_digest_mail_lists_every_post(): void
    {
        $sub = Subscription::factory()->create();
        $posts = Post::factory()->count(2)->create();

        $mail = (new DigestNotification($posts, $sub, DigestCadence::Weekly))
            ->toMail($this->notifiable);

        $this->assertStringContainsString('digest', $mail->subject);
        // One header line ("2 new posts:") plus one line per post.
        $body = collect($mail->introLines)->implode("\n");
        foreach ($posts as $post) {
            $this->assertStringContainsString($post->user->name, $body);
        }
    }
}

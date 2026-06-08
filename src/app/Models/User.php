<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\ImageProcessor;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'password', 'email_verified_at', 'avatar_path', 'bio', 'is_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        // Delete posts in PHP (not DB FK cascade) so Post/PostMedia deleting events
        // fire and remove their stored files; also drop this user's avatar.
        static::deleting(function (User $user) {
            $user->posts->each->delete();

            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::get(fn () => $this->avatar_path
            ? Storage::disk('public')->url($this->avatar_path)
            : null);
    }

    // Replace the avatar: drop the old file, store a compressed copy, persist the path.
    public function setAvatar(UploadedFile $file): void
    {
        $this->clearAvatar();
        $this->forceFill(['avatar_path' => ImageProcessor::compress($file, 'avatars', 512)])->save();
    }

    public function clearAvatar(): void
    {
        if ($this->avatar_path) {
            Storage::disk('public')->delete($this->avatar_path);
            $this->forceFill(['avatar_path' => null])->save();
        }
    }

    public function initials(): string
    {
        return collect(explode(' ', $this->name))
            ->map(fn ($part) => mb_substr($part, 0, 1))
            ->take(2)
            ->implode('');
    }
}

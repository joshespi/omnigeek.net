<?php

namespace App\Livewire;

use App\Enums\DigestCadence;
use App\Support\SiteMedia;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class AdminMedia extends Component
{
    use WithFileUploads;

    #[Validate('nullable|image|max:2048')]
    public $logo = null;

    #[Validate('nullable|image|max:5120|dimensions:min_width=600,min_height=315')]
    public $ogImage = null;

    public string $digestCadence = '';

    protected function rules(): array
    {
        return ['digestCadence' => ['required', new Enum(DigestCadence::class)]];
    }

    public function mount(): void
    {
        $this->digestCadence = DigestCadence::current()->value;
    }

    public function saveDigestCadence(): void
    {
        $this->authorize('admin');
        $this->validateOnly('digestCadence');
        DigestCadence::from($this->digestCadence)->store();
        $this->dispatch('cadence-saved');
    }

    public function saveLogo(): void
    {
        $this->authorize('admin');
        $this->validateOnly('logo');

        if (! $this->logo) {
            return;
        }

        SiteMedia::store(SiteMedia::LOGO, $this->logo);
        $this->reset('logo');
        $this->dispatch('logo-saved');
    }

    public function saveOgImage(): void
    {
        $this->authorize('admin');
        $this->validateOnly('ogImage');

        if (! $this->ogImage) {
            return;
        }

        SiteMedia::store(SiteMedia::OG_DEFAULT, $this->ogImage);
        $this->reset('ogImage');
        $this->dispatch('og-saved');
    }

    public function deleteLogo(): void
    {
        $this->authorize('admin');
        SiteMedia::delete(SiteMedia::LOGO);
    }

    public function deleteOgImage(): void
    {
        $this->authorize('admin');
        SiteMedia::delete(SiteMedia::OG_DEFAULT);
    }

    public function render()
    {
        return view('livewire.admin-media', [
            'logoUrl' => SiteMedia::logoUrl(),
            'ogImageUrl' => SiteMedia::ogDefaultUrl(),
        ]);
    }
}

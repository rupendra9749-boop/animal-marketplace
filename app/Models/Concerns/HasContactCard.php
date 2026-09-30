<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/** Shared by doctors and caretakers: a public profile with a photo, a list of services and phone / WhatsApp. */
trait HasContactCard
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? asset('storage/'.$this->photo_path) : null;
    }

    /** @return list<string> */
    public function serviceList(): array
    {
        return collect(explode(',', (string) $this->services))->map(fn ($s) => trim($s))->filter()->values()->all();
    }

    /** Digits only, ready for a wa.me link (a bare 10-digit Indian number gets the 91 prefix). */
    public function whatsappNumber(): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) ($this->whatsapp ?: ''));
        if ($digits === '') {
            return null;
        }

        return strlen($digits) === 10 ? '91'.$digits : $digits;
    }
}

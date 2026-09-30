<?php

namespace App\Services;

use Illuminate\Support\Collection;

/** What an AI doctor search did: how it ended, the new doctors it saved, and a message for the person. */
class AiSearchResult
{
    public const OK = 'ok';            // new doctors were saved
    public const EMPTY = 'empty';      // the search ran but found nobody new
    public const RECENT = 'recent';    // this area was searched recently, so nothing was run
    public const LIMITED = 'limited';  // a daily limit was reached
    public const ERROR = 'error';      // the AI service failed
    public const DISABLED = 'disabled';

    /**
     * @param  Collection<int, \App\Models\Vet>  $created
     */
    public function __construct(
        public readonly string $status,
        public readonly string $message,
        public readonly Collection $created,
    ) {}

    public static function of(string $status, string $message, ?Collection $created = null): self
    {
        return new self($status, $message, $created ?? collect());
    }

    public function found(): bool
    {
        return $this->created->isNotEmpty();
    }
}

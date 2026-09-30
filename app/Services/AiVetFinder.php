<?php

namespace App\Services;

use App\Models\AiSearch;
use App\Models\Category;
use App\Models\User;
use App\Models\Vet;
use App\Support\Locations;
use App\Support\Phone;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Finds animal doctors that are not in our database yet: an AI model with web search looks for real clinics
 * near a city, and each clinic it names is saved (marked as AI-found and unverified).
 *
 * Nothing is invented on our side: a clinic is only saved when it has a valid Indian phone number and the web
 * page it was found on is one the search actually returned.
 */
class AiVetFinder
{
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';

    public function isConfigured(): bool
    {
        return filled(config('services.anthropic.key'));
    }

    /**
     * Runs an AI search around a city. Admins can pass $force to skip the daily limits and the "searched
     * recently" shortcut.
     */
    public function search(?User $user, string $state, string $city, ?Category $type = null, bool $force = false): AiSearchResult
    {
        if (! $this->isConfigured()) {
            return AiSearchResult::of(AiSearchResult::DISABLED, __('AI search is not switched on yet.'));
        }

        $place = Locations::find($state, $city);
        if (! $place) {
            return AiSearchResult::of(AiSearchResult::ERROR, __('Please choose a city from the list first.'));
        }

        if (! $force && ($blocked = $this->limitReached($user, $place, $type))) {
            return $blocked;
        }

        try {
            $reply = $this->ask($place, $type);
        } catch (ConnectionException|RequestException $e) {
            Log::warning('AI doctor search failed', ['city' => $place['city'], 'error' => $e->getMessage()]);

            return $this->finish($user, $place, $type, AiSearchResult::ERROR, __('The AI search is not available right now. Please try again in a little while.'), collect(), $e->getMessage());
        }

        $items = $this->extractItems($reply['text']);
        if ($items === null) {
            Log::warning('AI doctor search returned unreadable output', ['city' => $place['city']]);

            return $this->finish($user, $place, $type, AiSearchResult::ERROR, __('The AI search did not give a usable answer. Please try again.'), collect(), 'unreadable answer');
        }

        $created = $this->import($items, $reply['urls'], $place, $type);

        return $created->isEmpty()
            ? $this->finish($user, $place, $type, AiSearchResult::EMPTY, __('The AI web search found no new doctors near :city.', ['city' => $place['city']]), $created)
            : $this->finish($user, $place, $type, AiSearchResult::OK, trans_choice('The AI web search found :count new doctor near :city. They are marked as unverified - please call to confirm before you go.|The AI web search found :count new doctors near :city. They are marked as unverified - please call to confirm before you go.', $created->count(), ['count' => $created->count(), 'city' => $place['city']]), $created);
    }

    /**
     * Saves the clinics an AI answer named. Public so the import rules can be tested without calling the AI.
     *
     * @param  list<array<string, mixed>>  $items  clinics parsed from the AI answer
     * @param  list<string>  $allowedUrls  pages the web search really returned
     * @param  array{state: string, city: string, lat: float, lng: float}  $place
     * @return Collection<int, Vet>
     */
    public function import(array $items, array $allowedUrls, array $place, ?Category $type = null): Collection
    {
        $allowed = collect($allowedUrls)->map(fn ($url) => $this->normaliseUrl($url))->filter()->unique()->flip();
        $categories = Category::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [mb_strtolower($name) => $id]);
        $created = collect();

        // Phone numbers are stored formatted, so compare the last 10 digits; the same name in the same city counts as a duplicate too.
        $tails = Vet::pluck('phone')->map(fn ($p) => substr(preg_replace('/\D+/', '', (string) $p), -10))->filter()->flip();
        $keys = Vet::get(['name', 'city'])->map(fn ($v) => mb_strtolower($v->name).'|'.mb_strtolower($v->city))->flip();

        foreach (array_slice($items, 0, (int) config('services.anthropic.max_results', 5) * 2) as $item) {
            if (! is_array($item) || $created->count() >= (int) config('services.anthropic.max_results', 5)) {
                continue;
            }

            $name = trim((string) ($item['name'] ?? '')) ?: trim((string) ($item['clinic_name'] ?? ''));
            $phone = Phone::contact((string) ($item['phone'] ?? ''));
            $sourceUrl = trim((string) ($item['source_url'] ?? ''));

            // Only real, traceable clinics: a name, a phone number, and a page the search actually returned.
            if ($name === '' || $phone === null || ! filter_var($sourceUrl, FILTER_VALIDATE_URL) || ! $allowed->has($this->normaliseUrl($sourceUrl))) {
                continue;
            }

            $tail = substr(preg_replace('/\D+/', '', $phone), -10);
            $key = mb_strtolower($name).'|'.mb_strtolower($place['city']);
            if ($tails->has($tail) || $keys->has($key)) {
                continue;
            }
            $tails->put($tail, true);
            $keys->put($key, true);

            $vet = Vet::create([
                'name' => Str::limit($name, 250, ''),
                'slug' => $this->uniqueSlug($name.' '.$place['city']),
                'clinic_name' => $this->text($item['clinic_name'] ?? null, 250),
                'services' => $this->text($item['services'] ?? null, 250),
                'timings' => $this->text($item['timings'] ?? null, 120),
                'address' => $this->text($item['address'] ?? null, 250),
                'phone' => $phone,
                'state' => $place['state'],
                'city' => $place['city'],
                'latitude' => $place['lat'],
                'longitude' => $place['lng'],
                'is_active' => true,
                'source' => 'ai',
                'source_url' => Str::limit($sourceUrl, 490, ''),
                'is_verified' => false,
            ]);

            $ids = collect($item['animals'] ?? [])
                ->map(fn ($animal) => $categories->get(mb_strtolower(trim((string) $animal))))
                ->filter()
                ->unique()
                ->values();
            if ($ids->isEmpty() && $type) {
                $ids = collect([$type->id]);
            }
            $vet->categories()->sync($ids->all());

            $created->push($vet);
        }

        return $created;
    }

    /**
     * Pulls the JSON list out of the model's final text. Returns null when there is no readable list.
     *
     * @return list<array<string, mixed>>|null
     */
    public function extractItems(string $text): ?array
    {
        $start = strpos($text, '[');
        $end = strrpos($text, ']');
        if ($start === false || $end === false || $end < $start) {
            return null;
        }

        $decoded = json_decode(substr($text, $start, $end - $start + 1), true);

        return is_array($decoded) ? array_values(array_filter($decoded, 'is_array')) : null;
    }

    /**
     * Calls the AI with web search on. Returns the final text and every page URL the search returned.
     *
     * @return array{text: string, urls: list<string>}
     */
    private function ask(array $place, ?Category $type): array
    {
        $max = (int) config('services.anthropic.max_results', 5);
        $animals = Category::orderBy('name')->pluck('name')->join(', ');

        $system = <<<PROMPT
You help animal owners in India find REAL veterinary clinics and animal doctors, using web search.

Rules:
- Only report clinics you actually saw on a web page during THIS search. Never invent or guess a name, phone number or address. If a detail is not on the page, use null.
- Every clinic needs an Indian phone number exactly as shown on the page, and the exact URL of the page where you saw it.
- Only clinics located in or within about 30 km of the requested city.
- Your final answer must be ONLY a JSON array - no explanation, no markdown fences. If you found nothing reliable, answer [].

Each array item:
{"name": "doctor or clinic name", "clinic_name": "clinic name or null", "phone": "as on the page", "address": "or null", "services": "comma separated or null", "animals": ["types treated, chosen from: {$animals}"], "timings": "or null", "source_url": "https://exact-page-you-saw-it-on"}
PROMPT;

        $wanted = $type ? " that treat {$type->name}" : ' (pets and farm animals)';
        $messages = [[
            'role' => 'user',
            'content' => "Find up to {$max} veterinary clinics or animal doctors near {$place['city']}, {$place['state']}, India{$wanted}. Return the JSON array now.",
        ]];

        $body = [
            'model' => config('services.anthropic.model'),
            'max_tokens' => 4096,
            'system' => $system,
            'tools' => [[
                'type' => 'web_search_20250305',
                'name' => 'web_search',
                'max_uses' => 4,
                'user_location' => ['type' => 'approximate', 'city' => $place['city'], 'region' => $place['state'], 'country' => 'IN', 'timezone' => 'Asia/Kolkata'],
            ]],
        ];

        $urls = [];
        $text = '';

        // The whole search must finish inside the time Cloudflare allows a page to load (about 100 seconds).
        $deadline = microtime(true) + (int) config('services.anthropic.budget', 85);

        // A long search can be paused by the API; keep going a few times until it gives its final answer.
        for ($turn = 0; $turn < 4; $turn++) {
            $remaining = (int) floor($deadline - microtime(true));
            if ($remaining < 8) {
                throw new ConnectionException('The AI search ran out of time.');
            }

            $response = Http::withHeaders([
                'x-api-key' => config('services.anthropic.key'),
                'anthropic-version' => config('services.anthropic.version'),
            ])->timeout(min((int) config('services.anthropic.timeout'), $remaining))->acceptJson()->asJson()->post(self::ENDPOINT, [...$body, 'messages' => $messages])->throw()->json();

            $content = $response['content'] ?? [];
            $turnText = '';

            foreach ($content as $block) {
                $kind = $block['type'] ?? '';

                if ($kind === 'text') {
                    $turnText .= $block['text'] ?? '';
                    foreach ($block['citations'] ?? [] as $citation) {
                        if (! empty($citation['url'])) {
                            $urls[] = $citation['url'];
                        }
                    }
                } elseif ($kind === 'web_search_tool_result' && is_array($block['content'] ?? null)) {
                    foreach ($block['content'] as $result) {
                        if (! empty($result['url'])) {
                            $urls[] = $result['url'];
                        }
                    }
                }
            }

            $text = $turnText !== '' ? $turnText : $text;

            if (($response['stop_reason'] ?? '') !== 'pause_turn') {
                break;
            }

            $messages[] = ['role' => 'assistant', 'content' => $content];
        }

        return ['text' => $text, 'urls' => array_values(array_unique($urls))];
    }

    /** A daily limit or a recent search of the same area stops the AI call. */
    private function limitReached(?User $user, array $place, ?Category $type): ?AiSearchResult
    {
        $recentDays = (int) config('services.anthropic.repeat_after_days', 7);

        $recent = AiSearch::where('kind', 'vet')->where('state', $place['state'])->where('city', $place['city'])
            ->where('category_id', $type?->id)->whereIn('status', ['ok', 'empty'])
            ->where('created_at', '>=', now()->subDays($recentDays))->exists();

        if ($recent) {
            return AiSearchResult::of(AiSearchResult::RECENT, __('This area was already searched with AI recently, so nothing new was added. Try again in a few days.'));
        }

        $today = AiSearch::where('kind', 'vet')->where('created_at', '>=', now()->startOfDay());

        if ((clone $today)->count() >= (int) config('services.anthropic.global_per_day', 60)) {
            return AiSearchResult::of(AiSearchResult::LIMITED, __('AI search has been used a lot today. Please try again tomorrow.'));
        }

        if ($user && (clone $today)->where('user_id', $user->id)->count() >= (int) config('services.anthropic.per_user_per_day', 5)) {
            return AiSearchResult::of(AiSearchResult::LIMITED, __('You have used your AI searches for today. Please try again tomorrow.'));
        }

        return null;
    }

    private function finish(?User $user, array $place, ?Category $type, string $status, string $message, Collection $created, ?string $detail = null): AiSearchResult
    {
        AiSearch::create([
            'user_id' => $user?->id,
            'kind' => 'vet',
            'state' => $place['state'],
            'city' => $place['city'],
            'category_id' => $type?->id,
            'found_count' => $created->count(),
            'status' => $status === AiSearchResult::OK || $status === AiSearchResult::EMPTY ? $status : 'error',
            'message' => $detail ? Str::limit($detail, 1000) : null,
        ]);

        return AiSearchResult::of($status, $message, $created);
    }

    private function normaliseUrl(string $url): ?string
    {
        $parts = parse_url(trim($url));
        if (! $parts || empty($parts['host'])) {
            return null;
        }

        return mb_strtolower(preg_replace('/^www\./', '', $parts['host'])).rtrim($parts['path'] ?? '', '/').(isset($parts['query']) ? '?'.$parts['query'] : '');
    }

    private function text(mixed $value, int $max): ?string
    {
        $value = trim((string) $value);

        return $value === '' || mb_strtolower($value) === 'null' ? null : Str::limit($value, $max, '');
    }

    private function uniqueSlug(string $base): string
    {
        $base = Str::slug($base) ?: 'vet';
        $slug = $base;

        for ($i = 2; Vet::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}

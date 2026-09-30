<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One run of the AI doctor search, kept so searches can be rate limited and reviewed by an admin. */
class AiSearch extends Model
{
    protected $fillable = ['user_id', 'kind', 'state', 'city', 'category_id', 'found_count', 'status', 'message'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

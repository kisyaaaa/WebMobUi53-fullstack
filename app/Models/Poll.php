<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Poll extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'question',
        'secret_token',
        'is_draft',
        'allow_multiple_choices',
        'allow_vote_change',
        'results_public',
        'duration',
        'started_at',
        'ends_at',
    ];

    protected $appends = ['status', 'share_url'];

    protected function casts(): array
    {
        return [
            'is_draft' => 'boolean',
            'allow_multiple_choices' => 'boolean',
            'allow_vote_change' => 'boolean',
            'results_public' => 'boolean',
            'duration' => 'integer',
            'started_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function isDraft(): bool
    {
        return $this->is_draft;
    }

    public function isEnded(): bool
    {
        return $this->ends_at !== null && $this->ends_at->isPast();
    }

    public function isActive(): bool
    {
        return !$this->is_draft && !$this->isEnded();
    }

    protected function status(): Attribute
    {
        return Attribute::get(function () {
            if ($this->is_draft) return 'draft';
            if ($this->isEnded()) return 'ended';
            return 'active';
        });
    }

    protected function shareUrl(): Attribute
    {
        return Attribute::get(fn () => url('/polls/' . $this->secret_token));
    }

    public static function generateToken(): string
    {
        return Str::random(32);
    }
}
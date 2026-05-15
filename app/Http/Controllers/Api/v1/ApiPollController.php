<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApiPollController extends Controller
{
    /**
     * Lister les sondages de l'utilisateur authentifié (dashboard).
     */
    public function index(Request $request)
    {
        return $request->user()
            ->polls()
            ->withCount('options')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Afficher un sondage en mode gestion (owner only via policy).
     */
    public function show(Poll $poll)
    {
        Gate::authorize('view', $poll);

        return $poll->load('options');
    }

    /**
     * Afficher un sondage par son secret_token (vue publique pour la page de vote).
     */
    public function showByToken(string $token)
    {
        $poll = Poll::with(['options' => fn ($q) => $q->withCount('votes')])
            ->where('secret_token', $token)
            ->first();

        if (! $poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        return $poll;
    }

    /**
     * Créer un sondage (toujours en brouillon, sans options).
     * Les options s'ajoutent via ApiPollOptionController (étape 1.5).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'question' => 'required|string|max:500',
            'allow_multiple_choices' => 'boolean',
            'allow_vote_change' => 'boolean',
            'results_public' => 'boolean',
            'duration' => 'nullable|integer|min:60|max:31536000',
        ]);

        $poll = $request->user()->polls()->create([
            ...$validated,
            'secret_token' => Poll::generateToken(),
            'is_draft' => true,
        ]);

        return $poll->load('options');
    }

    /**
     * Mettre à jour un sondage existant.
     */
    public function update(Request $request, Poll $poll)
    {
        Gate::authorize('update', $poll);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'question' => 'required|string|max:500',
            'allow_multiple_choices' => 'boolean',
            'allow_vote_change' => 'boolean',
            'results_public' => 'boolean',
            'duration' => 'nullable|integer|min:60|max:31536000',
        ]);

        $poll->update($validated);

        // Si le sondage est déjà lancé et que la durée a changé → recalcule ends_at
        if (! $poll->is_draft && $poll->started_at && array_key_exists('duration', $validated)) {
            $poll->ends_at = $validated['duration'] === null
                ? null
                : $poll->started_at->copy()->addSeconds($validated['duration']);
            $poll->save();
        }

        return $poll->load('options');
    }

    /**
     * Lancer un sondage : passage de brouillon à actif.
     */
    public function start(Poll $poll)
    {
        Gate::authorize('update', $poll);

        if (! $poll->is_draft) {
            return response()->json(['message' => 'Poll is already started.'], 422);
        }

        if ($poll->options()->count() < 2) {
            return response()->json(['message' => 'Poll must have at least 2 options.'], 422);
        }

        $now = now();
        $poll->update([
            'is_draft' => false,
            'started_at' => $now,
            'ends_at' => $poll->duration ? $now->copy()->addSeconds($poll->duration) : null,
        ]);

        return $poll->load('options');
    }

    /**
     * Supprimer un sondage.
     */
        public function destroy(Poll $poll)
    {
        Gate::authorize('delete', $poll);

        $poll->delete();

        return response()->json(['message' => 'Poll deleted.']);
    }
}
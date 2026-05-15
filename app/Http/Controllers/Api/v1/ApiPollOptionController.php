<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApiPollOptionController extends Controller
{
    /**
     * Ajouter une option à un sondage (brouillon uniquement).
     */
    public function store(Request $request, Poll $poll)
    {
        Gate::authorize('update', $poll);

        if (! $poll->is_draft) {
            return response()->json([
                'message' => 'Cannot modify options of a started poll.',
            ], 422);
        }

        $validated = $request->validate([
            'label' => 'required|string|max:255',
        ]);

        return $poll->options()->create($validated);
    }

    /**
     * Modifier le libellé d'une option (brouillon uniquement).
     */
    public function update(Request $request, Poll $poll, PollOption $option)
    {
        Gate::authorize('update', $poll);

        if ($option->poll_id !== $poll->id) {
            return response()->json(['message' => 'Option does not belong to this poll.'], 404);
        }

        if (! $poll->is_draft) {
            return response()->json([
                'message' => 'Cannot modify options of a started poll.',
            ], 422);
        }

        $validated = $request->validate([
            'label' => 'required|string|max:255',
        ]);

        $option->update($validated);

        return $option;
    }

    /**
     * Supprimer une option (brouillon uniquement).
     */
    public function destroy(Poll $poll, PollOption $option)
    {
        Gate::authorize('update', $poll);

        if ($option->poll_id !== $poll->id) {
            return response()->json(['message' => 'Option does not belong to this poll.'], 404);
        }

        if (! $poll->is_draft) {
            return response()->json([
                'message' => 'Cannot delete options of a started poll.',
            ], 422);
        }

        $option->delete();

        return response()->json(['message' => 'Option deleted.']);
    }
}
<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApiPollVoteController extends Controller
{
    /**
     * Soumettre un ou plusieurs votes pour un sondage (via son secret_token).
     */
    public function store(Request $request, string $token)
    {
        $poll = Poll::where('secret_token', $token)->first();

        if (! $poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        Gate::authorize('vote', $poll);

        $rules = $poll->allow_multiple_choices
            ? ['option_ids' => 'required|array|min:1', 'option_ids.*' => 'integer']
            : ['option_ids' => 'required|array|size:1', 'option_ids.*' => 'integer'];

        $validated = $request->validate($rules);

        // Anti-IDOR : les IDs reçus doivent tous appartenir à ce sondage
        $validIds = $poll->options()
            ->whereIn('id', $validated['option_ids'])
            ->pluck('id');

        if ($validIds->count() !== count($validated['option_ids'])) {
            return response()->json([
                'message' => 'Some options do not belong to this poll.',
            ], 422);
        }

        $user = $request->user();
        $hasAlreadyVoted = $poll->votes()->where('user_id', $user->id)->exists();

        if ($hasAlreadyVoted) {
            if (! $poll->allow_vote_change) {
                return response()->json([
                    'message' => 'You have already voted and vote change is not allowed.',
                ], 422);
            }
            $poll->votes()->where('user_id', $user->id)->delete();
        }

        $votes = [];
        foreach ($validIds as $optionId) {
            $votes[] = PollVote::create([
                'poll_id' => $poll->id,
                'user_id' => $user->id,
                'poll_option_id' => $optionId,
            ]);
        }

        return response()->json([
            'message' => 'Vote submitted.',
            'votes' => $votes,
        ]);
    }

    /**
     * Renvoyer les options votées par l'utilisateur authentifié.
     */
    public function myVote(Request $request, string $token)
    {
        $poll = Poll::where('secret_token', $token)->first();

        if (! $poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        $optionIds = $poll->votes()
            ->where('user_id', $request->user()->id)
            ->pluck('poll_option_id');

        return response()->json(['option_ids' => $optionIds]);
    }

    /**
     * Résultats agrégés du sondage. Accessible aux anonymes si results_public.
     */
    public function results(Request $request, string $token)
    {
        $poll = Poll::where('secret_token', $token)->first();

        if (! $poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        Gate::authorize('viewResults', $poll);

        $options = $poll->options()->withCount('votes')->get();
        $totalVotes = $poll->votes()->count();

        return response()->json([
            'poll_id' => $poll->id,
            'total_votes' => $totalVotes,
            'options' => $options->map(fn ($o) => [
                'id' => $o->id,
                'label' => $o->label,
                'votes_count' => $o->votes_count,
            ]),
        ]);
    }
}
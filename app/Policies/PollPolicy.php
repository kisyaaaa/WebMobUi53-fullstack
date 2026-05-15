<?php

namespace App\Policies;

use App\Models\Poll;
use App\Models\User;

class PollPolicy
{
    /**
     * Lister "ses" sondages — toujours autorisé pour un user connecté.
     * Le filtrage par user_id est fait dans le contrôleur.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Voir un sondage dans le contexte de gestion (dashboard).
     * Owner uniquement. La consultation publique via token passe par un autre flux.
     */
    public function view(?User $user, Poll $poll): bool
    {
        return $user !== null && $user->id === $poll->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Poll $poll): bool
    {
        return $user->id === $poll->user_id;
    }

    public function delete(User $user, Poll $poll): bool
    {
        return $user->id === $poll->user_id;
    }

    /**
     * Voir les résultats agrégés.
     * Owner toujours, anonyme/autre user seulement si results_public.
     */
    public function viewResults(?User $user, Poll $poll): bool
    {
        if ($poll->results_public) {
            return true;
        }

        return $user !== null && $user->id === $poll->user_id;
    }

    /**
     * Voter sur un sondage.
     * Authentification requise + sondage actif (ni brouillon, ni terminé).
     */
    public function vote(User $user, Poll $poll): bool
    {
        return ! $poll->is_draft && ! $poll->isEnded();
    }
}
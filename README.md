# Application de sondage — Laravel 12 + Vue 3

Mini-projet réalisé dans le cadre du cours [Développement de produit média (DévProdMéd)](https://github.com/heig-vd-devprodmed-course/heig-vd-devprodmed-course) à la HEIG-VD.

Application fullstack permettant de créer, configurer et partager des sondages avec une page de vote dédiée, des résultats en temps réel et un graphique d'aperçu.

## Fonctionnalités

- Dashboard listant les sondages de l'utilisateur connecté
- Création, édition, suppression, lancement et partage de sondages
- Gestion d'options (ajout, modification, suppression — bloquée après lancement)
- Paramètres configurables : choix multiple, modification du vote, résultats publics, durée
- Lien de partage unique par sondage (token)
- Page de vote accessible via ce lien (auth requise pour voter)
- Accès anonyme aux résultats si `results_public` est activé
- Affichage en temps réel des résultats via polling toutes les 5s
- Aperçu graphique des résultats (barres horizontales)
- Affichage clair quand le sondage est terminé (date de fin)
- Interface responsive (mobile first)

## Stack technique

- **Backend** : Laravel 12 (API JSON + auth Sanctum SPA)
- **Frontend** : Vue 3.5 + Vite 7 + Tailwind 4
- **Base de données** : SQLite (par défaut)
- **Authentification** : système existant fourni (sessions Laravel + Sanctum SPA), non modifié

> Voir aussi `README_FRONT.md` (fourni avec le squelette) pour les détails de l'intégration Sanctum SPA + Vue (gestion du CSRF, composable `useFetchApi`, layouts).

## Installation

```bash
# 1. Installer les dépendances
npm install && npm run build
composer install

# 2. Configurer l'environnement
cp .env.example .env
php artisan key:generate

# 3. Créer le lien symbolique pour les fichiers téléversés
php artisan storage:link

# 4. Migrer et seeder la base de données
php artisan migrate --seed

# 5. Lancer les serveurs (Laravel + Vite + queue)
composer run dev
```

L'application est accessible sur <http://127.0.0.1:8000>.

**Comptes de test** (créés par le seeder) :
- `john.doe@example.com` / `password`
- `jane.doe@example.com` / `password`

## Comment tester

- **Dashboard** : <http://127.0.0.1:8000/polls/dashboard> (connexion requise, accessible via le bouton "Sondages" dans la nav bar)
- **Page de vote** : copie le lien de partage d'un sondage "En cours" depuis le dashboard

5 sondages sont seedés pour couvrir tous les états :

| # | Titre | Statut | Choix | Résultats publics |
| --- | --- | --- | --- | --- |
| 1 | Tech préférences | En cours | Unique | ✓ |
| 2 | Frameworks frontend | En cours | Multiple | ✓ |
| 3 | Télétravail | Brouillon | Unique | ✗ |
| 4 | OS de dev | Terminé | Unique | ✓ |
| 5 | Soirée idéale | Brouillon | Multiple | ✗ |

## Choix techniques

- **Store maison via composable** (`usePollStore`) plutôt que Pinia, pour rester dans les libs vues en cours et garder une approche minimaliste.
- **Pas de Vue Router** : le dashboard utilise deux `ref()` (`view`, `editingPollId`) pour basculer entre liste et éditeur. Suffisant pour 2 vues, plus simple à défendre.
- **Graphique en `<div>` Tailwind** (pas de Chart.js) : barres horizontales avec largeur en `%` calculée par computed. Pas de dépendance externe.
- **Routes API séparées gestion / vue publique** : `/polls/{id}` pour la gestion (auth requise + policy owner), `/polls/by-token/{token}` pour la consultation publique. Préfixe `by-token` pour éviter toute collision de routing.
- **Unicité du vote pour choix unique** : double défense — contrainte unique en BDD sur `(poll_id, user_id, poll_option_id)` + vérification API qui refuse un second vote (sauf si `allow_vote_change`).
- **Policy Laravel pour les autorisations** : auto-discovery, centralise `view`, `update`, `delete`, `vote`, `viewResults`. Réutilisable côté API.
- **Polling 5s** via composable fourni `usePolling`, cleanup automatique au démontage du composant.

## Structure côté frontend

```
resources/js/
├── poll-dashboard.js          ← entrypoint Vite du dashboard
├── poll-vote.js               ← entrypoint Vite de la page de vote
├── AppPollDashboard.vue       ← racine du dashboard (toggle list/editor)
├── AppPollVote.vue            ← racine de la page de vote
├── components/
│   ├── PollList.vue
│   ├── PollEditor.vue
│   ├── PollVoteForm.vue
│   └── PollResultsChart.vue
├── stores/
│   └── usePollStore.js        ← store unique du dashboard
└── composables/               ← fournis (useFetchApi, usePolling, ...)
```

## Endpoints API v1

| Méthode | URL | Auth | But |
| --- | --- | --- | --- |
| GET | `/api/v1/polls` | ✅ | Liste des sondages de l'utilisateur |
| POST | `/api/v1/polls` | ✅ | Créer un sondage (brouillon) |
| GET | `/api/v1/polls/{id}` | ✅ owner | Détails d'un sondage |
| PUT | `/api/v1/polls/{id}` | ✅ owner | Mettre à jour un sondage |
| DELETE | `/api/v1/polls/{id}` | ✅ owner | Supprimer un sondage |
| POST | `/api/v1/polls/{id}/start` | ✅ owner | Lancer un sondage |
| POST | `/api/v1/polls/{id}/options` | ✅ owner | Ajouter une option |
| PUT | `/api/v1/polls/{id}/options/{opt}` | ✅ owner | Modifier une option |
| DELETE | `/api/v1/polls/{id}/options/{opt}` | ✅ owner | Supprimer une option |
| GET | `/api/v1/polls/by-token/{token}` | ❌ | Voir un sondage via son lien |
| POST | `/api/v1/polls/by-token/{token}/votes` | ✅ | Voter |
| GET | `/api/v1/polls/by-token/{token}/votes/me` | ✅ | Mon vote actuel |
| GET | `/api/v1/polls/by-token/{token}/results` | ❌ (policy) | Résultats agrégés |
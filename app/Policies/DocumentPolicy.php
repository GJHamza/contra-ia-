<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Determine if the given document can be viewed by the user.
     */
    public function view(User $user, Document $document): bool
    {
        return $user->id === $document->user_id;
    }

    /**
     * Determine if the given document can be updated by the user.
     */
    public function update(User $user, Document $document): bool
    {
        return $user->id === $document->user_id;
    }

    /**
     * Determine if the given document can be deleted by the user.
     */
    public function delete(User $user, Document $document): bool
    {
        return $user->id === $document->user_id;
    }
}

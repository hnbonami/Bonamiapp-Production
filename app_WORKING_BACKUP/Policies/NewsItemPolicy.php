<?php

namespace App\Policies;

use App\Models\NewsItem;
use App\Models\User;

class NewsItemPolicy
{
    public function create(User $user)
    {
        return in_array($user->role, ['admin', 'medewerker']);
    }

    public function update(User $user, NewsItem $newsItem)
    {
        return in_array($user->role, ['admin', 'medewerker']);
    }

    public function delete(User $user, NewsItem $newsItem)
    {
        return in_array($user->role, ['admin', 'medewerker']);
    }
}

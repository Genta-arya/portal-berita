<?php
namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function view(User $user, Article $article): bool
    {
        return $user->active && ($this->manage($user) || $article->author_id === $user->id);
    }

    public function update(User $user, Article $article): bool
    {
        return $this->view($user, $article) && ($this->manage($user) || in_array($article->status, ['draft', 'needs_revision'], true));
    }

    public function manage(User $user): bool
    {
        return $user->managesArticles();
    }

    public function delete(User $user, Article $article): bool
    {
        return $article->published_at === null && $this->manage($user);
    }
}

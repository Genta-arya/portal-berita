<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;

    protected $attributes = ['status' => 'draft', 'version' => 1];

    protected $fillable = ['title', 'slug', 'summary', 'body', 'category_id', 'hero_media_id', 'hero_alt', 'hero_caption', 'hero_credit', 'guest_author', 'seo_title', 'seo_description', 'sponsored'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'scheduled_at' => 'datetime', 'withdrawn_at' => 'datetime', 'sponsored' => 'boolean', 'version' => 'integer'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function revisions()
    {
        return $this->hasMany(ArticleRevision::class);
    }

    public function getUrlAttribute(): string
    {
        return url('/berita/'.$this->id.'/'.$this->slug);
    }
}

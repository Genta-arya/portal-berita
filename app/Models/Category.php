<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'position', 'active'];
    protected function casts(): array { return ['active' => 'boolean']; }
    public function articles() { return $this->hasMany(Article::class); }
}

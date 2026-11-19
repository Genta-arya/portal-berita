<?php

namespace App\Http\Requests;

use App\Policies\ArticlePolicy;
use Illuminate\Foundation\Http\FormRequest;

class SaveArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && (new ArticlePolicy)->update($this->user(), $this->route('article'));
    }

    public function rules(): array
    {
        return [
            'version' => 'required|integer', 'title' => 'required|string|max:255', 'slug' => 'required|alpha_dash|max:255', 'summary' => 'nullable|string|max:1000', 'body' => 'nullable|string|max:200000', 'category_id' => 'nullable|exists:categories,id', 'tags' => 'nullable|string|max:1000', 'hero_media_id' => 'nullable|exists:media,id', 'hero_alt' => 'nullable|string|max:255', 'hero_credit' => 'nullable|string|max:255', 'hero_caption' => 'nullable|string|max:1000', 'guest_author' => 'nullable|string|max:255', 'seo_title' => 'nullable|string|max:255', 'seo_description' => 'nullable|string|max:500', 'sponsored' => 'sometimes|boolean', 'note' => 'nullable|string|max:2000', 'autosave' => 'sometimes|boolean',
        ];
    }
}

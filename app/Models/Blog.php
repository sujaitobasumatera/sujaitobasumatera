<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasImageFallback;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    use HasFactory;
    use \App\Traits\Syncable, HasImageFallback, SoftDeletes;

    const CREATED_AT = 'createdAt';

    const UPDATED_AT = 'updatedAt';
/**
 * @mixin \Eloquent
 */

    protected $fillable = [
        'slug', 'title', 'content', 'excerpt', 'image', 'cover_image_id', 'author', 'category', 'status', 'tags',
        'published_at', 'metaTitle', 'metaDescription',
    ];

    protected $appends = ['image_url', 'translated_title', 'translated_excerpt', 'translated_category'];

    protected $casts = [
        'tags' => 'array',
        'published_at' => 'datetime',
    ];

    public function coverImage()
    {
        return $this->belongsTo(Media::class, 'cover_image_id');
    }

    public function getImageUrlAttribute()
    {
        // Priority: media relation > legacy image field > fallback
        if ($this->coverImage) {
            return $this->coverImage->url;
        }
        
        return $this->resolveImageUrl($this->image);
    }

    public function getTranslatedTitleAttribute()
    {
        return __($this->title);
    }

    public function getTranslatedExcerptAttribute()
    {
        return __($this->excerpt);
    }

    public function getTranslatedCategoryAttribute()
    {
        return __($this->category);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use ZaimeaLabs\Sluggable\HasSlug;
use ZaimeaLabs\Sluggable\SlugOptions;

class ScopeableModel extends Model
{
    use HasSlug;

    protected $table = 'scopeable_models';

    protected $guarded = [];
    public $timestamps = false;

    public function newSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->extraScope(fn ($builder) => $builder->where('scope_id', $this->scope_id));
    }
}

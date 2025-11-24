---
title: How to use package
description: How to use package
github: https://github.com/zaimealabs/slugabble/edit/main/docs
onThisArticle: true
sidebar: true
rightbar: true
---

# Sluggable Usage

[[TOC]]

## Usage

```php 
namespace App;

use Zaimea\Sluggable\HasSlug;
use Zaimea\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;

class EloquentModel extends Model
{
    use HasSlug;

    /**
     * Create a new options for generating the slug.
     *
     * @return \Zaimea\Sluggable\SlugOptions
     */
    public function newSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }
}
```

### With route
```php
/**
 * Get the route key for the model.
 */
public function getRouteKeyName(): string
{
    return 'slug';
}
```

### Options for slug
```php
->generateSlugsFrom(['field', 'field_2'])
->saveSlugsTo('slug');
->allowDuplicateSlugs();
->slugsShouldBeNoLongerThan(50);
->usingSeparator('_');
->doNotGenerateSlugsOnCreate();
->doNotGenerateSlugsOnUpdate();
->preventOverwrite();
->startSlugSuffixFrom(2);
```

### Find models by slug

For convenience, you can use the alias `findBySlug` to retrieve a model. The query will compare against the field passed to `saveSlugsTo` when defining the `SlugOptions`.

```php
$model = Article::findBySlug('my-article');
```

`findBySlug` also accepts a second parameter `$columns` just like the default Eloquent `find` method.

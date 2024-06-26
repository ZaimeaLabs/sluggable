<p align="center">
  <a href="https://zaimea.com/" target="_blank">
    <img src=".github/sluggable.svg" alt="Sluggable" width="300">
  </a>
</p>
<p align="center">
  Generate aotomated slug for your models.
<p>
<p align="center">
    <a href="https://github.com/zaimealabs/sluggable/actions/workflows/sluggable-tests.yml"><img src="https://github.com/zaimealabs/sluggable/actions/workflows/sluggable-tests.yml/badge.svg" alt="Sluggable Tests"></a>
    <a href="https://github.com/zaimealabs/sluggable/blob/main/LICENSE"><img src="https://img.shields.io/badge/License-Mit-brightgreen.svg" alt="License"></a>
</p>
<div align="center">
  Hey 👋 thanks for considering making a donation, with these donations I can continue working to contribute to ZaimeaLabs projects.
  
  [![Donate](https://img.shields.io/badge/Via_PayPal-blue)](https://www.paypal.com/donate/?hosted_button_id=V6YPST5PUAUKS)
</div>

## Usage

```php 
namespace App;

use ZaimeaLabs\Sluggable\HasSlug;
use ZaimeaLabs\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;

class EloquentModel extends Model
{
    use HasSlug;

    /**
     * Create a new options for generating the slug.
     *
     * @return \ZaimeaLabs\Sluggable\SlugOptions
     */
    public function newSlugOptions() : SlugOptions
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

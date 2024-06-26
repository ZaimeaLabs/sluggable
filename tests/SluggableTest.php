<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Tests\Fixtures\ModelSoftDeletes;
use Tests\Fixtures\ScopeableModel;
use Tests\Fixtures\SluggableModel;
use ZaimeaLabs\Sluggable\SlugOptions;

it('can save a slug when saving a model', function () {
    $model = SluggableModel::create(['name' => 'this is a save test']);

    expect($model->url)->toEqual('this-is-a-save-test');
});

it('can handle null values when creating slugs', function () {
    $model = SluggableModel::create(['name' => null]);

    expect($model->url)->toEqual('-1');
});

it('will not change the slug when the source field is not changed', function () {
    $model = SluggableModel::create(['name' => 'not changed']);

    $model->other_field = 'otherValue';
    $model->save();

    expect($model->url)->toEqual('not-changed');
});

it('will use the source field if the slug field is empty', function () {
    $model = SluggableModel::create(['name' => 'is empty']);

    $model->url = null;
    $model->save();

    expect($model->url)->toEqual('is-empty');
});

it('will update the slug when the source field is changed', function () {
    $model = SluggableModel::create(['name' => 'is changed']);

    $model->name = 'update name';
    $model->save();

    expect($model->url)->toEqual('update-name');
});

it('will save a unique slug by default', function () {
    SluggableModel::create(['name' => 'unique slug']);

    foreach (range(1, 10) as $i) {
        $model = SluggableModel::create(['name' => 'unique slug']);

        expect($model->url)->toEqual("unique-slug-{$i}");
    }
});

it('can generate slugs from multiple source fields', function () {
    $model = new class () extends SluggableModel {
        public function newSlugOptions(): SlugOptions
        {
            return parent::newSlugOptions()->generateSlugsFrom(['name', 'other_field']);
        }
    };

    $model->name = 'a name';
    $model->other_field = 'a second field';
    $model->save();

    expect($model->url)->toEqual('a-name-a-second-field');
});

it('can generate slugs from a callable', function () {
    $model = new class () extends SluggableModel {
        public function newSlugOptions(): SlugOptions
        {
            return parent::newSlugOptions()->generateSlugsFrom(function (SluggableModel $model): string {
                return 'foo-' . Str::slug($model->name);
            });
        }
    };

    $model->name = 'a name';
    $model->save();

    expect($model->url)->toEqual('foo-a-name');
});

it('can generate duplicate slugs', function () {
    foreach (range(1, 10) as $ignored) {
        $model = new class () extends SluggableModel {
            public function newSlugOptions(): SlugOptions
            {
                return parent::newSlugOptions()->allowDuplicateSlugs();
            }
        };

        $model->name = 'duplicate name';
        $model->save();

        expect($model->url)->toEqual('duplicate-name');
    }
});

it('can generate slugs with a maximum length', function () {
    $model = new class () extends SluggableModel {
        public function newSlugOptions(): SlugOptions
        {
            return parent::newSlugOptions()->slugsShouldBeNoLongerThan(5);
        }
    };

    $model->name = '123456789';
    $model->save();

    expect($model->url)->toEqual('12345');
});

it('can handle weird characters when generating the slug', function (string $weirdCharacter, string $normalCharacter) {
    $model = SluggableModel::create(['name' => $weirdCharacter]);

    expect($model->url)->toEqual($normalCharacter);
})->with([
    ['é', 'e'],
    ['è', 'e'],
    ['à', 'a'],
    ['a€', 'aeur'],
    ['ß', 'ss'],
    ['a/ ', 'a'],
]);


it('can handle multibytes characters cutting when generating the slug', function () {
    $model = SluggableModel::create(['name' => 'là']);
    $model->setSlugOptions($model->newSlugOptions()->slugsShouldBeNoLongerThan(2));
    $model->generateSlug();

    expect($model->url)->toEqual('la');
});

it('can handle overwrites when updating a model', function () {
    $model = SluggableModel::create(['name' => 'over writes']);

    $model->url = 'an-url';
    $model->save();

    expect($model->url)->toEqual('an-url');
});

it('can handle duplicates when overwriting a slug', function () {
    $model = SluggableModel::create(['name' => 'a slug']);
    $otherModel = SluggableModel::create(['name' => 'an other slug']);

    $model->url = 'an-other-slug';
    $model->save();

    expect($model->url)->toEqual('an-other-slug-1');
});

it('has a method that prevents a slug being generated on condition', function () {
    $model = new class () extends SluggableModel {
        public function newSlugOptions(): SlugOptions
        {
            return parent::newSlugOptions()
                ->skipGenerateWhen(fn () => $this->name === 'foo');
        }
    };

    $model->name = 'foo';
    $model->save();

    expect($model->url)->toBeNull();

    $model->other_field = 'craft';
    $model->save();

    expect($model->url)->toBeNull();

    $model->name = 'is not a foo';
    $model->save();

    expect($model->url)->toEqual('is-not-a-foo');
});

it('has a method that prevents a slug being generated on creation', function () {
    $model = new class () extends SluggableModel {
        public function newSlugOptions(): SlugOptions
        {
            return parent::newSlugOptions()->doNotGenerateSlugsOnCreate();
        }
    };

    $model->name = 'test name';
    $model->save();

    expect($model->url)->toBeNull();
});

it('has a method that prevents a slug being generated on update', function () {
    $model = new class () extends SluggableModel {
        public function newSlugOptions(): SlugOptions
        {
            return parent::newSlugOptions()->doNotGenerateSlugsOnUpdate();
        }
    };

    $model->name = 'test name';
    $model->save();

    $model->name = 'another test name';
    $model->save();

    expect($model->url)->toEqual('test-name');
});

it('has an method that prevents a slug beign generated if already present', function () {
    $model = new class () extends SluggableModel {
        public function newSlugOptions(): SlugOptions
        {
            return parent::newSlugOptions()->preventOverwrite();
        }
    };

    $model->name = 'test-name';
    $model->url = 'already-generated-slug';
    $model->save();

    expect($model->url)->toEqual('already-generated-slug');
});

it('will use separator option for slug generation', function () {
    $model = new class () extends SluggableModel {
        public function newSlugOptions(): SlugOptions
        {
            return parent::newSlugOptions()->usingSeparator('_');
        }
    };

    $model->name = 'separator test';
    $model->save();

    expect($model->url)->toEqual('separator_test');
});

it('will save a unique slug by default even when soft deletes are on', function () {
    ModelSoftDeletes::create(['name' => 'test name', 'deleted_at' => date('Y-m-d h:i:s')]);

    foreach (range(1, 10) as $i) {
        $model = ModelSoftDeletes::create(['name' => 'test name']);

        expect($model->url)->toEqual("test-name-{$i}");
    }
});

it('will save a unique slug by default when replicating a model', function () {
    $model = SluggableModel::create(['name' => 'test name']);

    $replica = $model->replicate();
    $replica->save();

    expect($model->url)->toEqual('test-name');
    expect($replica->url)->toEqual('test-name-1');
});

it('will save a unique slug when replicating a model that does not generates slugs on update', function () {
    $model = new class () extends SluggableModel {
        public function newSlugOptions(): SlugOptions
        {
            return parent::newSlugOptions()->doNotGenerateSlugsOnUpdate();
        }
    };

    $model->name = 'test name';
    $model->save();

    $replica = $model->replicate();
    $replica->save();

    expect($model->url)->toEqual('test-name');
    expect($replica->url)->toEqual('test-name-1');
});

it('can generate slug suffix starting from given number', function () {
    $model = new class () extends SluggableModel {
        public function newSlugOptions(): SlugOptions
        {
            return parent::newSlugOptions()->startSlugSuffixFrom(2);
        }
    };

    $model->name = 'test name';
    $model->save();

    $replica = $model->replicate();
    $replica->save();

    expect($model->url)->toEqual('test-name');
    expect($replica->url)->toEqual('test-name-2');
});

it('can find models using findBySlug alias', function () {
    $model = new class () extends SluggableModel {
        public function newSlugOptions(): SlugOptions
        {
            return parent::newSlugOptions()->saveSlugsTo('url');
        }
    };

    $model->name = 'my custom url';
    $model->save();

    $savedModel = $model::findBySlug('my-custom-url');

    expect($savedModel->id)->toEqual($model->id);
});

it('generates same slug for each scope', function () {
    $SluggableModel = ScopeableModel::create(['name' => 'name', 'scope_id' => 1]);
    $SluggableModel2 = ScopeableModel::create(['name' => 'name', 'scope_id' => 2]);

    expect($SluggableModel->slug)->toBe($SluggableModel2->slug);
});

it('generates different slug for same scope', function () {
    $SluggableModel = ScopeableModel::create(['name' => 'name', 'scope_id' => 1]);
    $SluggableModel2 = ScopeableModel::create(['name' => 'name', 'scope_id' => 1]);

    expect($SluggableModel->slug)->not->toBe($SluggableModel2->slug);
});

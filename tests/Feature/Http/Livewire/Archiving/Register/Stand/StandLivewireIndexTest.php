<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Stand\StandLivewireIndex;
use App\Models\Shelf;
use App\Models\Stand;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->stand = Stand::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot list stand records without being authenticated', function () {
    logout();

    get(route('archiving.register.stand.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access stand records listing route', function () {
    get(route('archiving.register.stand.index'))
    ->assertForbidden();
});

test('cannot render listing component from stand records without specific permission', function () {
    Livewire::test(StandLivewireIndex::class)->assertForbidden();
});

// Happy path
test('pagination returns the amount of expected stand records', function () {
    grantPermission(PermissionType::StandViewAny->value);

    Stand::factory(30)->create();

    Livewire::test(StandLivewireIndex::class)
    ->set('per_page', 25)
    ->assertCount('stands', 25);
});

test('lists stand records with specific permission', function () {
    grantPermission(PermissionType::StandViewAny->value);

    get(route('archiving.register.stand.index'))
    ->assertOk()
    ->assertSeeLivewire(StandLivewireIndex::class);
});

test('search returns expected results', function () {
    grantPermission(PermissionType::StandViewAny->value);

    $this->stand->delete();

    Stand::factory()->create(['number' => 10]);
    Stand::factory()->create(['number' => 210]); // contains 10
    Stand::factory()->create(['number' => 20]);

    Livewire::test(StandLivewireIndex::class)
    ->set('term', '210')
    ->assertCount('stands', 1)
    ->set('term', '10')
    ->assertCount('stands', 2)
    ->set('term', '')
    ->assertCount('stands', 3);
});

test('emits feedback event when deleting a stand record', function () {
    grantPermission(PermissionType::StandViewAny->value);
    grantPermission(PermissionType::StandDelete->value);

    Livewire::test(StandLivewireIndex::class)
    ->call('setToDelete', $this->stand->id)
    ->call('destroy')
    ->assertOk()
    ->assertDispatchedBrowserEvent('notify', [
        'type' => FeedbackType::Success->value,
        'icon' => FeedbackType::Success->icon(),
        'header' => FeedbackType::Success->label(),
        'message' => null,
        'timeout' => 3000,
    ]);
});

test('StandLivewireIndex uses trait', function () {
    expect(
        collect(class_uses(StandLivewireIndex::class))
        ->has([
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});

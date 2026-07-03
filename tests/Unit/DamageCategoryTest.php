<?php

namespace Tests\Unit;

use App\Models\DamageCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DamageCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_damage_category(): void
    {
        $dc = DamageCategory::create([
            'name' => 'LCD Retak',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->assertNotNull($dc->id);
        $this->assertEquals('LCD Retak', $dc->name);
    }

    public function test_scope_active_returns_only_active(): void
    {
        DamageCategory::create(['name' => 'A', 'is_active' => true, 'sort_order' => 1]);
        DamageCategory::create(['name' => 'B', 'is_active' => false, 'sort_order' => 2]);

        $this->assertEquals(1, DamageCategory::active()->count());
    }

    public function test_scope_ordered_sorts_by_sort_order_then_name(): void
    {
        DamageCategory::create(['name' => 'Z', 'is_active' => true, 'sort_order' => 2]);
        DamageCategory::create(['name' => 'A', 'is_active' => true, 'sort_order' => 1]);

        $ordered = DamageCategory::ordered()->get();
        $this->assertEquals('A', $ordered[0]->name);
        $this->assertEquals('Z', $ordered[1]->name);
    }

    public function test_is_active_casts_to_boolean(): void
    {
        $dc = DamageCategory::create(['name' => 'Test', 'is_active' => true, 'sort_order' => 1]);
        $this->assertTrue($dc->is_active);
    }
}

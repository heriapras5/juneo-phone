<?php

namespace Tests\Unit;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::flushCache();
    }

    public function test_get_value_returns_value(): void
    {
        Setting::create(['key' => 'nama_toko', 'value' => 'Juneo Phone']);

        $this->assertEquals('Juneo Phone', Setting::getValue('nama_toko'));
    }

    public function test_get_value_returns_default_when_not_found(): void
    {
        $this->assertEquals('Default', Setting::getValue('nonexistent', 'Default'));
    }

    public function test_get_value_returns_null_default_when_not_set(): void
    {
        $this->assertNull(Setting::getValue('nonexistent'));
    }

    public function test_flush_cache_clears_cached_values(): void
    {
        Setting::create(['key' => 'test_key', 'value' => 'original']);

        $this->assertEquals('original', Setting::getValue('test_key'));

        Setting::where('key', 'test_key')->update(['value' => 'updated']);

        $this->assertEquals('original', Setting::getValue('test_key'));

        Setting::flushCache();

        $this->assertEquals('updated', Setting::getValue('test_key'));
    }
}

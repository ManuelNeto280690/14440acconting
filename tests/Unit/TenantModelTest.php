<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TenantModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_generates_uuid_on_creation()
    {
        $tenant = Tenant::factory()->create();
        
        $this->assertNotNull($tenant->id);
        $this->assertTrue(is_string($tenant->id));
        $this->assertEquals(36, strlen($tenant->id)); // UUID length
    }

    public function test_tenant_has_one_subscription()
    {
        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->create();
        
        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id
        ]);
        
        $this->assertInstanceOf(Subscription::class, $tenant->subscription);
        $this->assertEquals($subscription->id, $tenant->subscription->id);
    }

    public function test_tenant_can_check_if_active()
    {
        $activeTenant = Tenant::factory()->create([
            'data' => ['status' => 'active']
        ]);
        $inactiveTenant = Tenant::factory()->create([
            'data' => ['status' => 'inactive']
        ]);
        
        $this->assertTrue($activeTenant->isActive());
        $this->assertFalse($inactiveTenant->isActive());
    }

    public function test_tenant_can_get_database_name()
    {
        $tenant = Tenant::factory()->create();
        
        $expectedDatabaseName = 'tenant_' . $tenant->id;
        $this->assertEquals($expectedDatabaseName, $tenant->getDatabaseName());
    }

    public function test_tenant_can_get_full_domain()
    {
        $tenant = Tenant::factory()->create(['domain' => 'example']);
        
        $expectedDomain = 'example.' . config('app.url');
        $this->assertEquals($expectedDomain, $tenant->getFullDomain());
    }
}
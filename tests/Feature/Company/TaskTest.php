<?php

namespace Tests\Feature\Company;

use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class TaskTest extends TestCase
{
    use DatabaseMigrations;

    protected $token;

    public function setUp(): void
    {
        parent::setUp();

        $adminRole = factory(Role::class)->create(['name' => 'admin', 'title' => 'Administrator']);
        $user = factory(User::class)->create();
        $user->roles()->attach($adminRole);

        factory(Task::class, 2)->create(['company_id' => $user->company_id]);

        $this->token = JWTAuth::fromUser($user);
    }

    public function test_admin_can_get_all_tasks()
    {
        $this->withoutExceptionHandling();

        $response = $this->get('/v1/companies/tasks', [
            "Authorization" => "Bearer {$this->token}"
        ]);

        $response->assertStatus(200)->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'person_name',
                    'note',
                    'user_id',
                ]
            ],
        ]);
    }
}

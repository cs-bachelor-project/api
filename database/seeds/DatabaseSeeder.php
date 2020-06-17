<?php

use App\Models\Company;
use App\Models\PlanOption;
use App\Models\Role;
use App\Models\Task;
use App\Models\TaskDetail;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $adminRole = factory(Role::class)->create(['name' => 'admin', 'title' => 'Administrator']);
        $manager = factory(Role::class)->create(['name' => 'manager', 'title' => 'Manager']);
        $driverRole = factory(Role::class)->create(['name' => 'driver', 'title' => 'Driver']);

        factory(Company::class, 5)->create()->each(function ($company) use ($adminRole, $manager, $driverRole) {
            factory(User::class)->create(['company_id' => $company->id])->roles()->attach($adminRole);

            factory(User::class)->create(['company_id' => $company->id])->roles()->attach($manager);

            factory(User::class, 4)->create(['company_id' => $company->id])->each(function ($driver) use ($company, $driverRole) {
                factory(Task::class, 10)->create(['user_id' => $driver->id, 'company_id' => $company->id])->each(function ($task) {
                    factory(TaskDetail::class)->create(['action' => 'pick', 'task_id' => $task->id]);
                    factory(TaskDetail::class)->create(['action' => 'drop', 'task_id' => $task->id]);
                });

                $driver->roles()->attach($driverRole);
            });
        });

        factory(PlanOption::class)->create(['stripe_plan' => 'plan_HDtLlsSL3sqIYg', 'option' => 'max-drivers', 'value' => '5']);
        factory(PlanOption::class)->create(['stripe_plan' => 'plan_HDtLX0PusTLGI0', 'option' => 'max-drivers', 'value' => '15']);
        factory(PlanOption::class)->create(['stripe_plan' => 'plan_HDtMYrR7fKDDkh', 'option' => 'max-drivers', 'value' => '50']);
    }
}

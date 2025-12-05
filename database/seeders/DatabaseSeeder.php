<?php

namespace Database\Seeders;

// Core Laravel classes
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

// Models
use App\Models\User;
use App\Models\Role;
use App\Models\Team;
use App\Models\Project;
use App\Models\Review;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
        ]);

        if (App::environment('local')) {
            $this->command->info('Detected Local Environment. Seeding fake test data...');

            $this->seedDevelopmentData();

            $this->command->info('Development data seeded successfully!');
        }
    }

    /**
     * Logic to generate complex, interrelated test data.
     */
    private function seedDevelopmentData(): void
    {

        $managers = User::factory(5)->create([
            'password_hash' => Hash::make('password'),
        ]);

        $associates = User::factory(20)->create([
            'password_hash' => Hash::make('password'),
        ]);

        $managerRole = Role::where('role_name', 'Manager')->first();
        $associateRole = Role::where('role_name', 'Associate')->first();

        $managers->each(fn($u) => $u->roles()->attach($managerRole));
        $associates->each(fn($u) => $u->roles()->attach($associateRole));

        $teams = Team::factory(5)->create();

        foreach ($teams as $team) {
            $team->members()->attach($managers->random());

            $teamMembers = $associates->random(rand(3, 5));
            $team->members()->attach($teamMembers);
        }

        $admin = User::where('email', 'admin@company.com')->first();

        $projects = Project::factory(10)->create([
            'created_by' => $admin?->user_id
        ]);

        foreach ($projects as $project) {

            $assignedTeams = $teams->random(rand(1, 2));
            $project->teams()->attach($assignedTeams);

            $teamUserIds = DB::table('team_members')
                ->whereIn('team_id', $assignedTeams->pluck('team_id'))
                ->pluck('user_id');

            foreach ($assignedTeams as $team) {
                $members = $team->members;

                foreach ($members as $member) {
                    if (rand(1, 100) <= 60) {
                        Review::factory()->create([
                            'project_id' => $project->project_id,
                            'reviewer_id' => $member->user_id,
                            'rating' => rand(3, 5), // Generally positive
                            'content' => 'Standard review from team member.',
                        ]);
                    }
                }
            }

            $potentialAdvisor = $managers->whereNotIn('user_id', $teamUserIds)->first();

            if ($potentialAdvisor) {
                $project->advisors()->attach($potentialAdvisor);

                Review::factory()->create([
                    'project_id' => $project->project_id,
                    'reviewer_id' => $potentialAdvisor->user_id,
                    'rating' => rand(2, 4),
                    'content' => 'INTERNAL ADVISOR REVIEW: I have reviewed the deliverables per the executive request.',
                ]);
            }
        }
    }
}

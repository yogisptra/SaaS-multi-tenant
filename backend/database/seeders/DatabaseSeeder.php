<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create 1 Company
        $company = Company::create([
            'uuid' => Str::uuid(),
            'name' => 'PT Teknologi Nusantara',
            'slug' => 'pt-teknologi-nusantara',
            'status' => 'active',
        ]);

        // Create 1 Admin
        $admin = User::create([
            'uuid' => Str::uuid(),
            'company_id' => $company->id,
            'name' => 'Admin User',
            'email' => 'admin@saas.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Create 3 Members
        $members = [];
        $memberData = [
            ['name' => 'Budi Santoso', 'email' => 'budi@saas.test'],
            ['name' => 'Siti Rahayu', 'email' => 'siti@saas.test'],
            ['name' => 'Andi Pratama', 'email' => 'andi@saas.test'],
        ];

        foreach ($memberData as $data) {
            $members[] = User::create([
                'uuid' => Str::uuid(),
                'company_id' => $company->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'member',
                'status' => 'active',
            ]);
        }

        // Create 3 Projects
        $projectData = [
            [
                'name' => 'Website Redesign',
                'description' => 'Redesign the company website with modern UI/UX principles.',
                'status' => 'in_progress',
                'start_date' => '2026-07-01',
                'due_date' => '2026-09-30',
            ],
            [
                'name' => 'Mobile App Development',
                'description' => 'Develop a cross-platform mobile application for customers.',
                'status' => 'pending',
                'start_date' => '2026-08-01',
                'due_date' => '2026-12-31',
            ],
            [
                'name' => 'API Integration',
                'description' => 'Integrate third-party payment and shipping APIs.',
                'status' => 'in_progress',
                'start_date' => '2026-07-15',
                'due_date' => '2026-10-15',
            ],
        ];

        $projects = [];
        foreach ($projectData as $data) {
            $projects[] = Project::create(array_merge($data, [
                'uuid' => Str::uuid(),
                'company_id' => $company->id,
                'created_by' => $admin->id,
            ]));
        }

        // Create 20 Tasks spread across 3 projects
        $taskTemplates = [
            // Project 1: Website Redesign (7 tasks)
            ['project' => 0, 'title' => 'Create wireframes', 'description' => 'Design wireframes for homepage, about, and contact pages.', 'priority' => 'high', 'status' => 'done', 'member' => 0],
            ['project' => 0, 'title' => 'Design mockups', 'description' => 'Create high-fidelity mockups based on wireframes.', 'priority' => 'high', 'status' => 'in_progress', 'member' => 0],
            ['project' => 0, 'title' => 'Implement responsive layout', 'description' => 'Code the responsive CSS layout for all pages.', 'priority' => 'medium', 'status' => 'todo', 'member' => 1],
            ['project' => 0, 'title' => 'Setup CI/CD pipeline', 'description' => 'Configure GitHub Actions for automated testing and deployment.', 'priority' => 'medium', 'status' => 'in_progress', 'member' => 2],
            ['project' => 0, 'title' => 'SEO optimization', 'description' => 'Implement meta tags and structured data.', 'priority' => 'low', 'status' => 'todo', 'member' => 1],
            ['project' => 0, 'title' => 'Performance audit', 'description' => 'Run Lighthouse audits and optimize performance.', 'priority' => 'medium', 'status' => 'todo', 'member' => 2],
            ['project' => 0, 'title' => 'Browser testing', 'description' => 'Test across Chrome, Firefox, Safari, and Edge.', 'priority' => 'high', 'status' => 'todo', 'member' => 0],

            // Project 2: Mobile App Development (7 tasks)
            ['project' => 1, 'title' => 'Setup React Native project', 'description' => 'Initialize project with TypeScript template.', 'priority' => 'high', 'status' => 'todo', 'member' => 1],
            ['project' => 1, 'title' => 'Design app navigation', 'description' => 'Implement tab and stack navigation structure.', 'priority' => 'high', 'status' => 'todo', 'member' => 0],
            ['project' => 1, 'title' => 'Build authentication screens', 'description' => 'Create login, register, and forgot password screens.', 'priority' => 'high', 'status' => 'todo', 'member' => 1],
            ['project' => 1, 'title' => 'Implement push notifications', 'description' => 'Setup Firebase Cloud Messaging for push notifications.', 'priority' => 'medium', 'status' => 'todo', 'member' => 2],
            ['project' => 1, 'title' => 'Build product listing', 'description' => 'Create product list with search and filter.', 'priority' => 'medium', 'status' => 'todo', 'member' => 0],
            ['project' => 1, 'title' => 'Build cart module', 'description' => 'Implement shopping cart with quantity management.', 'priority' => 'medium', 'status' => 'todo', 'member' => 1],
            ['project' => 1, 'title' => 'App store submission', 'description' => 'Prepare screenshots and submit to Play Store and App Store.', 'priority' => 'low', 'status' => 'todo', 'member' => 2],

            // Project 3: API Integration (6 tasks)
            ['project' => 2, 'title' => 'Research payment gateways', 'description' => 'Compare Midtrans, Xendit, and Stripe for payment processing.', 'priority' => 'high', 'status' => 'done', 'member' => 2],
            ['project' => 2, 'title' => 'Implement Midtrans integration', 'description' => 'Integrate Midtrans payment gateway with snap.js.', 'priority' => 'high', 'status' => 'in_progress', 'member' => 2],
            ['project' => 2, 'title' => 'Implement shipping API', 'description' => 'Integrate RajaOngkir for shipping cost calculation.', 'priority' => 'medium', 'status' => 'todo', 'member' => 0],
            ['project' => 2, 'title' => 'Write API documentation', 'description' => 'Document all API endpoints with Swagger/OpenAPI.', 'priority' => 'medium', 'status' => 'todo', 'member' => 1],
            ['project' => 2, 'title' => 'Setup webhook handlers', 'description' => 'Handle payment status callbacks from Midtrans.', 'priority' => 'high', 'status' => 'todo', 'member' => 2],
            ['project' => 2, 'title' => 'Integration testing', 'description' => 'Write integration tests for all API endpoints.', 'priority' => 'medium', 'status' => 'todo', 'member' => 1],
        ];

        foreach ($taskTemplates as $template) {
            Task::create([
                'uuid' => Str::uuid(),
                'company_id' => $company->id,
                'project_id' => $projects[$template['project']]->id,
                'assigned_to' => $members[$template['member']]->id,
                'title' => $template['title'],
                'description' => $template['description'],
                'priority' => $template['priority'],
                'status' => $template['status'],
                'due_date' => now()->addDays(rand(7, 60)),
            ]);
        }
    }
}

<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Services\TaskManagementService;
use App\Http\Services\UserManagementService;
use App\Constants\KeyType;
use App\Constants\Category;
use App\Auth\AuthContext;

// Sample data for randomization
$titles = [
    'Complete project documentation',
    'Review pull requests',
    'Update database schema',
    'Fix authentication bug',
    'Implement new feature',
    'Optimize search queries',
    'Write unit tests',
    'Deploy to production',
    'Refactor legacy code',
    'Update dependencies',
];

$descriptions = [
    'This task requires immediate attention and careful review',
    'Low priority task that can be completed when time permits',
    'Critical bug that affects user experience',
    'Enhancement requested by the product team',
    'Technical debt that needs to be addressed',
    'Performance improvement for better user experience',
    'Security update required for compliance',
    'New functionality based on customer feedback',
    'Maintenance task to keep the system running smoothly',
    'Research and investigation needed before implementation',
];

// Users to create/use
$users = [
    ['email' => 'john.doe@example.com', 'name' => 'John Doe', 'password' => password_hash('password123', PASSWORD_BCRYPT)],
    ['email' => 'jane.smith@example.com', 'name' => 'Jane Smith', 'password' => password_hash('password123', PASSWORD_BCRYPT)],
    ['email' => 'bob.wilson@example.com', 'name' => 'Bob Wilson', 'password' => password_hash('password123', PASSWORD_BCRYPT)],
];

foreach ($users as $userData) {
    try {
        // Try to create the user
        $userId = UserManagementService::createUser($userData);
        echo "Created user: {$userData['email']} (ID: $userId)\n";
    } catch (Exception $e) {
        // If user already exists, find by email
        echo "User {$userData['email']} already exists, finding...\n";
        $existingUser = UserManagementService::findByEmail($userData['email']);
        $userId = $existingUser['id'];
        echo "Found user: {$userData['email']} (ID: $userId)\n";
    }

    // Bind AuthContext for current user
    $app->singleton(AuthContext::class, function () use ($userId, $userData) {
        return new AuthContext(
            userId: $userId,
            email: $userData['email'],
            jwt: 'mock-jwt-token-' . $userId
        );
    });

    // Get fresh instance of TaskManagementService with new AuthContext
    $taskService = $app->make(TaskManagementService::class);

    // Create 5 random tasks for this user
    $tasksCreated = 0;
    for ($i = 0; $i < 5; $i++) {
        // Generate random created_at date (between 60 days ago and today)
        $daysAgo = rand(0, 60);
        try {
            $createdAt = new DateTime('now', new DateTimeZone('UTC'))
                ->modify("-$daysAgo days")
                ->modify('-' . rand(0, 23) . ' hours')
                ->modify('-' . rand(0, 59) . ' minutes')
                ->modify('-' . rand(0, 59) . ' seconds');

            // Generate due_date (between created_at and 30 days after created_at)
            $daysUntilDue = rand(0, 30);
            $dueDate = (clone $createdAt)->modify("+$daysUntilDue days");
        } catch (Exception $e) {
            echo "  Failed to create task: " . $e->getMessage() . "\n";
            continue;
        }

        // Randomize completed status
        $completed = (bool)rand(0, 1);

        $data = [
            'title' => $titles[array_rand($titles)],
            'description' => $descriptions[array_rand($descriptions)],
            'category' => Category::cases()[array_rand(Category::cases())]->value,
            'created_at' => $createdAt->format('Y-m-d\TH:i:s.u\Z'),
            'due_date' => $dueDate->format('Y-m-d'),
            'completed' => $completed,
        ];

        try {
            $taskService->createEntity(KeyType::Task, $data);
            $tasksCreated++;
        } catch (Exception $e) {
            echo "  Failed to create task: " . $e->getMessage() . "\n";
        }
    }

    echo "  Created $tasksCreated tasks for {$userData['email']}\n\n";
}

echo "Seeding completed!\n";

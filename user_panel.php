<?php
include 'includes/auth_check.php';
requireLogin();

include 'config/database.php';
include 'models/User.php';

// Get users info
$database = new Database();
$db = $database->getConnection();
$user_model = new User($db);

// Get user logged info
$user = $user_model->readOne($_SESSION['user_id']);

include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<div id="content" class="ml-64 transition-all duration-300 p-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-2">Your information</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
        </div>

        <div class="flex justify-center pt-4 mt-4 border-t">
            <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-md">
                Log Out
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php
include 'includes/auth_check.php';
include 'config/database.php';
include 'models/User.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// Initialization of variables
$database = new Database();
$db = $database->getConnection();
$user_model = new User($db);

// Errors of register form
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        // Validate required fields
        if (empty($username)) {
            $error = 'Username is required.';
        } else if (empty($email)) {
            $error = 'Email is required.';
        } else if (empty($password)) {
            $error = 'Password is required.';
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format.';
        } else if (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } else {
            // Handle image upload
            $profile_pic_path = null;

            if (isset($_FILES['profilepic']) && $_FILES['profilepic']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['profilepic'];
                $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
                $tmp_file = $file['tmp_name'];

                // Get file extension
                $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                // Validate file type
                if (!in_array($file['type'], $allowed_types) || !in_array($file_extension, $allowed_extensions)) {
                    $error = 'File type not allowed. Only JPG, JPEG, PNG and WEBP images are allowed.';
                } else {
                    // Create directory if it doesn't exist
                    $upload_dir = 'assets/img/profile_pics/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $file_hash = md5_file($tmp_file);
                    $new_filename = $username . "_profile_pic_" . $file_hash . "." . $file_extension; // Using the file hash
                    $upload_path = $upload_dir . $new_filename;

                    // Move file
                    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                        $profile_pic_path = $upload_path;
                    } else {
                        $error = 'Error uploading image. Please try again.';
                    }
                }
            }

            if (empty($error)) {
                if ($user_model->create($username, $email, 'user', $password, $profile_pic_path)) {
                    header('Location: login.php');
                    exit;
                } else {
                    $error = 'Error creating user. Please try again.';
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Ticketing System
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Register to access the system
            </p>
        </div>

        <form class="mt-8 space-y-6 bg-white p-8 shadow rounded-lg" method="POST" action="register.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create">

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <div class="space-y-6">
                <div class="relative">
                    <input id="username" name="username" type="text" required
                        class="peer h-10 w-full border border-gray-300 rounded-md px-3 pt-4 pb-1 
                        focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm
                        placeholder-transparent has-[:not(:placeholder-shown)]:pt-4 has-[:not(:placeholder-shown)]:pb-1"
                        placeholder="Username">
                    <label for="username"
                        class="absolute left-3 top-2 text-gray-500 text-sm transition-all 
                        peer-placeholder-shown:top-2.5 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 
                        peer-focus:top-1 peer-focus:text-sm peer-focus:text-primary-600
                        peer-[:not(:placeholder-shown)]:top-1 peer-[:not(:placeholder-shown)]:text-sm">
                        Username
                    </label>
                </div>

                <div class="relative">
                    <input id="email" name="email" type="email" required
                        class="peer h-10 w-full border border-gray-300 rounded-md px-3 pt-4 pb-1 
                        focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm
                        placeholder-transparent has-[:not(:placeholder-shown)]:pt-4 has-[:not(:placeholder-shown)]:pb-1"
                        placeholder="Email">
                    <label for="email"
                        class="absolute left-3 top-2 text-gray-500 text-sm transition-all 
                        peer-placeholder-shown:top-2.5 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 
                        peer-focus:top-1 peer-focus:text-sm peer-focus:text-primary-600
                        peer-[:not(:placeholder-shown)]:top-1 peer-[:not(:placeholder-shown)]:text-sm">
                        Email
                    </label>
                </div>

                <div class="relative">
                    <input id="password" name="password" type="password" required
                        class="peer h-10 w-full border border-gray-300 rounded-md px-3 pt-4 pb-1 
                        focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm
                        placeholder-transparent has-[:not(:placeholder-shown)]:pt-4 has-[:not(:placeholder-shown)]:pb-1"
                        placeholder="Password">
                    <label for="password"
                        class="absolute left-3 top-2 text-gray-500 text-sm transition-all 
                        peer-placeholder-shown:top-2.5 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 
                        peer-focus:top-1 peer-focus:text-sm peer-focus:text-primary-600
                        peer-[:not(:placeholder-shown)]:top-1 peer-[:not(:placeholder-shown)]:text-sm">
                        Password
                    </label>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="profilepic" class="text-sm text-gray-500">Profile Picture (PNG, JPG, JPEG, WEBP):</label>
                    <input id="profilepic" name="profilepic" type="file" accept=".png,.jpg,.jpeg,.webp" required
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                   file:rounded-md file:border-0 file:text-sm file:font-semibold
                   file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100" />
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <button type="submit"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent 
                               text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 
                               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Register
                </button>
                <a href="./login.php" class="text-gray-600 text-sm hover:font-bold transition-all">Already have an account? Log in</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
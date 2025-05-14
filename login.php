<?php
include 'includes/auth_check.php';
include 'config/database.php';
include 'models/User.php';

// If already authenticated, redirect to dashboard
if (isLoggedIn()) {
  header('Location: dashboard.php');
  exit;
}

// Get database connection
$database = new Database();
$db = $database->getConnection();
$user_model = new User($db);

// Process login form
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';

  if ($user_model->authenticate($email, $password)) {
    // Get authenticated user data
    $_SESSION['user_id'] = $user_model->id;
    $_SESSION['user_name'] = $user_model->name;
    $_SESSION['user_email'] = $user_model->email;
    $_SESSION['user_role'] = $user_model->role;
    $_SESSION['profile_pic'] = $user_model->profile_pic;

    header('Location: dashboard.php');
    exit;
  } else {
    $error = 'Invalid credentials';
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
        Log in to access the system
      </p>
    </div>

    <form class="mt-8 space-y-6 bg-white p-8 shadow rounded-lg" method="POST" action="login.php">
      <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
          <span class="block sm:inline"><?php echo $error; ?></span>
        </div>
      <?php endif; ?>

      <div class="rounded-md shadow-sm -space-y-px">
        <div>
          <label for="email" class="sr-only">Email address</label>
          <input id="email" name="email" type="email" autocomplete="email" required
            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 
                                  placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none 
                                  focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
            placeholder="Email address">
        </div>
        <div>
          <label for="password" class="sr-only">Password</label>
          <input id="password" name="password" type="password" autocomplete="current-password" required
            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 
                                  placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none 
                                  focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm"
            placeholder="Password">
        </div>
      </div>

      <div>
        <button type="submit"
          class="group relative w-full flex justify-center py-2 px-4 border border-transparent 
                               text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 
                               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
          Log in
        </button>
      </div>
    </form>
    <a href="./register.php" class="text-gray-600 text-sm hover:font-bold transition-all">If you don't have an account... <i class="fa-solid fa-arrow-right"></i></a>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
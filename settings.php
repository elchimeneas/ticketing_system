<?php
include 'includes/auth_check.php';
include 'config/database.php';
include 'includes/header.php';

requireLogin();
requireRole('administrator');

// Get settings info
$database = new Database();
$db = $database->getConnection();
$allSettings = new Settings($db);

$settings = $allSettings->read();

$success = false;
$error = '';

$safe_email_domains = [
  "gmail.com",         # Gmail, de Google
  "outlook.com",       # Outlook, de Microsoft
  "hotmail.com",       # Hotmail, de Microsoft
  "hotmail.es",       # Hotmail, de Microsoft
  "icloud.com",        # iCloud Mail, de Apple
];

function isValidAdminEmail($admin_email, $safe_domains)
{
  if (filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
    $domain = substr(strrchr($admin_email, "@"), 1);

    if (in_array($domain, $safe_domains)) {
      return true;
    } else {
      return false;
    }
  } else {
    return false;
  }
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Validar datos
  $site_name = $_POST['site_name'] ?? '';
  $admin_email = $_POST['admin_email'] ?? '';

  if (empty($site_name)) {
    $error = 'The site name is mandatory.';
  } else if (empty($admin_email) || !filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
    $error = 'The email you provided is not valid.';
  } else if (!isValidAdminEmail($admin_email, $safe_email_domains)) {
    $error = 'You must use a safe email domain, for example: gmail.com, outlook.com or icloud.com';
  } else {
    $settings = $allSettings->updateAdminEmail($admin_email);
    $settings = $allSettings->updatePageTitle($site_name);
    $settings = $allSettings->read();
    $success = true;
    header('location: ' . $_SERVER['PHP_SELF']);
  }
}


?>

<?php include 'includes/sidebar.php'; ?>

<div id="content" class="ml-64 transition-all duration-300 p-8">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Configuración del Sistema</h1>
  </div>

  <?php if ($success): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
      <p class="font-bold">Éxito</p>
      <p>La configuración ha sido guardada correctamente.</p>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
      <p class="font-bold">Error</p>
      <p><?php echo $error; ?></p>
    </div>
  <?php endif; ?>

  <div class="bg-white rounded-lg shadow p-6">
    <form action="settings.php" method="POST">
      <div class="gap-6">
        <!-- Configuración General -->
        <div>
          <h2 class="text-lg font-semibold mb-4">Configuración General</h2>

          <div class="mb-4">
            <label for="site_name" class="block text-sm font-medium text-gray-700 mb-1">Nombre del Sitio *</label>
            <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings[0]['site_name']); ?>" required
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
          </div>

          <div class="mb-4">
            <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-1">Email del Administrador *</label>
            <input type="email" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($settings[0]['admin_email']); ?>" required
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
          </div>
        </div>
        <div class="pt-6 mt-6">
          <div class="flex justify-center">
            <button type="reset" class="bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-md mr-2">
              Restablecer
            </button>
            <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-md">
              Guardar Configuración
            </button>
          </div>
        </div>
    </form>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php
include 'includes/auth_check.php';
requireLogin();

include 'config/database.php';
include 'models/Category.php';
include 'models/Ticket.php';

// Initialization of variables
$database = new Database();
$db = $database->getConnection();
$ticket_model = new Ticket($db);
$category = new Category($db);

// Read categories from database
$categories = $category->read();

$success = false;
$error = '';

// Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $subject = $_POST['subject'] ?? '';
  $message = $_POST['message'] ?? '';
  $category_id = $_POST['category_id'] ?? '';

  // Checking fields
  if (empty($subject) || empty($message) || empty($category_id)) {
    $error = 'Por favor, completa todos los campos obligatorios.';
  } else {
    $ticket_model->create($subject, $category_id, $message, $_SESSION['user_id']);
    $success = true;
  }
}

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div id="content" class="ml-64 transition-all duration-300 p-8">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Crear Nuevo Ticket</h1>
    <a href="tickets.php" class="text-primary-600 hover:text-primary-800 flex items-center">
      <i class="fas fa-arrow-left mr-2"></i>
      <span>Volver a la lista</span>
    </a>
  </div>

  <?php if ($success): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
      <p class="font-bold">Éxito</p>
      <p>El ticket ha sido creado correctamente.</p>
      <div class="mt-3">
        <a href="tickets.php" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md inline-block">
          Ver todos los tickets
        </a>
      </div>
    </div>
  <?php else: ?>

    <?php if ($error): ?>
      <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p class="font-bold">Error</p>
        <p><?php echo $error; ?></p>
      </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-6">
      <form action="new_ticket.php" method="POST">
        <div class="mb-4">
          <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Asunto *</label>
          <input type="text" id="subject" name="subject" required
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
        </div>

        <div class="mb-4">
          <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Categoría *</label>
          <select id="category_id" name="category_id" required
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
            <option value="">Selecciona una categoría</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-6">
          <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Descripción *</label>
          <textarea id="message" name="message" rows="6" required
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"></textarea>
        </div>

        <div class="flex justify-end">
          <a href="tickets.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-md mr-2">
            Cancelar
          </a>
          <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-md">
            Crear Ticket
          </button>
        </div>
      </form>
    </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
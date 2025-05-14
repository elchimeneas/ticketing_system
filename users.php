<?php
include 'includes/auth_check.php';
requireLogin();
requireRole('administrator');

include 'config/database.php';
include 'models/User.php';

// Initialization of variables
$database = new Database();
$db = $database->getConnection();
$user_model = new User($db);

// Filters
$role = isset($_GET['role']) ? $_GET['role'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$users = $user_model->read();

// Apply filters
if (!empty($role) && $role !== 'all') {
  $users = array_filter($users, function ($u) use ($role) {
    return $u['role'] === $role;
  });
}

if (!empty($search)) {
  $search = strtolower($search);
  $users = array_filter($users, function ($u) use ($search) {
    return strpos(strtolower($u['name']), $search) !== false ||
      strpos(strtolower($u['email']), $search) !== false;
  });
}

// Flag variables
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
  $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

  if ($user_id <= 0) {
    $error = 'ID de usuario inválido.';
  } else if ($user_id === $_SESSION['user_id']) {
    $error = 'No puedes eliminar tu propio usuario.';
  } else {
    $user_model->delete($user_id);
    $success = true;
    $users = $user_model->read();
  }
}

// Create user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
  $name = isset($_POST['name']) ? trim($_POST['name']) : '';
  $email = isset($_POST['email']) ? trim($_POST['email']) : '';
  $password = isset($_POST['password']) ? trim($_POST['password']) : '';
  $role = isset($_POST['role']) ? trim($_POST['role']) : '';

  if (empty($name)) {
    $error = 'El nombre es obligatorio.';
  } else if (empty($email)) {
    $error = 'El email es obligatorio.';
  } else if (empty($password)) {
    $error = 'La contraseña es obligatoria.';
  } else if (empty($role)) {
    $error = 'El rol es obligatorio.';
  } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'El formato del email no es válido.';
  } else if (strlen($password) < 8) {
    $error = 'La contraseña debe tener al menos 8 caracteres.';
  } else if (!in_array($role, ['user', 'support', 'administrator'])) {
    $error = 'El rol seleccionado no es válido.';
  } else {
    if ($user_model->create($name, $email, $role, $password)) {
      $success = true;
      $users = $user_model->read();
    } else {
      $error = 'Error al crear el usuario. Por favor intente nuevamente.';
    }
  }
}

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div id="content" class="ml-64 transition-all duration-300 p-8">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Users Managament</h1>
    <button id="new-user-btn" class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-lg flex items-center">
      <i class="fas fa-user-plus mr-2"></i>
      <span>New User</span>
    </button>
  </div>

  <?php if ($success): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
      <p class="font-bold">Success</p>
      <p>User has been deleted successfully.</p>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
      <p class="font-bold">Error</p>
      <p><?php echo $error; ?></p>
    </div>
  <?php endif; ?>

  <!-- Filters -->
  <div class="bg-white rounded-lg shadow p-4 mb-6">
    <form action="users.php" method="GET" class="flex flex-wrap gap-4">
      <div class="flex-1 min-w-[200px]">
        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
          </div>
          <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>"
            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
            placeholder="Buscar por nombre o email">
        </div>
      </div>

      <div class="w-full sm:w-auto">
        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
        <select id="role" name="role"
          class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
          <option value="all" <?php echo $role === 'all' || $role === '' ? 'selected' : ''; ?>>All</option>
          <option value="user" <?php echo $role === 'user' ? 'selected' : ''; ?>>User</option>
          <option value="support" <?php echo $role === 'support' ? 'selected' : ''; ?>>Support</option>
          <option value="administrator" <?php echo $role === 'administrator' ? 'selected' : ''; ?>>Administrator</option>
        </select>
      </div>

      <div class="flex items-end">
        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-md">
          Filter
        </button>
        <a href="users.php" class="ml-2 text-gray-600 hover:text-gray-900 py-2 px-2">
          <i class="fas fa-sync-alt"></i>
        </a>
      </div>
    </form>
  </div>

  <!-- Users List -->
  <div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creation Date</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <?php if (empty($users)): ?>
          <tr>
            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
              No users found
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($users as $u): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                <?php echo $u['id']; ?>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <?php echo htmlspecialchars($u['name']); ?>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <?php echo htmlspecialchars($u['email']); ?>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <?php if ($u['role'] === 'administrator'): ?>
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                    Administrator
                  </span>
                <?php elseif ($u['role'] === 'support'): ?>
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                    Support
                  </span>
                <?php else: ?>
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                    User
                  </span>
                <?php endif; ?>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <?php echo date('d/m/Y H:i', strtotime($u['created_at'])); ?>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button class="edit-user-btn text-primary-600 hover:text-primary-900 mr-3" data-id="<?php echo $u['id']; ?>">
                  <i class="fas fa-edit"></i>
                </button>
                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                  <button class="delete-user-btn text-red-600 hover:text-red-900" data-id="<?php echo $u['id']; ?>" data-name="<?php echo htmlspecialchars($u['name']); ?>">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- New User Modal -->
<div id="user-modal" class="modal">
  <div class="modal-content max-w-md w-full">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold" id="modal-title">New User</h2>
      <button id="close-modal" class="text-gray-500 hover:text-gray-700 focus:outline-none">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <form id="user-form" action="users.php" method="POST">
      <input type="hidden" id="user_id" name="user_id" value="">
      <input type="hidden" id="action" name="action" value="create">

      <div class="mb-4">
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
        <input type="text" id="name" name="name" required
          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
      </div>

      <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
        <input type="email" id="email" name="email" required
          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
      </div>

      <div class="mb-4">
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
        <input type="password" id="password" name="password" required
          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
        <p class="text-xs text-gray-500 mt-1" id="password-hint">Password must have almost 8 characters.</p>
      </div>

      <div class="mb-6">
        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
        <select id="user_role" name="role" required
          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
          <option value="user">User</option>
          <option value="support">Support</option>
          <option value="administrator">Administrator</option>
        </select>
      </div>

      <div class="flex justify-end">
        <button type="button" id="cancel-form" class="bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-md mr-2">
          Cancel
        </button>
        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-md">
          Save
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div id="confirm-modal" class="modal">
  <div class="modal-content max-w-md w-full">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold">Confirm deleting</h2>
      <button id="close-confirm-modal" class="text-gray-500 hover:text-gray-700 focus:outline-none">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <p class="mb-6">¿Are you sure want to delete ticket #<span id="ticket-id-confirm" class="font-semibold"></span>? This cannot be undone.</p>

    <form id="delete-form" action="users.php" method="POST">
      <input type="hidden" id="delete_user_id" name="user_id" value="">
      <input type="hidden" name="action" value="delete">

      <div class="flex justify-end">
        <button type="button" id="cancel-delete" class="bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-md mr-2">
          Cancel
        </button>
        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-md">
          Delete
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const userModal = document.getElementById('user-modal');
    const confirmModal = document.getElementById('confirm-modal');
    const newUserBtn = document.getElementById('new-user-btn');
    const closeModalBtn = document.getElementById('close-modal');
    const cancelFormBtn = document.getElementById('cancel-form');
    const closeConfirmModalBtn = document.getElementById('close-confirm-modal');
    const cancelDeleteBtn = document.getElementById('cancel-delete');
    const editUserBtns = document.querySelectorAll('.edit-user-btn');
    const deleteUserBtns = document.querySelectorAll('.delete-user-btn');
    const userForm = document.getElementById('user-form');

    // Abrir modal para nuevo usuario
    newUserBtn.addEventListener('click', function() {
      document.getElementById('modal-title').textContent = 'Nuevo Usuario';
      document.getElementById('user_id').value = '';
      document.getElementById('action').value = 'create';
      document.getElementById('password').required = true;
      document.getElementById('password-hint').style.display = 'block';
      userForm.reset();
      userModal.classList.add('active');
    });

    // Cerrar modal de usuario
    closeModalBtn.addEventListener('click', function() {
      userModal.classList.remove('active');
    });

    cancelFormBtn.addEventListener('click', function() {
      userModal.classList.remove('active');
    });

    // Cerrar modal de confirmación
    closeConfirmModalBtn.addEventListener('click', function() {
      confirmModal.classList.remove('active');
    });

    cancelDeleteBtn.addEventListener('click', function() {
      confirmModal.classList.remove('active');
    });

    // Abrir modal para editar usuario
    editUserBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const userId = this.getAttribute('data-id');
        document.getElementById('modal-title').textContent = 'Editar Usuario';
        document.getElementById('user_id').value = userId;
        document.getElementById('action').value = 'update';
        document.getElementById('password').required = false;
        document.getElementById('password-hint').style.display = 'none';

        // Obtener datos reales del usuario por AJAX
        fetch('get_user.php?id=' + userId)
          .then(response => response.json())
          .then(user => {
            if (user && user.id) {
              document.getElementById('name').value = user.name || '';
              document.getElementById('email').value = user.email || '';
              document.getElementById('user_role').value = user.role || '';
            } else {
              document.getElementById('name').value = '';
              document.getElementById('email').value = '';
              document.getElementById('user_role').value = '';
            }
            userModal.classList.add('active');
          });
      });
    });

    // Abrir modal para confirmar eliminación
    deleteUserBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const userId = this.getAttribute('data-id');
        const userName = this.getAttribute('data-name');

        document.getElementById('delete_user_id').value = userId;
        document.getElementById('user-name-confirm').textContent = userName;

        confirmModal.classList.add('active');
      });
    });

    // Cerrar modales al hacer clic fuera de ellos
    window.addEventListener('click', function(event) {
      if (event.target === userModal) {
        userModal.classList.remove('active');
      }
      if (event.target === confirmModal) {
        confirmModal.classList.remove('active');
      }
    });
  });
</script>

<?php include 'includes/footer.php'; ?>
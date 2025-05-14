<?php
include 'includes/auth_check.php';
requireLogin();

include 'config/database.php';
include 'models/Ticket.php';

// Initialization of variables
$database = new Database();
$db = $database->getConnection();
$ticket = new Ticket($db);

// Filters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Read the tickets on database
$tickets = $ticket->read();

// Apply filters
if (!empty($status) && $status !== 'all') {
  $tickets = array_filter($tickets, function ($t) use ($status) {
    return $t['status'] === $status;
  });
}

if (!empty($search)) {
  $search = strtolower($search);
  $tickets = array_filter($tickets, function ($t) use ($search) {
    return strpos(strtolower($t['subject']), $search) !== false ||
      strpos(strtolower($t['created_by_name']), $search) !== false;
  });
}

// Flag variable and errors
$success = false;
$error = '';

// Delete a ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
  $ticket_id = isset($_POST['ticket_id']) ? intval($_POST['ticket_id']) : 0;

  if ($ticket_id <= 0) {
    $error = 'Ticket ID invalid.';
  } else {
    if ($ticket->delete($ticket_id)) {
      $tickets = $ticket->read();
      $success = true;
    } else {
      $error = 'No se pudo eliminar el ticket.';
    }
  }
}

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div id="content" class="ml-64 transition-all duration-300 p-8">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Ticket Details</h1>
    <a href="new_ticket.php" class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-lg flex items-center">
      <i class="fas fa-plus mr-2"></i>
      <span>New Ticket</span>
    </a>
  </div>

  <!-- Filtros -->
  <div class="bg-white rounded-lg shadow p-4 mb-6">
    <form action="tickets.php" method="GET" class="flex flex-wrap gap-4">
      <div class="flex-1 min-w-[200px]">
        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
          </div>
          <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>"
            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
            placeholder="Buscar por asunto o creador">
        </div>
      </div>

      <div class="w-full sm:w-auto">
        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
        <select id="status" name="status"
          class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
          <option value="all" <?php echo $status === 'all' || $status === '' ? 'selected' : ''; ?>>All</option>
          <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
          <option value="in_progress" <?php echo $status === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
          <option value="resolved" <?php echo $status === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
        </select>
      </div>

      <div class="flex items-end">
        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-md">
          Filtrar
        </button>
        <a href="tickets.php" class="ml-2 text-gray-600 hover:text-gray-900 py-2 px-2">
          <i class="fas fa-sync-alt"></i>
        </a>
      </div>
    </form>
  </div>

  <!-- Tickets list -->
  <div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Topic</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created by</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned to</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <?php if (empty($tickets)): ?>
          <tr>
            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
              No tickets found
            </td>
          </tr>
        <?php else: ?>
          <?php if (isAdminOrSupport()): ?>
            <?php foreach ($tickets as $t): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  <?php echo $t['id']; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <?php echo htmlspecialchars($t['subject']); ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <?php echo htmlspecialchars($t['category_name']); ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <?php if ($t['status'] === 'pending'): ?>
                    <span class="badge badge-pending">Pending</span>
                  <?php elseif ($t['status'] === 'in_progress'): ?>
                    <span class="badge badge-in-progress">In Progress</span>
                  <?php else: ?>
                    <span class="badge badge-resolved">Resolved</span>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <?php echo htmlspecialchars($t['created_by_name']); ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <?php echo $t['assigned_to_name'] ? htmlspecialchars($t['assigned_to_name']) : '-'; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <?php echo date('d/m/Y H:i', strtotime($t['created_at'])); ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <a href="ticket_detail.php?id=<?php echo $t['id']; ?>" class="text-primary-600 hover:text-primary-900">
                    <i class="fas fa-eye"></i>
                  </a>
                  <button class="delete-user-btn text-red-600 hover:text-red-900 ml-[5px]" data-id="<?php echo $t['id']; ?>">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </td>
              <?php endforeach; ?>
            <?php endif; ?>
              </tr>
              <?php foreach ($tickets as $t): ?>
                <?php if ($_SESSION['user_name'] == $t['created_by_name'] && !isAdminOrSupport()): ?>

                  <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      <?php echo $t['id']; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <?php echo htmlspecialchars($t['subject']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <?php echo htmlspecialchars($t['category_name']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <?php if ($t['status'] === 'pending'): ?>
                        <span class="badge badge-pending">Pending</span>
                      <?php elseif ($t['status'] === 'in_progress'): ?>
                        <span class="badge badge-in-progress">In Progress</span>
                      <?php else: ?>
                        <span class="badge badge-resolved">Resolved</span>
                      <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <?php echo htmlspecialchars($t['created_by_name']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <?php echo $t['assigned_to_name'] ? htmlspecialchars($t['assigned_to_name']) : '-'; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <?php echo date('d/m/Y H:i', strtotime($t['created_at'])); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <a href="ticket_detail.php?id=<?php echo $t['id']; ?>" class="text-primary-600 hover:text-primary-900">
                        <i class="fas fa-eye"></i>
                      </a>
                      <button class="delete-user-btn text-red-600 hover:text-red-900 ml-[5px]" data-id="<?php echo $t['id']; ?>">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </td>
                  </tr>

                <?php endif; ?>
              <?php endforeach; ?>
            <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($success): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
      <p class="font-bold">Success</p>
      <p>Ticket has been deleted successfully.</p>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
      <p class="font-bold">Error</p>
      <p><?php echo $error; ?></p>
    </div>
  <?php endif; ?>
</div>

<!-- Confirmation deleting modal -->
<div id="confirm-modal" class="modal">
  <div class="modal-content max-w-md w-full">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold">Confirm deleting</h2>
      <button id="close-confirm-modal" class="text-gray-500 hover:text-gray-700 focus:outline-none">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <p class="mb-6">¿Are you sure want to delete ticket #<span id="ticket-id-confirm" class="font-semibold"></span>? This cannot be undone.</p>
    <form id="delete-form" action="tickets.php" method="POST">
      <input type="hidden" id="delete_ticket_id" name="ticket_id" value="">
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
    const confirmModal = document.getElementById('confirm-modal');
    const closeConfirmModalBtn = document.getElementById('close-confirm-modal');
    const cancelDeleteBtn = document.getElementById('cancel-delete');
    const deleteTicketBtns = document.querySelectorAll('.delete-user-btn');

    // Open deleting modal
    deleteTicketBtns.forEach(btn => {
      btn.addEventListener('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        const ticketId = this.getAttribute('data-id');
        document.getElementById('delete_ticket_id').value = ticketId;
        document.getElementById('ticket-id-confirm').textContent = ticketId;
        confirmModal.classList.add('active');
      });
    });

    // Close deleting modal
    closeConfirmModalBtn.addEventListener('click', function() {
      confirmModal.classList.remove('active');
    });

    cancelDeleteBtn.addEventListener('click', function() {
      confirmModal.classList.remove('active');
    });

    // Close modal when clic is out of it
    window.addEventListener('click', function(event) {
      if (event.target === confirmModal) {
        confirmModal.classList.remove('active');
      }
    });
  });
</script>

<?php include 'includes/footer.php'; ?>
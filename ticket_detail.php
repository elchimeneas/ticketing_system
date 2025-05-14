<?php
include 'includes/auth_check.php';
requireLogin();

include 'config/database.php';
include 'models/Ticket.php';
include 'models/User.php';
include 'models/Category.php';
include 'models/TicketMessage.php';

// Obtener el ID del ticket
$ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($ticket_id <= 0) {
  header('Location: tickets.php');
  exit;
}

// Obtener detalles del ticket
$database = new Database();
$db = $database->getConnection();
$ticket_model = new Ticket($db);
$user_model = new User($db);
$category = new Category($db);
$message_model = new TicketMessage($db);

$ticket = $ticket_model->readOne($ticket_id);
$messages = $message_model->getTicketMessages($ticket_id);
$can_reply = $message_model->canUserReply($ticket_id, $_SESSION['user_id'], $_SESSION['user_role']);

// Obtener todas las categorías
$categories = $category->read();

// Usuarios de soporte para asignar
$support_users = array_merge(
  $user_model->readByRole('support'),
  $user_model->readByRole('administrator')
);

$success = false;
$error = '';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Procesar nuevo mensaje
  if (isset($_POST['action']) && $_POST['action'] === 'send_message') {
    if ($can_reply) {
      $message_model->ticket_id = $ticket_id;
      $message_model->user_id = $_SESSION['user_id'];
      $message_model->message = $_POST['message'];
      // Si es admin o support, el usuario podrá responder
      $message_model->can_reply = ($_SESSION['user_role'] === 'administrator' || $_SESSION['user_role'] === 'support');

      if ($message_model->create()) {
        $success = true;
        // Recargar mensajes
        $messages = $message_model->getTicketMessages($ticket_id);
        $can_reply = $message_model->canUserReply($ticket_id, $_SESSION['user_id'], $_SESSION['user_role']);
      } else {
        $error = 'Error sending message.';
      }
    } else {
      $error = 'You cannot reply at this moment. Please wait for support response.';
    }
  }

  // Asignar ticket
  if (isset($_POST['action']) && $_POST['action'] === 'assign') {
    $assigned_to = $_POST['assigned_to'] ?? '';

    if (empty($assigned_to)) {
      $error = 'Por favor, selecciona un usuario para asignar el ticket.';
    } else {
      $ticket_model->assign($ticket_id, $assigned_to);
      $success = true;
      // Vuelve a cargar el ticket actualizado
      $ticket = $ticket_model->readOne($ticket_id);
    }
  }

  // Actualizar estado
  if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $new_status = $_POST['status'] ?? '';

    if (empty($new_status)) {
      $error = 'Por favor, selecciona un estado para actualizar el ticket.';
    } else {
      $ticket_model->updateStatus($ticket_id, $new_status);
      $success = true;
      // Vuelve a cargar el ticket actualizado
      $ticket = $ticket_model->readOne($ticket_id);
    }
  }

  if (isset($_POST['action']) && $_POST['action'] === 'update_category') {
    $new_category = $_POST['category'] ?? '';

    if (empty($new_category)) {
      $error = 'Por favor, selecciona una categoria para actualizar el ticket.';
    } else {
      $ticket_model->updateCategory($ticket_id, $new_category);
      $success = true;
      // Vuelve a cargar el ticket actualizado
      $ticket = $ticket_model->readOne($ticket_id);
    }
  }
}

include 'includes/header.php';
?>
<?php include 'includes/sidebar.php'; ?>

<?php if ($_SESSION['user_name'] == $ticket[0]['created_by_name'] || isAdminOrSupport()): ?>
  <div id="content" class="ml-64 transition-all duration-300 p-8">
    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center">
        <a href="tickets.php" class="text-primary-600 hover:text-primary-800 mr-3">
          <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold">Ticket #<?php echo $ticket[0]['id']; ?></h1>
      </div>

      <?php if ($ticket[0]['status'] === 'pending'): ?>
        <span class="badge badge-pending">Pending</span>
      <?php elseif ($ticket[0]['status'] === 'in_progress'): ?>
        <span class="badge badge-in-progress">In Progress</span>
      <?php else: ?>
        <span class="badge badge-resolved">Resolved</span>
      <?php endif; ?>
    </div>

    <?php if ($success): ?>
      <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p class="font-bold">Success</p>
        <p>Ticket updated successfully.</p>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p class="font-bold">Error</p>
        <p><?php echo $error; ?></p>
      </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <h2 class="text-xl font-semibold mb-4"><?php echo htmlspecialchars($ticket[0]['subject']); ?></h2>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div>
          <div class="flex items-center text-gray-500 text-sm mb-1">
            <i class="fas fa-user mr-2"></i>
            <span>Created by:</span>
          </div>
          <div class="font-medium"><?php echo htmlspecialchars($ticket[0]['created_by_name']); ?></div>
        </div>

        <div>
          <div class="flex items-center text-gray-500 text-sm mb-1">
            <i class="fas fa-user-check mr-2"></i>
            <span>Assigned to:</span>
          </div>
          <div class="font-medium"><?php echo $ticket[0]['assigned_to_name'] ? htmlspecialchars($ticket[0]['assigned_to_name']) : 'Sin asignar'; ?></div>
        </div>

        <div>
          <div class="flex items-center text-gray-500 text-sm mb-1">
            <i class="fas fa-folder mr-2"></i>
            <span>Category:</span>
          </div>
          <div class="font-medium"><?php echo htmlspecialchars($ticket[0]['category_name']); ?></div>
        </div>

        <div>
          <div class="flex items-center text-gray-500 text-sm mb-1">
            <i class="fas fa-calendar mr-2"></i>
            <span>Date:</span>
          </div>
          <div class="font-medium"><?php echo date('d/m/Y H:i', strtotime($ticket[0]['created_at'])); ?></div>
        </div>
      </div>

      <div class="border-t border-gray-200 pt-4">
        <h3 class="text-lg font-medium mb-2">Description</h3>
        <p class="text-gray-700 whitespace-pre-line"><?php echo htmlspecialchars($ticket[0]['message']); ?></p>
      </div>
    </div>

    <!-- Chat Section -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <h3 class="text-lg font-medium mb-4">Messages</h3>

      <div class="space-y-4 mb-6 max-h-96 overflow-y-auto" id="chat-messages">
        <?php foreach ($messages as $message): ?>
          <div class="flex <?php echo $message['user_id'] == $_SESSION['user_id'] ? 'justify-end' : 'justify-start'; ?>">
            <div class="flex items-start max-w-3/4 <?php echo $message['user_id'] == $_SESSION['user_id'] ? 'flex-row-reverse' : ''; ?>">
              <img src="<?php echo htmlspecialchars($message['profile_pic']); ?>"
                alt="<?php echo htmlspecialchars($message['user_name']); ?>"
                class="w-8 h-8 rounded-full <?php echo $message['user_id'] == $_SESSION['user_id'] ? 'ml-3' : 'mr-3'; ?>">

              <div>
                <div class="flex items-center <?php echo $message['user_id'] == $_SESSION['user_id'] ? 'justify-end' : ''; ?> mb-1">
                  <span class="text-sm font-medium text-gray-900">
                    <?php echo htmlspecialchars($message['user_name']); ?>
                  </span>
                  <span class="text-xs text-gray-500 ml-2">
                    <?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?>
                  </span>
                </div>

                <div class="<?php echo $message['user_id'] == $_SESSION['user_id'] ?
                              'bg-primary-100 text-primary-800' :
                              'bg-gray-100 text-gray-800'; ?> 
                           rounded-lg px-4 py-2">
                  <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($can_reply): ?>
        <form action="ticket_detail.php?id=<?php echo $ticket_id; ?>" method="POST" class="mt-4">
          <input type="hidden" name="action" value="send_message">

          <div class="flex items-start space-x-4">
            <div class="flex-grow">
              <textarea name="message" rows="3" required
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                placeholder="Type your message..."></textarea>
            </div>
            <button type="submit"
              class="inline-flex items-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
              Send
              <i class="fas fa-paper-plane ml-2"></i>
            </button>
          </div>
        </form>
      <?php else: ?>
        <?php if ($ticket[0]['status'] === 'resolved'): ?>
          <div class="mt-4 bg-green-50 border-l-4 border-green-400 p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <i class="fas fa-check text-green-400"></i>
              </div>
              <div class="ml-3">
                <p class="text-sm text-green-700">
                  Ticket is resolved, thanks for use our ticketing system.
                </p>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
              </div>
              <div class="ml-3">
                <p class="text-sm text-yellow-700">
                  Please wait for support team to respond before sending another message.
                </p>
              </div>
            </div>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <?php if (isAdminOrSupport()): ?>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php if ($ticket[0]['status'] === 'pending'): ?>
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium mb-4">Assign Ticket</h3>
            <form action="ticket_detail.php?id=<?php echo $ticket[0]['id']; ?>" method="POST">
              <input type="hidden" name="action" value="assign">

              <div class="mb-4">
                <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">Assign to</label>
                <select id="assigned_to" name="assigned_to" required
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                  <option value="">Select an user</option>
                  <?php foreach ($support_users as $user): ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['name']); ?> (<?php echo ucfirst($user['role']); ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>

              <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-md">
                Asignar
              </button>
            </form>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium mb-4">Change category</h3>
            <form action="ticket_detail.php?id=<?php echo $ticket[0]['id']; ?>" method="POST">
              <input type="hidden" name="action" value="update_category">

              <div class="mb-4">
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Asignar a</label>
                <select id="category" name="category" required
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                  <option value="">Select a category</option>
                  <?php foreach ($categories as $item): ?>
                    <option value="<?php echo $item['id']; ?>"><?php echo $item["name"] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <button type=" submit" class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-md">
                Change
              </button>
            </form>
          </div>
        <?php endif; ?>

        <?php if ($ticket[0]['status'] !== 'resolved' || isAdminOrSupport()): ?>
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium mb-4">Update status</h3>
            <form action="ticket_detail.php?id=<?php echo $ticket[0]['id']; ?>" method="POST">
              <input type="hidden" name="action" value="update_status">

              <div class="mb-4">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select id="status" name="status" required
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                  <option value="">Select a status</option>
                  <option value="pending" <?php echo $ticket[0]['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                  <option value="in_progress" <?php echo $ticket[0]['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                  <option value="resolved">Resolved</option>
                </select>
              </div>

              <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-md">
                Update Status
              </button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

<?php else: ?>

  <div id="content" class="ml-64 transition-all duration-300 p-8">
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
      <p class="font-bold">Error</p>
      <p>We're sorry, you're not allowed to see this ticket.</p>
    </div>
  </div>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>

<script>
  // Scroll to bottom of chat messages
  document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chat-messages');
    if (chatMessages) {
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }
  });
</script>
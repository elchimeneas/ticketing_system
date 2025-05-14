<?php
include 'includes/auth_check.php';
requireLogin();

include 'config/database.php';
include 'models/Ticket.php';

// Initialization of variables
$database = new Database();
$db = $database->getConnection();
$ticket = new Ticket($db);

$allTickets = $ticket->read();

$pendingTickets = array_filter($allTickets, function ($ticket) {
  return $ticket['status'] === 'pending';
});
$pendingTickets = count($pendingTickets);

$inProgress = array_filter($allTickets, function ($ticket) {
  return $ticket['status'] === 'in_progress';
});
$inProgressTickets = count($inProgress);

$resolved = array_filter($allTickets, function ($ticket) {
  return $ticket['status'] === 'resolved';
});
$resolvedTickets = count($resolved);

$totalTickets = $pendingTickets + $inProgressTickets + $resolvedTickets;

// Categorías de tickets
$access = array_filter($allTickets, function ($ticket) {
  return $ticket['category_name'] === 'Access';
});
$accessTickets = count($access);

$errors = array_filter($allTickets, function ($ticket) {
  return $ticket['category_name'] === 'Errors';
});
$errorsTickets = count($errors);

$consultation = array_filter($allTickets, function ($ticket) {
  return $ticket['category_name'] === 'Asking';
});
$consultationTickets = count($consultation);

$improvements = array_filter($allTickets, function ($ticket) {
  return $ticket['category_name'] === 'Improvements';
});
$improvementsTickets = count($improvements);

// Categorías más comunes
$topCategories = [
  ['name' => 'Access', 'count' => $accessTickets],
  ['name' => 'Errors', 'count' => $errorsTickets],
  ['name' => 'Asking', 'count' => $consultationTickets],
  ['name' => 'Improvements', 'count' => $improvementsTickets]
];

include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<div id="content" class="ml-64 transition-all duration-300 p-8">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Dashboard</h1>
    <div class="flex items-center justify-center">
      <div class="flex items-center justify-center bg-primary-300 rounded-full w-[50px] h-[50px] overflow-hidden mr-2">
        <img class="w-full" src="<?php if (empty($_SESSION['profile_pic'])) {
                                    echo $_SESSION['profile_pic'];
                                  } else {
                                    echo "assets/img/profilepic.webp";
                                  }; ?>" alt="Profile pic of <?php echo $_SESSION['user_name']; ?>">
      </div>
      <span>Welcome <strong><?php echo $_SESSION['user_name']; ?></strong></span>
    </div>
  </div>

  <!-- Tarjetas de resumen -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
      <div class="text-gray-500 text-sm">All Tickets</div>
      <div class="text-3xl font-bold"><?php echo $totalTickets; ?></div>
    </div>

    <div class="bg-amber-50 rounded-lg shadow p-6">
      <div class="flex items-center">
        <i class="fas fa-clock text-amber-600 mr-2"></i>
        <div class="text-gray-500 text-sm">Pending</div>
      </div>
      <div class="text-3xl font-bold text-amber-600"><?php echo $pendingTickets; ?></div>
    </div>

    <div class="bg-blue-50 rounded-lg shadow p-6">
      <div class="flex items-center">
        <i class="fas fa-spinner text-blue-600 mr-2"></i>
        <div class="text-gray-500 text-sm">In Progress</div>
      </div>
      <div class="text-3xl font-bold text-blue-600"><?php echo $inProgressTickets; ?></div>
    </div>

    <div class="bg-green-50 rounded-lg shadow p-6">
      <div class="flex items-center">
        <i class="fas fa-check-circle text-green-600 mr-2"></i>
        <div class="text-gray-500 text-sm">Resolved</div>
      </div>
      <div class="text-3xl font-bold text-green-600"><?php echo $resolvedTickets; ?></div>
    </div>
  </div>

  <!-- Contenido principal -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Tickets por categoría -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-lg font-semibold mb-4">Tickets by Category</h2>
      <ul class="divide-y divide-gray-200">
        <?php if (empty(!$allTickets)) : ?>
          <?php foreach ($topCategories as $category): ?>
            <li class="py-3 flex justify-between items-center">
              <div class="flex items-center">
                <i class="fas fa-folder text-primary-500 mr-3"></i>
                <span><?php echo $category['name']; ?></span>
              </div>
              <div class="flex items-center">
                <span class="text-gray-600 mr-2"><?php echo $category['count']; ?> tickets</span>
                <span class="text-sm font-semibold">
                  <?php echo round(($category['count'] / $totalTickets) * 100); ?>%
                </span>
              </div>
            </li>
          <?php endforeach; ?>
        <?php else : ?>
          <p>No tickets available.</p>
        <?php endif; ?>
      </ul>
    </div>

    <!-- Recent Tickets -->
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-lg font-semibold mb-4"><?php if ($_SESSION['user_role'] === 'user') {
                                                echo "Your Most Recent Tickets";
                                              } else {
                                                echo "Most Recent Tickets";
                                              } ?></h2>
      <ul class="divide-y divide-gray-200">
        <?php if (isAdminOrSupport()): ?>
          <?php foreach ($allTickets as $ticket): ?>
            <li class="py-3">
              <a href="ticket_detail.php?id=<?php echo $ticket['id']; ?>" class="flex items-center hover:bg-gray-50 -mx-4 px-4 py-2 rounded-lg">
                <?php if ($ticket['status'] === 'pending'): ?>
                  <span class="badge badge-pending mr-3">Pending</span>
                <?php elseif ($ticket['status'] === 'in_progress'): ?>
                  <span class="badge badge-in-progress mr-3">In Progress</span>
                <?php else: ?>
                  <span class="badge badge-resolved mr-3">Solved</span>
                <?php endif; ?>

                <div class="flex-1">
                  <div class="font-medium">#<?php echo $ticket['id']; ?> - <?php echo $ticket['subject']; ?></div>
                  <div class="text-sm text-gray-500">
                    <?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?>
                  </div>
                </div>

                <i class="fas fa-chevron-right text-gray-400"></i>
              </a>
            </li>
          <?php endforeach; ?>
        <?php else: ?>
          <?php
          $userTickets = array_filter($allTickets, function ($ticket) {
            return $ticket['created_by_name'] === $_SESSION['user_name'];
          });
          foreach ($userTickets as $ticket):
          ?>
            <li class="py-3">
              <a href="ticket_detail.php?id=<?php echo $ticket['id']; ?>" class="flex items-center hover:bg-gray-50 -mx-4 px-4 py-2 rounded-lg">
                <?php if ($ticket['status'] === 'pending'): ?>
                  <span class="badge badge-pending mr-3">Pending</span>
                <?php elseif ($ticket['status'] === 'in_progress'): ?>
                  <span class="badge badge-in-progress mr-3">In Progress</span>
                <?php else: ?>
                  <span class="badge badge-resolved mr-3">Solved</span>
                <?php endif; ?>

                <div class="flex-1">
                  <div class="font-medium">#<?php echo $ticket['id']; ?> - <?php echo $ticket['subject']; ?></div>
                  <div class="text-sm text-gray-500">
                    <?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?>
                  </div>
                </div>

                <i class="fas fa-chevron-right text-gray-400"></i>
              </a>
            </li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>

      <div class="mt-4 text-center">
        <a href="tickets.php" class="text-primary-600 hover:text-primary-800 text-sm font-medium">
          See all tickets </a>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php if (isAdminOrSupport()): ?>
  <div id="sidebar" class="sidebar bg-stone-800 text-white h-screen w-64 fixed left-0 top-0 overflow-y-auto">
  <?php else: ?>
    <div id="sidebar" class="sidebar bg-gray-800 text-white h-screen w-64 fixed left-0 top-0 overflow-y-auto">
    <?php endif; ?>
    <div class="p-4 flex items-center justify-between">
      <h2 class="text-xl font-bold sidebar-text">Ticketing system</h2>
      <button id="toggle-sidebar" class="text-white focus:outline-none">
        <i class="fas fa-bars"></i>
      </button>
    </div>

    <div class="flex items-center justify-center">
      <div class="flex items-center justify-center bg-primary-300 rounded-full w-[50px] h-[50px] overflow-hidden mr-2">
        <img class="w-full" src="<?php if (empty($_SESSION['profile_pic'])) {
                                    echo $_SESSION['profile_pic'];
                                  } else {
                                    echo "assets/img/profilepic.webp";
                                  }; ?>" alt="Profile pic of <?php echo $_SESSION['user_name']; ?>">
      </div>
      <span><strong><?php echo $_SESSION['user_name']; ?></strong></span>
    </div>

    <nav class="mt-8">
      <a href="dashboard.php" class="flex items-center px-4 py-3 hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-gray-700' : ''; ?>">
        <i class="fas fa-tachometer-alt w-6"></i>
        <span class="ml-2 sidebar-text">Dashboard</span>
      </a>

      <a href="tickets.php" class="flex items-center px-4 py-3 hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) == 'tickets.php' ? 'bg-gray-700' : ''; ?>">
        <i class="fas fa-ticket-alt w-6"></i>
        <span class="ml-2 sidebar-text"><?php
                                        if ($_SESSION['user_role'] === 'support' || $_SESSION['user_role'] === 'administrator') {
                                          echo "Tickets";
                                        } else {
                                          echo "My Tickets";
                                        }
                                        ?></span>
      </a>

      <a href="new_ticket.php" class="flex items-center px-4 py-3 hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) == 'new_ticket.php' ? 'bg-gray-700' : ''; ?>">
        <i class="fas fa-plus w-6"></i>
        <span class="ml-2 sidebar-text">New Ticket</span>
      </a>

      <?php if (hasRole('administrator')): ?>
        <div class="px-4 py-2 text-xs text-gray-400 uppercase sidebar-text">
          Administración
        </div>
        <a href="users.php" class="flex items-center px-4 py-3 hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'bg-gray-700' : ''; ?>">
          <i class="fas fa-users w-6"></i>
          <span class="ml-2 sidebar-text">Users</span>
        </a>
        <a href="settings.php" class="flex items-center px-4 py-3 hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'bg-gray-700' : ''; ?>">
          <i class="fas fa-cog w-6"></i>
          <span class="ml-2 sidebar-text">Settings</span>
        </a>
      <?php endif; ?>



      <div class="px-4 py-2 text-xs text-gray-400 uppercase mt-6 sidebar-text">
        User
      </div>
      <a href="user_panel.php" class="flex items-center px-4 py-3 hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) == 'user_panel.php' ? 'bg-gray-700' : ''; ?>">
        <i class="fas fa-user-alt w-6"></i>
        <span class="ml-2 sidebar-text">User Panel</span>
      </a>
      <a href="logout.php" class="flex items-center px-4 py-3 hover:bg-gray-700">
        <i class="fas fa-sign-out-alt w-6"></i>
        <span class="ml-2 sidebar-text">Log Out</span>
      </a>
    </nav>
    </div>

    <script>
      document.getElementById('toggle-sidebar').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('collapsed');
        document.getElementById('content').classList.toggle('ml-20');
        document.getElementById('content').classList.toggle('ml-64');
      });
    </script>
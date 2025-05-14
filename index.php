<?php
include 'includes/auth_check.php';

// Redirigir al dashboard si está autenticado, o al login si no lo está
if (isLoggedIn()) {
  header('Location: dashboard.php');
} else {
  header('Location: login.php');
}
exit;

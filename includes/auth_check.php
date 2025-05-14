<?php
session_start();

// Verificar si el usuario está autenticado
function isLoggedIn()
{
  return isset($_SESSION['user_id']);
}

// Verificar si el usuario tiene un rol específico
function hasRole($role)
{
  if (!isLoggedIn()) {
    return false;
  }
  return $_SESSION['user_role'] === $role;
}

// Verificar si el usuario es administrador o soporte
function isAdminOrSupport()
{
  if (!isLoggedIn()) {
    return false;
  }
  return $_SESSION['user_role'] === 'administrator' || $_SESSION['user_role'] === 'support';
}

// Redirigir si no está autenticado
function requireLogin()
{
  if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
  }
}

// Redirigir si no tiene el rol requerido
function requireRole($role)
{
  if (!hasRole($role)) {
    header('Location: dashboard.php?error=unauthorized');
    exit;
  }
}

// Redirigir si no es administrador o soporte
function requireAdminOrSupport()
{
  if (!isAdminOrSupport()) {
    header('Location: dashboard.php?error=unauthorized');
    exit;
  }
}

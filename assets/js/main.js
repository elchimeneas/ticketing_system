// Función para mostrar notificaciones
function showNotification(message, type = "success") {
  // Crear elemento de notificación
  const notification = document.createElement("div");
  notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
    type === "success"
      ? "bg-green-500"
      : type === "error"
      ? "bg-red-500"
      : type === "warning"
      ? "bg-yellow-500"
      : "bg-blue-500"
  } text-white`;

  notification.innerHTML = `
      <div class="flex items-center">
          <i class="fas fa-${
            type === "success"
              ? "check-circle"
              : type === "error"
              ? "exclamation-circle"
              : type === "warning"
              ? "exclamation-triangle"
              : "info-circle"
          } mr-2"></i>
          <span>${message}</span>
      </div>
  `;

  // Añadir al DOM
  document.body.appendChild(notification);

  // Animar entrada
  setTimeout(() => {
    notification.classList.add("fade-in");
  }, 10);

  // Eliminar después de 3 segundos
  setTimeout(() => {
    notification.style.opacity = "0";
    setTimeout(() => {
      document.body.removeChild(notification);
    }, 300);
  }, 3000);
}

// Función para confirmar acciones
function confirmAction(message, callback) {
  if (confirm(message)) {
    callback();
  }
}

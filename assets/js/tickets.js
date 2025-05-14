document.addEventListener("DOMContentLoaded", function () {
  // Inicializar la página de tickets
  initTicketsPage();
});

function initTicketsPage() {
  // Añadir eventos a los botones de filtro
  const filterForm = document.querySelector("form");
  const resetButton = document.querySelector('a[href="tickets.php"]');

  if (resetButton) {
    resetButton.addEventListener("click", function (e) {
      e.preventDefault();
      // Resetear los campos del formulario
      filterForm.reset();
      // Enviar el formulario
      filterForm.submit();
    });
  }

  // Añadir eventos a las filas de la tabla para hacerlas clickeables
  const tableRows = document.querySelectorAll("tbody tr");
  tableRows.forEach((row) => {
    row.addEventListener("click", function () {
      const id = this.querySelector("td:first-child").textContent.trim();
      window.location.href = `ticket_detail.php?id=${id}`;
    });
  });
}

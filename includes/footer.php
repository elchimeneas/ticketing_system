<script src="assets/js/main.js"></script>
<?php
// Cargar scripts específicos según la página
$current_page = basename($_SERVER['PHP_SELF'], '.php');
if (file_exists("assets/js/{$current_page}.js")) {
  echo "<script src=\"assets/js/{$current_page}.js\"></script>";
}
?>
</body>

</html>
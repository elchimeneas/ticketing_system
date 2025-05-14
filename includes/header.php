<?php
include 'models/Settings.php';
// Get settings info
$database = new Database();
$db = $database->getConnection();
$allSettings = new Settings($db);

$settings = $allSettings->read();

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="./assets/img/favicon/favicon.ico" type="image/x-icon">
  <title><?php if ($settings[0]['site_name']): ?><?php echo $settings[0]['site_name']; ?><?php else: ?>Ticketing Support System<?php endif; ?></title>

  <!-- Estilos personalizados -->
  <script src="https://cdn.tailwindcss.com/"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: {
              50: "#f0f9ff",
              100: "#e0f2fe",
              200: "#bae6fd",
              300: "#7dd3fc",
              400: "#38bdf8",
              500: "#0ea5e9",
              600: "#0284c7",
              700: "#0369a1",
              800: "#075985",
              900: "#0c4a6e",
            },
            secondary: {
              50: "#fefce8",
              100: "#fef9c3",
              200: "#fef08a",
              300: "#fde047",
              400: "#facc15",
              500: "#eab308",
              600: "#ca8a04",
              700: "#a16207",
              800: "#854d0e",
              900: "#713f12",
            },
          },
          fontFamily: {
            sans: ["Roboto", "sans-serif"],
          },
        },
      },
    };
  </script>

  <!-- Font Awesome para iconos -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100">
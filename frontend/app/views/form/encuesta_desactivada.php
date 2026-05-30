<?php
$assetBase = BASE_URL . '/assets';
$sede = isset($sede) ? htmlspecialchars((string)$sede) : '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuestas Desactivadas</title>
    <link rel="stylesheet" href="<?php echo $assetBase; ?>/css/output.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50 w-full overflow-hidden">
    <main class="flex items-center justify-center w-full h-screen">
        <div class="bg-white rounded-2xl shadow-lg p-8 max-w-md text-center">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fa-solid fa-ban text-4xl text-red-500"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-3">Encuestas Desactivadas</h1>
            <?php if ($sede): ?>
                <p class="text-gray-600 mb-2">Las encuestas para <strong><?php echo $sede; ?></strong> están temporalmente desactivadas.</p>
            <?php else: ?>
                <p class="text-gray-600 mb-2">Las encuestas están temporalmente desactivadas.</p>
            <?php endif; ?>
            <p class="text-gray-500 text-sm mb-6">Intente más tarde o comuníquese con la administración.</p>
            <a href="<?php echo BASE_URL; ?>/" class="inline-block bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-lg font-medium transition">
                <i class="fa-solid fa-home mr-2"></i> Volver al inicio
            </a>
        </div>
    </main>
</body>

</html>
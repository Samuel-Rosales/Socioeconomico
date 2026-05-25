<?php
$assetBase = BASE_URL . '/assets';
$cssFile = 'output.css';
$cssDiskPath = ROOT_PATH . '/assets/css/' . $cssFile;
$cssVersion = is_file($cssDiskPath) ? (@filemtime($cssDiskPath) ?: null) : null;
$cssHref = $assetBase . '/css/' . $cssFile . ($cssVersion ? ('?v=' . $cssVersion) : '');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Formulario Socioeconómico'; ?></title>
    <link rel="icon" href="<?php echo $assetBase; ?>/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo $assetBase; ?>/favicon.ico" type="image/x-icon">
    <?php
    $themeComponentMode = 'bootstrap';
    include __DIR__ . '/../components/theme-toggle.php';
    unset($themeComponentMode);
    ?>
    <link rel="stylesheet" href="<?php echo $cssHref; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body class="bg-gray-100 min-h-screen pb-8 transition-colors duration-300">
    <?php echo $content ?? ''; ?>
</body>

</html>
<?php
$logoColorMode = isset($logoColorMode) && is_string($logoColorMode) ? $logoColorMode : 'theme';
$logoTextClass = $logoColorMode === 'white' ? 'text-white' : '';
$logoSede = '';

if (isset($sede) && is_string($sede)) {
    $logoSede = strtoupper(trim($sede));
} elseif (isset($authUser) && is_array($authUser)) {
    if (isset($authUser['instituto']) && is_array($authUser['instituto']) && !empty($authUser['instituto']['siglas']) && is_string($authUser['instituto']['siglas'])) {
        $logoSede = strtoupper(trim((string)$authUser['instituto']['siglas']));
    }
} elseif (isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user'])) {
    $sessionUser = $_SESSION['auth_user'];
    if (isset($sessionUser['instituto']) && is_array($sessionUser['instituto']) && !empty($sessionUser['instituto']['siglas']) && is_string($sessionUser['instituto']['siglas'])) {
        $logoSede = strtoupper(trim((string)$sessionUser['instituto']['siglas']));
    }
}

$isIUSF = ($logoSede === 'IUSF');
?>
<div id="Logo" class="group flex items-center justify-center gap-1 @[250px]:gap-3  w-auto h-20 ">
    <div class="flex items-center justify-center leading-none [&>svg]:w-auto [&>svg]:h-18  [&>svg]:block @[200px]:[&>svg]:h-10 @[400px]:[&>svg]:h-14 <?php echo $logoTextClass; ?>">
        <?php include ROOT_PATH . '/public/assets/svg/' . ($isIUSF ? 'IUSF.svg' : 'IUJO.svg'); ?>
    </div>
        <div class="flex items-center justify-center leading-none [&>svg]:w-auto [&>svg]:h-12 [&>svg]:block @[200px]:[&>svg]:h-16 @[400px]:[&>svg]:h-20 group-hover:animate-heartbeat origin-center will-change-transform ">
        <?php include ROOT_PATH . '/public/assets/svg/FyA-logo.svg'; ?>
    </div>
</div>
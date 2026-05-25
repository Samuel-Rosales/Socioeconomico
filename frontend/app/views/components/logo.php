<?php
$logoColorMode = isset($logoColorMode) && is_string($logoColorMode) ? $logoColorMode : 'theme';
$logoTextClass = $logoColorMode === 'white' ? 'text-white' : '';
$logoSede = isset($sede) && is_string($sede) ? strtoupper(trim($sede)) : '';
$isIUSF = $logoSede === 'IUSF';
?>
<div id="Logo" class="group flex items-center justify-center gap-1 @[250px]:gap-3  w-auto h-20 ">
    <div class="flex items-center justify-center leading-none [&>svg]:w-auto [&>svg]:h-18  [&>svg]:block @[200px]:[&>svg]:h-10 @[400px]:[&>svg]:h-14 <?php echo $logoTextClass; ?>">
        <?php include ROOT_PATH . '/public/assets/svg/' . ($isIUSF ? 'IUSF.svg' : 'IUJO.svg'); ?>
    </div>
        <div class="flex items-center justify-center leading-none [&>svg]:w-auto [&>svg]:h-12 [&>svg]:block @[200px]:[&>svg]:h-16 @[400px]:[&>svg]:h-20 group-hover:animate-heartbeat origin-center will-change-transform ">
        <?php include ROOT_PATH . '/public/assets/svg/FyA-logo.svg'; ?>
    </div>
    <div class="flex items-center justify-center leading-none [&>svg]:w-auto [&>svg]:h-10 [&>svg]:block @[200px]:[&>svg]:h-12 @[400px]:[&>svg]:h-16 <?php echo $logoTextClass; ?>">
        <?php include ROOT_PATH . '/public/assets/svg/' . ($isIUSF ? 'IUSF-letra.svg' : 'IUJO-letras.svg'); ?>
    </div>
</div>
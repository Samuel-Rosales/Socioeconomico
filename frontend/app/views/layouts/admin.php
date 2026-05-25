<?php
$assetBase = BASE_URL . '/assets';

// Cache busting: avoid browser hard-cache when output.css changes.
// Determine which CSS file exists in the current entrypoint's assets folder.
// todo esto es para que recargue el css en caso de estar en modo dev o actualizar los estilos para que
// recargen y los vuelva a cargar para tomar los nuevos cambios 
$cssCandidates = ['output.css'];
$cssFile = 'output.css';
$cssVersion = null;

$assetsDiskDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'assets';
foreach ($cssCandidates as $candidate) {
    $candidateDiskPath = $assetsDiskDir
        ? ($assetsDiskDir . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . $candidate)
        : '';
    if ($candidateDiskPath && is_file($candidateDiskPath)) {
        $cssFile = $candidate;
        $cssVersion = @filemtime($candidateDiskPath) ?: null;
        break;
    }
}

$cssHref = $assetBase . '/css/' . $cssFile;
if ($cssVersion !== null) {
    $cssHref .= '?v=' . $cssVersion;
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? htmlspecialchars($title) : 'Panel de Administración'; ?></title>
    <link rel="icon" href="<?php echo $assetBase; ?>/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo $assetBase; ?>/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo $cssHref; ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="admin-shell font-sans leading-normal tracking-normal flex h-screen overflow-hidden ">

    <?php
    $sidebarRol = isset($sidebarRol) && is_string($sidebarRol) ? $sidebarRol : null;
    $authUser = (isset($authUser) && is_array($authUser)) ? $authUser : [];
    $headerUserName = 'Administrador';
    if (!empty($authUser['nombre_completo']) && is_string($authUser['nombre_completo'])) {
        $headerUserName = trim((string)$authUser['nombre_completo']);
    } elseif (!empty($authUser['ci']) && is_string($authUser['ci'])) {
        $headerUserName = 'CI ' . trim((string)$authUser['ci']);
    }

    $headerUserMeta = '';
    if (isset($authUser['rol']) && is_array($authUser['rol']) && !empty($authUser['rol']['nombre'])) {
        $headerUserMeta = (string)$authUser['rol']['nombre'];
    } elseif (isset($authUser['rol']) && is_array($authUser['rol']) && !empty($authUser['rol']['codigo'])) {
        $headerUserMeta = (string)$authUser['rol']['codigo'];
    }

    if (isset($authUser['instituto']) && is_array($authUser['instituto'])) {
        $institutoTxt = '';
        if (!empty($authUser['instituto']['siglas']) && is_string($authUser['instituto']['siglas'])) {
            $institutoTxt = (string)$authUser['instituto']['siglas'];
        } elseif (!empty($authUser['instituto']['nombre']) && is_string($authUser['instituto']['nombre'])) {
            $institutoTxt = (string)$authUser['instituto']['nombre'];
        }

        if ($institutoTxt !== '') {
            $headerUserMeta = ($headerUserMeta !== '') ? ($headerUserMeta . ' · ' . $institutoTxt) : $institutoTxt;
        }
    }

    $isSuperAdmin = ($sidebarRol === 'SUPER_ADMIN');

    $current_page = isset($current_page) ? (string)$current_page : '';

    // Menú desplegable de reportes por vista.
    $reportesMenuItems = [
        [
            'key' => 'reportes_dashboard_general',
            'label' => 'Resumen General',
            'href' => BASE_URL . '/admin/reportes/dashboard-general',
        ],
        [
            'key' => 'reportes_analisis_academico',
            'label' => 'Análisis Académico',
            'href' => BASE_URL . '/admin/reportes/analisis-academico',
        ],
        [
            'key' => 'reportes_demografico_vulnerabilidad',
            'label' => 'Perfil Social',
            'href' => BASE_URL . '/admin/reportes/demografico-vulnerabilidad',
        ],
    ];

    $isReportesSection = ($current_page === 'reportes' || strpos($current_page, 'reportes_') === 0);
    ?>
    <!-- Sidebar -->
    <aside id="mobile-sidebar" class="bg-white w-64 h-screen text-gray-800 hidden md:grid md:grid-rows-[auto_1fr_auto] fixed inset-y-0 left-0 z-999 md:z-30 overflow-y-auto">
        <a class="flex border-b justify-center h-24 w-full px-1.5 items-center @container"  href="<?php echo BASE_URL; ?>/">
            <?php include APP_PATH . '/views/components/logo.php'; ?>
        </a>
        <nav class="p-4 space-y-2">
            <a href="<?php echo BASE_URL; ?>/admin" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 <?php echo ($current_page === 'dashboard') ? 'bg-primary2-50 text-primary2-600 font-medium ' : 'hover:text-gray-800 hover:bg-gray-100 '; ?>">
                <i class="fas fa-home w-5 text-center"></i> Panel Principal
            </a>
            <div class="space-y-1" data-dropdown data-open="<?php echo $isReportesSection ? '1' : '0'; ?>">
                <button
                    type="button"
                    class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-lg transition-colors duration-200 <?php echo $isReportesSection ? 'bg-primary2-50 text-primary2-600 font-medium' : 'text-gray-700 hover:bg-gray-100'; ?>"
                    aria-expanded="<?php echo $isReportesSection ? 'true' : 'false'; ?>"
                    data-dropdown-btn>
                    <span class="flex items-center gap-3">
                        <i class="fas fa-chart-pie w-5 text-center"></i>
                        <span>Reportes</span>
                    </span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200 <?php echo $isReportesSection ? 'rotate-180' : ''; ?> text-gray-500 " data-dropdown-icon></i>
                </button>

                <div class="pl-6" data-dropdown-menu>
                    <div class="space-y-1">
                        <?php foreach ($reportesMenuItems as $item):
                            $itemKey = isset($item['key']) ? (string)$item['key'] : '';
                            $itemHref = isset($item['href']) ? (string)$item['href'] : '#';
                            $itemLabel = isset($item['label']) ? (string)$item['label'] : 'Vista';
                            $isActive = ($current_page === $itemKey);
                        ?>
                            <a
                                href="<?php echo htmlspecialchars($itemHref); ?>"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-colors duration-200 <?php echo $isActive ? 'bg-primary2-50 text-primary2-600 font-medium ' : 'text-gray-700 hover:bg-gray-100 '; ?>">
                                <span class="w-5 text-center">•</span>
                                <span><?php echo htmlspecialchars($itemLabel); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php if ($isSuperAdmin): ?>
                <a href="<?php echo BASE_URL; ?>/admin/usuarios" class="flex items-center gap-3 px-4 py-3 rounded-lg <?php echo ($current_page === 'users') ? 'bg-primary2-50 text-primary2-600 font-medium ' : 'text-gray-700 hover:bg-gray-100 '; ?>">
                    <i class="fas fa-users w-5 text-center"></i> Usuarios
                </a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>/admin/respuestas" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 <?php echo ($current_page === 'responses') ? 'bg-primary2-50 text-primary2-600 font-medium ' : 'text-gray-700 hover:bg-gray-100 '; ?>">
                <i class="fas fa-file-alt w-5 text-center"></i> Respuestas
            </a>
            <?php if ($isSuperAdmin): ?>
                <a href="<?php echo BASE_URL; ?>/admin/catalogos" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 <?php echo ($current_page === 'catalogs') ? 'bg-primary2-50 text-primary2-600 font-medium ' : 'text-gray-700 hover:bg-gray-100  '; ?>">
                    <i class="fas fa-list w-5 text-center"></i> Configuración
                </a>
            <?php endif; ?>
        </nav>
        <!-- Form para Logout -->
        <form action="<?php echo BASE_URL; ?>/logout" method="POST" class="p-4 text-sm border-t mt-2">
            <button type="submit" class="text-red-500 hover:text-red-700 font-medium flex items-center gap-2 w-full">
                <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
            </button>
        </form>
    </aside>

    <!-- Mobile Sidebar Backdrop -->
    <div id="mobile-sidebar-backdrop" class="fixed z-40 inset-0 bg-black/40 hidden md:hidden"></div>

    <!-- Main Content wrapper -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden transition-all duration-300 md:ml-64">
        <!-- Top Navbar -->
        <header class="bg-white shadow-sm flex items-center justify-between px-8 py-6 sticky top-0 z-20 shrink-0 transition-colors duration-300 h-24 border-b">
            <div class="flex items-center h-10">
                <button id="mobile-menu-btn" class="md:hidden text-gray-500 hover:text-gray-700 focus:outline-none" aria-controls="mobile-sidebar" aria-expanded="false">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <h2 class="text-xl font-semibold text-gray-800 ml-4 md:ml-0">
                    <?php
                    $titles = [
                        'dashboard' => 'Panel Principal',
                        'reportes' => 'Reportes',
                        'reportes_dashboard_general' => 'Reportes · Resumen General',
                        'reportes_analisis_academico' => 'Reportes · Análisis Académico',
                        'reportes_demografico_vulnerabilidad' => 'Reportes · Perfil Socioeconómico por Carreras',
                        'users' => 'Gestión de Usuarios',
                        'responses' => 'Respuestas Recibidas',
                        'catalogs' => 'Configuración de Opciones para las Encuestas'
                    ];
                    echo isset($titles[$current_page]) ? $titles[$current_page] : 'Administración';
                    ?>
                </h2>
            </div>

            <div class="flex items-center gap-4">

                <div class="text-right leading-tight max-w-56">
                    <div class="text-sm font-semibold text-gray-700 truncate " title="<?php echo htmlspecialchars((string)$headerUserName); ?>">
                        <?php echo htmlspecialchars((string)$headerUserName); ?>
                    </div>
                    <?php if ($headerUserMeta !== ''): ?>
                        <div class="text-xs text-gray-500 truncate " title="<?php echo htmlspecialchars((string)$headerUserMeta); ?>">
                            <?php echo htmlspecialchars((string)$headerUserMeta); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
                $themeToggleId = 'themeToggleAdmin';
                $themeToggleAriaLabel = 'Cambiar entre modo claro y oscuro';
                include __DIR__ . '/../components/theme-toggle.php';
                unset($themeToggleId, $themeToggleAriaLabel);
                ?>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 grid grid-rows-[1fr_auto] bg-gray-100 overflow-y-auto">
            <div class="p-6">
                <!-- Renderiza la vista específica -->
                <?php echo $content ?? ''; ?>
            </div>

            <!-- Footer -->
            <!-- <footer class="bg-white border-t py-4 text-center text-sm text-gray-500 transition-colors duration-300 ">
                &copy; <?php echo date('Y'); ?> IUJO - Sistema de Administración Socioeconómico. Todos los derechos reservados.
            </footer> -->
        </main>
    </main>

    <script>
        (function() {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileSidebar = document.getElementById('mobile-sidebar');
            const mobileBackdrop = document.getElementById('mobile-sidebar-backdrop');

            const isMobile = () => window.innerWidth < 768;

            const closeMobileSidebar = () => {
                if (!mobileSidebar || !mobileBackdrop) return;
                mobileSidebar.classList.add('hidden');
                mobileBackdrop.classList.add('hidden');
                if (mobileMenuBtn) {
                    mobileMenuBtn.setAttribute('aria-expanded', 'false');
                }
            };

            const openMobileSidebar = () => {
                if (!mobileSidebar || !mobileBackdrop) return;
                mobileSidebar.classList.remove('hidden');
                mobileBackdrop.classList.remove('hidden');
                if (mobileMenuBtn) {
                    mobileMenuBtn.setAttribute('aria-expanded', 'true');
                }
            };

            if (mobileMenuBtn && mobileSidebar && mobileBackdrop) {
                mobileMenuBtn.addEventListener('click', () => {
                    if (!isMobile()) return;
                    const isHidden = mobileSidebar.classList.contains('hidden');
                    if (isHidden) {
                        openMobileSidebar();
                    } else {
                        closeMobileSidebar();
                    }
                });

                mobileBackdrop.addEventListener('click', closeMobileSidebar);

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeMobileSidebar();
                    }
                });

                const sidebarLinks = mobileSidebar.querySelectorAll('a[href]');
                sidebarLinks.forEach((link) => {
                    link.addEventListener('click', () => {
                        if (isMobile()) {
                            closeMobileSidebar();
                        }
                    });
                });

                window.addEventListener('resize', () => {
                    if (!isMobile()) {
                        closeMobileSidebar();
                    }
                });
            }

            const dropdowns = document.querySelectorAll('[data-dropdown]');
            dropdowns.forEach((root) => {
                const btn = root.querySelector('[data-dropdown-btn]');
                const menu = root.querySelector('[data-dropdown-menu]');
                const icon = root.querySelector('[data-dropdown-icon]');
                if (!btn || !menu) return;

                const setOpen = (open) => {
                    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                    menu.classList.toggle('hidden', !open);
                    if (icon) icon.classList.toggle('rotate-180', open);
                    root.setAttribute('data-open', open ? '1' : '0');
                };

                const initialOpen = root.getAttribute('data-open') === '1';
                setOpen(initialOpen);

                btn.addEventListener('click', () => {
                    const isOpen = root.getAttribute('data-open') === '1';
                    setOpen(!isOpen);
                });
            });

            const INACTIVITY_LIMIT = <?php echo (int)SESSION_TIMEOUT; ?>;
            const WARNING_BEFORE = <?php echo (int)SESSION_WARNING_BEFORE; ?>;
            const HEARTBEAT_INTERVAL = 60;
            let inactivityTimer = null;
            let warningTimer = null;
            let countdownInterval = null;
            let toastEl = null;
            let lastHeartbeat = 0;
            const HEARTBEAT_THROTTLE = 5;

            const BASE_URL = <?php echo json_encode(BASE_URL); ?>;

            const resetInactivityTimer = () => {
                clearTimeout(inactivityTimer);
                clearTimeout(warningTimer);
                clearInterval(countdownInterval);
                hideWarningToast();

                warningTimer = setTimeout(() => {
                    showWarningToast(INACTIVITY_LIMIT);
                }, (INACTIVITY_LIMIT - WARNING_BEFORE) * 1000);

                inactivityTimer = setTimeout(() => {
                    invalidateAndLogout();
                }, INACTIVITY_LIMIT * 1000);
            };

            const showWarningToast = () => {
                hideWarningToast();
                toastEl = document.createElement('div');
                toastEl.id = 'inactivity-toast';
                toastEl.className = 'fixed bottom-6 right-6 bg-white border border-amber-400 rounded-xl shadow-2xl p-4 max-w-sm z-50 flex items-start gap-3';
                toastEl.style.cssText = 'opacity: 1; background-color: #fffbeb;';
                toastEl.innerHTML = `
                    <div class="flex-shrink-0 w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-amber-600"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-amber-900 mb-1">Tu sesión expirará pronto</p>
                        <p class="text-xs text-amber-700">Sin actividad en <span id="inactivity-countdown" class="font-bold">${WARNING_BEFORE}</span> segundos, serás desconectado.</p>
                    </div>
                    <button type="button" id="inactivity-dismiss" class="flex-shrink-0 text-amber-400 hover:text-amber-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                document.body.appendChild(toastEl);

                let remaining = WARNING_BEFORE;
                countdownInterval = setInterval(() => {
                    remaining--;
                    const countdownEl = document.getElementById('inactivity-countdown');
                    if (countdownEl) {
                        countdownEl.textContent = remaining;
                    }
                    if (remaining <= 0) {
                        clearInterval(countdownInterval);
                    }
                }, 1000);

                document.getElementById('inactivity-dismiss').addEventListener('click', () => {
                    resetInactivityTimer();
                });

                toastEl.addEventListener('click', (e) => {
                    if (e.target === toastEl || !toastEl.contains(e.target)) {
                        resetInactivityTimer();
                    }
                });
            };

            const hideWarningToast = () => {
                clearInterval(countdownInterval);
                if (toastEl) {
                    toastEl.remove();
                    toastEl = null;
                }
            };

            const invalidateAndLogout = () => {
                hideWarningToast();
                fetch(BASE_URL + '/logout/invalidate', {
                    method: 'POST',
                    credentials: 'same-origin'
                }).then(() => {
                    window.location.href = BASE_URL + '/login';
                }).catch(() => {
                    window.location.href = BASE_URL + '/login';
                });
            };

            const updateLastActivity = () => {
                fetch(BASE_URL + '/admin/heartbeat', {
                    method: 'POST',
                    credentials: 'same-origin'
                }).catch(() => {});
                resetInactivityTimer();
            };

            const throttledHeartbeat = () => {
                const now = Date.now();
                if (now - lastHeartbeat >= HEARTBEAT_THROTTLE * 1000) {
                    lastHeartbeat = now;
                    fetch(BASE_URL + '/admin/heartbeat', {
                        method: 'POST',
                        credentials: 'same-origin'
                    }).catch(() => {});
                }
            };

            ['click', 'scroll', 'keypress', 'touchstart'].forEach(event => {
                document.addEventListener(event, updateLastActivity, { passive: true });
            });

            document.addEventListener('mousemove', throttledHeartbeat, { passive: true });

            setInterval(() => {
                fetch(BASE_URL + '/admin/heartbeat', {
                    method: 'POST',
                    credentials: 'same-origin'
                }).catch(() => {});
            }, HEARTBEAT_INTERVAL * 1000);

            resetInactivityTimer();
        })();
    </script>

</body>

</html>

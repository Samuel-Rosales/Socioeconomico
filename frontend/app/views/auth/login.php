<canvas id="bg-canvas"></canvas>

<?php
    $oldUser = isset($old['usuario']) ? htmlspecialchars($old['usuario']) : '';
?>

<main class="relative min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 py-8 overflow-hidden">

    <div class="w-full max-w-120 rounded-xl bg-white dark:bg-slate-800 dark:text-slate-100 shadow-md p-8 sm:p-10 relative z-10">
        <div class="mb-8">
            <a class="flex justify-center h-28 w-full items-center @container" href="<?php echo BASE_URL; ?>/">
                <?php include APP_PATH . '/views/components/logo.php'; ?>
            </a>
            <h1 class="text-4xl leading-tight text-slate-700 dark:text-slate-100 font-medium mb-2">Login Socio Economico </h1>
            <p class="text-slate-500 dark:text-slate-300 text-lg">Por favor, coloca tu usuario y contraseña</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/login" method="POST" class="space-y-5" novalidate>
            <div>
                <label for="usuario" class="label-field">Usuario</label>
                <input
                    id="usuario"
                    name="usuario"
                    type="text"
                    class="input-field focus:ring-indigo-500"
                    placeholder="Usuario"
                    value="<?php echo htmlspecialchars($oldUser); ?>"
                    autocomplete="username"
                    required>
            </div>

            <div>
                <label for="contrasena" class="label-field">Contraseña</label>
                <div class="relative">
                    <input
                        id="contrasena"
                        name="contrasena"
                        type="password"
                        class="input-field pr-12 focus:ring-indigo-500"
                        placeholder="********"
                        autocomplete="current-password"
                        required>
                    <button
                        id="togglePassword"
                        type="button"
                        class="absolute inset-y-0 right-0 px-4 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
                        aria-label="Mostrar u ocultar contraseña">
                        <svg id="eyeOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg id="eyeClosed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5 hidden">
                            <path d="m1 1 22 22"></path>
                            <path d="M10.58 10.58a2 2 0 0 0 2.83 2.83"></path>
                            <path d="M9.88 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a17.33 17.33 0 0 1-4.27 5.49"></path>
                            <path d="M6.61 6.61A17.39 17.39 0 0 0 1 12s4 8 11 8a10.94 10.94 0 0 0 5.35-1.42"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full rounded-md bg-indigo-500 dark:bg-indigo-600 hover:bg-indigo-600 text-white text-xl font-semibold py-2.5 transition duration-200 shadow-sm">
                Login
            </button>
        </form>
    </div>
</main>

<script>
    (function() {
        const passwordInput = document.getElementById('contrasena');
        const toggleButton = document.getElementById('togglePassword');
        const eyeOpen = document.getElementById('eyeOpen');
        const eyeClosed = document.getElementById('eyeClosed');

        if (!passwordInput || !toggleButton || !eyeOpen || !eyeClosed) {
            return;
        }

        toggleButton.addEventListener('click', function() {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            eyeOpen.classList.toggle('hidden', isPassword);
            eyeClosed.classList.toggle('hidden', !isPassword);
        });
    })();
</script>

<script src="<?php echo BASE_URL; ?>/assets/js/login-particles.js"></script>
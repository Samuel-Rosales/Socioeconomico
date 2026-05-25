<?php
$themeComponentMode = isset($themeComponentMode) && is_string($themeComponentMode)
    ? $themeComponentMode
    : 'toggle';

if ($themeComponentMode === 'bootstrap'):
?>
<script>
(function () {
    const storageKey = 'socioeconomico-theme';
    const root = document.documentElement;
    const savedTheme = localStorage.getItem(storageKey);
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const hasValidSavedTheme = savedTheme === 'dark' || savedTheme === 'light';
    const theme = hasValidSavedTheme ? savedTheme : (prefersDark ? 'dark' : 'light');
    root.classList.toggle('dark', theme === 'dark');
    root.dataset.theme = theme;
}());
</script>
<?php
return;
endif;

$toggleId = isset($themeToggleId) && is_string($themeToggleId) && $themeToggleId !== ''
    ? $themeToggleId
    : 'themeToggle';

$toggleAriaLabel = isset($themeToggleAriaLabel) && is_string($themeToggleAriaLabel) && $themeToggleAriaLabel !== ''
    ? $themeToggleAriaLabel
    : 'Cambiar entre modo claro y oscuro';
?>
<label class="inline-flex items-center relative cursor-pointer shrink-0" for="<?php echo htmlspecialchars($toggleId); ?>" aria-label="<?php echo htmlspecialchars($toggleAriaLabel); ?>">
    <input class="peer hidden" id="<?php echo htmlspecialchars($toggleId); ?>" data-theme-toggle type="checkbox" />
    <div class="relative w-13 h-7 bg-slate-200 peer-checked:bg-zinc-500 rounded-full after:absolute after:content-[''] after:w-5.5 after:h-5.5 after:bg-gradient-to-r after:from-orange-500 after:to-yellow-400 peer-checked:after:from-zinc-900 peer-checked:after:to-zinc-900 after:rounded-full after:top-0.75 after:left-0.75 active:after:w-7 peer-checked:after:left-12.25 peer-checked:after:translate-x-[-100%] shadow-sm duration-300 after:duration-300 after:shadow-md peer-focus:after:ring-2 peer-focus:after:ring-orange-400"></div>
    <svg xmlns="http://www.w3.org/2000/svg" width="100" height="0" class="fill-white peer-checked:opacity-60 absolute w-3.5 h-3.5 left-1.5" data-name="Layer 1" viewBox="0 0 24 24"><path d="M12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5m1-17h-2v5h2zm0 19h-2v5h2zm-8-8H0v2h5zm19 0h-5v2h5zm-2.81-6.78-1.41-1.41-3.54 3.54 1.41 1.41zM7.76 17.66l-1.41-1.41-3.54 3.54 1.41 1.41zm0-11.31L4.22 2.81 2.81 4.22l3.54 3.54zM21.2 19.79l-3.54-3.54-1.41 1.41 3.54 3.54z"/></svg>
    <svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" class="fill-black opacity-60 peer-checked:opacity-70 peer-checked:fill-white absolute w-3.5 h-3.5 right-1.5" data-name="Layer 1" viewBox="0 0 24 24"><path d="M12.009 24A12.067 12.067 0 0 1 .075 10.725 12.12 12.12 0 0 1 10.1.152a13 13 0 0 1 5.03.206 2.5 2.5 0 0 1 1.8 1.8 2.47 2.47 0 0 1-.7 2.425c-4.559 4.168-4.165 10.645.807 14.412a2.5 2.5 0 0 1-.7 4.319 13.9 13.9 0 0 1-4.328.686m.074-22a11 11 0 0 0-1.675.127 10.1 10.1 0 0 0-8.344 8.8A9.93 9.93 0 0 0 4.581 18.7a10.47 10.47 0 0 0 11.093 2.734.5.5 0 0 0 .138-.856C9.883 16.1 9.417 8.087 14.865 3.124a.46.46 0 0 0 .127-.465.49.49 0 0 0-.356-.362A10.7 10.7 0 0 0 12.083 2M20.5 12a1 1 0 0 1-.97-.757l-.358-1.43-1.432-.385a1 1 0 0 1 .035-1.94l1.4-.325.351-1.406a1 1 0 0 1 1.94 0l.355 1.418 1.418.355a1 1 0 0 1 0 1.94l-1.418.355-.355 1.418A1 1 0 0 1 20.5 12M16 14a1 1 0 0 0 2 0 1 1 0 0 0-2 0m6 4a1 1 0 0 0 2 0 1 1 0 0 0-2 0"/></svg>
</label>

<?php if (empty($GLOBALS['__theme_toggle_logic_rendered'])): ?>
<?php $GLOBALS['__theme_toggle_logic_rendered'] = true; ?>
<script>
(function () {
    const storageKey = 'socioeconomico-theme';
    const root = document.documentElement;
    const toggles = document.querySelectorAll('[data-theme-toggle]');
    const systemThemeQuery = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

    const hasValidSavedTheme = () => {
        const savedTheme = localStorage.getItem(storageKey);
        return savedTheme === 'dark' || savedTheme === 'light';
    };

    const getInitialTheme = () => {
        const savedTheme = localStorage.getItem(storageKey);
        if (savedTheme === 'dark' || savedTheme === 'light') {
            return savedTheme;
        }
        return systemThemeQuery && systemThemeQuery.matches ? 'dark' : 'light';
    };

    const applyTheme = (theme, persist) => {
        const isDark = theme === 'dark';
        root.classList.toggle('dark', isDark);
        root.dataset.theme = theme;

        toggles.forEach((toggle) => {
            toggle.checked = isDark;
        });

        if (persist) {
            localStorage.setItem(storageKey, theme);
        }
    };

    applyTheme(getInitialTheme(), false);

    toggles.forEach((toggle) => {
        toggle.addEventListener('change', function () {
            applyTheme(this.checked ? 'dark' : 'light', true);
        });
    });

    window.addEventListener('storage', (event) => {
        if (event.key === storageKey && event.newValue) {
            applyTheme(event.newValue, false);
        }
    });

    if (!hasValidSavedTheme() && systemThemeQuery) {
        const syncWithSystemTheme = (event) => {
            applyTheme(event.matches ? 'dark' : 'light', false);
        };

        if (typeof systemThemeQuery.addEventListener === 'function') {
            systemThemeQuery.addEventListener('change', syncWithSystemTheme);
        } else if (typeof systemThemeQuery.addListener === 'function') {
            systemThemeQuery.addListener(syncWithSystemTheme);
        }
    }
}());
</script>
<?php endif; ?>
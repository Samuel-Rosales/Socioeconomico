  <?php
  $estadoEncuestas = isset($estadoEncuestas) && is_array($estadoEncuestas) ? $estadoEncuestas : [];
  $sedes = isset($sedes) && is_array($sedes) ? $sedes : [];

  $getEstado = function($sede) use ($estadoEncuestas) {
      $sigla = isset($sede['sigla_db']) ? $sede['sigla_db'] : $sede['id'];
      $key = strtolower($sigla);
      return !isset($estadoEncuestas[$key]) || $estadoEncuestas[$key] === true;
  };

  $sedeEstados = [];
  foreach ($sedes as $s) {
      $sedeEstados[$s['id']] = $getEstado($s);
  }
  ?>

   <aside class=" absolute right-0 top-0 flex z-50 p-4 gap-4 ">
        <div class="flex justify-center items-center gap-4">
           <a href="admin">
               <i class="fa-solid text-xl fa-user-shield"></i>
           </a>
           <?php include __DIR__ . '/../components/theme-toggle.php'; ?>
       </div>
   </aside>

   <main class=" relative px-4 py-12 min-h-[80vh] r-0 l-0 mx-auto flex flex-col">

       <section class="text-center mb-6">
           <h1 class="text-4xl md:text-5xl font-extrabold text-gray-800 mb-4 tracking-tight">Seleccione su Instituto</h1>
           <p class="text-xl text-gray-600 max-w-2xl mx-auto">Bienvenido al sistema de registro socioeconómico. Por favor, seleccione la sede a la que pertenece para continuar.</p>
       </section>

       <section class="grid grid-cols-1 grid-rows-1 lg:grid-cols-3 lg:grid-rows-3 gap-6 py-6 px-2 md:px-6 max-w-450 w-full left-0 right-0 mx-auto  justify-center ">

           <?php $s = $sedes[0]; $id = $s['id']; $activa = $sedeEstados[$id]; ?>
           <?php if ($activa): ?>
           <a href="<?php echo BASE_URL; ?>/<?php echo $id; ?>/formulario" class=" block style-card group lg:col-span-2 card-grit-home relative overflow-hidden min-h-75 @container">
               <div class="container-logo">
                   <img src="<?php echo BASE_URL; ?>/assets/img/iujo-barquisimeto2.jpg" alt="IUJO Barquisimeto" class="@[930px]:w-1/2 w-full  h-full object-cover @[930px]:object-top transition-all duration-500">
                   <img src="<?php echo BASE_URL; ?>/assets/img/IUJO-Barquisimeto-1024x1024.jpg" alt="IUJO Barquisimeto" class="w-0 hidden  @[710px]:block @[930px]:w-1/2 h-full object-cover transition-all duration-500">
                   <span class="style-overlay absolute inset-0" style="background: linear-gradient(to right, rgba(11, 17, 32, 0.70), rgba(18, 27, 46, 0.50));"></span>
               </div>
               <div class="relative z-10 flex flex-col h-full w-full items-center">
                   <div class=" absolute top-4 left-4">
                       <?php $logoColorMode = 'white'; ?>
                       <?php include APP_PATH . '/views/components/logo.php'; ?>
                       <?php unset($logoColorMode); ?>
                   </div>
                   <span class="mt-auto self-end rounded-full bg-black/45 px-3 py-1 text-xl font-semibold tracking-wide text-white backdrop-blur-sm shadow-lg">IUJO Barquisimeto
                   </span>
               </div>
           </a>
           <?php else: ?>
             <div data-card-desactivada data-nombre="IUJO Barquisimeto" class="block style-card group lg:col-span-2 card-grit-home relative overflow-hidden min-h-75 @container cursor-pointer hover:scale-[1.02] transition-transform duration-200">
                <div class="container-logo">
                    <img src="<?php echo BASE_URL; ?>/assets/img/iujo-barquisimeto2.jpg" alt="IUJO Barquisimeto" class="@[930px]:w-1/2 w-full h-full object-cover @[930px]:object-top transition-all duration-500 grayscale">
                    <img src="<?php echo BASE_URL; ?>/assets/img/IUJO-Barquisimeto-1024x1024.jpg" alt="IUJO Barquisimeto" class="w-0 hidden @[710px]:block @[930px]:w-1/2 h-full object-cover transition-all duration-500 grayscale">
                    <span class="style-overlay absolute inset-0" style="background: linear-gradient(to right, rgba(11, 17, 32, 0.70), rgba(18, 27, 46, 0.50));"></span>
                </div>
                <div class="relative z-10 flex flex-col h-full w-full items-center">
                    <div class=" absolute top-4 left-4">
                        <?php $logoColorMode = 'white'; ?>
                        <?php include APP_PATH . '/views/components/logo.php'; ?>
                        <?php unset($logoColorMode); ?>
                    </div>
                    <span class="mt-auto self-end rounded-full bg-black/45 px-3 py-1 text-xl font-semibold tracking-wide text-white backdrop-blur-sm shadow-lg">IUJO Barquisimeto</span>
                </div>
                <div class="absolute inset-0 z-20 bg-red-900/20 flex flex-col items-center justify-center gap-2">
                    <i class="fa-solid fa-ban text-4xl text-white drop-shadow-md"></i>
                    <span class="text-white text-lg font-semibold drop-shadow-md">Encuestas desactivadas</span>
                    <span class="text-white/80 text-sm drop-shadow">Temporalmente no disponible</span>
                </div>
            </div>
           <?php endif; ?>

           <?php $s = $sedes[1]; $id = $s['id']; $activa = $sedeEstados[$id]; ?>
           <?php if ($activa): ?>
           <a href="<?php echo BASE_URL; ?>/<?php echo $id; ?>/formulario" class="style-card block group card-grit-home lg:row-span-2 relative overflow-hidden min-h-75 @container">
               <div class="container-logo">
                   <img src="<?php echo BASE_URL; ?>/assets/img/iujo-petare2-2024.jpg" alt="IUJO Petare" class="w-full h-full object-cover ">
                   <img src="<?php echo BASE_URL; ?>/assets/img/iujo-petare2-2024.jpg" alt="IUJO Petare" class="block @[500px]:hidden w-full h-full object-cover">
                   <span class="style-overlay absolute inset-0" style="background: linear-gradient(to right, rgba(11, 17, 32, 0.70), rgba(18, 27, 46, 0.50));"></span>
               </div>
               <div class="relative z-10 flex flex-col h-full w-full items-center">
                   <div class=" absolute top-4 left-4">
                       <?php $logoColorMode = 'white'; ?>
                       <?php include APP_PATH . '/views/components/logo.php'; ?>
                       <?php unset($logoColorMode); ?>
                   </div>
                   <h2 class=" mt-auto self-end rounded-full bg-black/45 px-3 py-1 text-xl font-semibold tracking-wide text-white backdrop-blur-sm shadow-lg">IUJO Petare</h2>
               </div>
           </a>
            <?php else: ?>
             <div data-card-desactivada data-nombre="IUJO Petare" class="style-card block group card-grit-home lg:row-span-2 relative overflow-hidden min-h-75 @container cursor-pointer hover:scale-[1.02] transition-transform duration-200">
                 <div class="container-logo">
                     <img src="<?php echo BASE_URL; ?>/assets/img/iujo-petare2-2024.jpg" alt="IUJO Petare" class="w-full h-full object-cover grayscale">
                     <img src="<?php echo BASE_URL; ?>/assets/img/iujo-petare2-2024.jpg" alt="IUJO Petare" class="block @[500px]:hidden w-full h-full object-cover grayscale">
                    <span class="style-overlay absolute inset-0" style="background: linear-gradient(to right, rgba(11, 17, 32, 0.70), rgba(18, 27, 46, 0.50));"></span>
                </div>
                <div class="relative z-10 flex flex-col h-full w-full items-center">
                    <div class=" absolute top-4 left-4">
                        <?php $logoColorMode = 'white'; ?>
                        <?php include APP_PATH . '/views/components/logo.php'; ?>
                        <?php unset($logoColorMode); ?>
                    </div>
                    <h2 class=" mt-auto self-end rounded-full bg-black/45 px-3 py-1 text-xl font-semibold tracking-wide text-white backdrop-blur-sm shadow-lg">IUJO Petare</h2>
                </div>
                <div class="absolute inset-0 z-20 bg-red-900/20 flex flex-col items-center justify-center gap-2">
                    <i class="fa-solid fa-ban text-4xl text-white drop-shadow-md"></i>
                    <span class="text-white text-lg font-semibold drop-shadow-md">Encuestas desactivadas</span>
                    <span class="text-white/80 text-sm drop-shadow">Temporalmente no disponible</span>
                </div>
            </div>
           <?php endif; ?>

           <?php $s = $sedes[2]; $id = $s['id']; $activa = $sedeEstados[$id]; ?>
           <?php if ($activa): ?>
           <a href="<?php echo BASE_URL; ?>/<?php echo $id; ?>/formulario" class="style-card block group card-grit-home lg:row-span-2 relative overflow-hidden min-h-75 @container">
               <div class="container-logo">
                   <img src="<?php echo BASE_URL; ?>/assets/img/IUJO-Catia-768x768.jpg" alt="IUJO catia" class="w-full h-full object-cover ">
                   <img src="<?php echo BASE_URL; ?>/assets/img/ITJO-2.jpeg" alt="IUJO catia" class="block @[500px]:hidden w-full h-full object-cover">
                   <span class="style-overlay absolute inset-0" style="background: linear-gradient(to right, rgba(11, 17, 32, 0.70), rgba(18, 27, 46, 0.50));"></span>
               </div>

               <div class="relative z-10 flex flex-col h-full w-full items-center">
                   <div class=" absolute top-4 left-4">
                       <?php $logoColorMode = 'white'; ?>
                       <?php include APP_PATH . '/views/components/logo.php'; ?>
                       <?php unset($logoColorMode); ?>
                   </div>
                   <h2 class=" mt-auto self-end rounded-full bg-black/45 px-3 py-1 text-xl font-semibold tracking-wide text-white backdrop-blur-sm shadow-lg">IUJO Catia</h2>
               </div>
           </a>
            <?php else: ?>
             <div data-card-desactivada data-nombre="IUJO Catia" class="style-card block group card-grit-home lg:row-span-2 relative overflow-hidden min-h-75 @container cursor-pointer hover:scale-[1.02] transition-transform duration-200">
                 <div class="container-logo">
                     <img src="<?php echo BASE_URL; ?>/assets/img/IUJO-Catia-768x768.jpg" alt="IUJO catia" class="w-full h-full object-cover grayscale">
                     <img src="<?php echo BASE_URL; ?>/assets/img/ITJO-2.jpeg" alt="IUJO catia" class="block @[500px]:hidden w-full h-full object-cover grayscale">
                    <span class="style-overlay absolute inset-0" style="background: linear-gradient(to right, rgba(11, 17, 32, 0.70), rgba(18, 27, 46, 0.50));"></span>
                </div>
                <div class="relative z-10 flex flex-col h-full w-full items-center">
                    <div class=" absolute top-4 left-4">
                        <?php $logoColorMode = 'white'; ?>
                        <?php include APP_PATH . '/views/components/logo.php'; ?>
                        <?php unset($logoColorMode); ?>
                    </div>
                    <h2 class=" mt-auto self-end rounded-full bg-black/45 px-3 py-1 text-xl font-semibold tracking-wide text-white backdrop-blur-sm shadow-lg">IUJO Catia</h2>
                </div>
                <div class="absolute inset-0 z-20 bg-red-900/20 flex flex-col items-center justify-center gap-2">
                    <i class="fa-solid fa-ban text-4xl text-white drop-shadow-md"></i>
                    <span class="text-white text-lg font-semibold drop-shadow-md">Encuestas desactivadas</span>
                    <span class="text-white/80 text-sm drop-shadow">Temporalmente no disponible</span>
                </div>
            </div>
           <?php endif; ?>

           <?php $s = $sedes[3]; $id = $s['id']; $activa = $sedeEstados[$id]; ?>
           <?php if ($activa): ?>
           <a href="<?php echo BASE_URL; ?>/<?php echo $id; ?>/formulario" class=" block style-card group card-grit-home relative overflow-hidden min-h-75 @container">
               <div class="container-logo">
                   <img src="<?php echo BASE_URL; ?>/assets/img/IUJO-Guanarito.jpg" alt="IUJO Guanarito" class="w-full h-full object-cover">
                   <span class="style-overlay absolute inset-0" style="background: linear-gradient(to right, rgba(11, 17, 32, 0.70), rgba(18, 27, 46, 0.50));"></span>
               </div>
               <div class="relative z-10 flex flex-col h-full w-full items-center">
                   <div class=" absolute top-4 left-4">
                       <?php $logoColorMode = 'white'; ?>
                       <?php include APP_PATH . '/views/components/logo.php'; ?>
                       <?php unset($logoColorMode); ?>
                   </div>
                   <h2 class=" mt-auto self-end rounded-full bg-black/45 px-3 py-1 text-xl font-semibold tracking-wide text-white backdrop-blur-sm shadow-lg">IUJO Guanarito</h2>
               </div>
           </a>
            <?php else: ?>
             <div data-card-desactivada data-nombre="IUJO Guanarito" class="block style-card group card-grit-home relative overflow-hidden min-h-75 @container cursor-pointer hover:scale-[1.02] transition-transform duration-200">
                 <div class="container-logo">
                     <img src="<?php echo BASE_URL; ?>/assets/img/IUJO-Guanarito.jpg" alt="IUJO Guanarito" class="w-full h-full object-cover grayscale">
                    <span class="style-overlay absolute inset-0" style="background: linear-gradient(to right, rgba(11, 17, 32, 0.70), rgba(18, 27, 46, 0.50));"></span>
                </div>
                <div class="relative z-10 flex flex-col h-full w-full items-center">
                    <div class=" absolute top-4 left-4">
                        <?php $logoColorMode = 'white'; ?>
                        <?php include APP_PATH . '/views/components/logo.php'; ?>
                        <?php unset($logoColorMode); ?>
                    </div>
                    <h2 class=" mt-auto self-end rounded-full bg-black/45 px-3 py-1 text-xl font-semibold tracking-wide text-white backdrop-blur-sm shadow-lg">IUJO Guanarito</h2>
                </div>
                <div class="absolute inset-0 z-20 bg-red-900/20 flex flex-col items-center justify-center gap-2">
                    <i class="fa-solid fa-ban text-4xl text-white drop-shadow-md"></i>
                    <span class="text-white text-lg font-semibold drop-shadow-md">Encuestas desactivadas</span>
                    <span class="text-white/80 text-sm drop-shadow">Temporalmente no disponible</span>
                </div>
            </div>
           <?php endif; ?>

           <?php $s = $sedes[4]; $id = $s['id']; $activa = $sedeEstados[$id]; ?>
           <?php if ($activa): ?>
           <a href="<?php echo BASE_URL; ?>/<?php echo $id; ?>/formulario" class=" block style-card group lg:col-span-2 card-grit-home relative overflow-hidden min-h-75 @container">
               <div class="container-logo">
                   <img src="<?php echo BASE_URL; ?>/assets/img/IUSF-1024x1024.jpg" alt="IUSF" class="@[930px]:w-1/2 w-full  h-full object-cover @[930px]:object-top transition-all duration-500">
                   <img src="<?php echo BASE_URL; ?>/assets/img/iusf2.jpg" alt="IUSF" class="w-0 hidden  @[710px]:block @[930px]:w-1/2 h-full object-cover transition-all duration-500">
                   <span class="style-overlay absolute inset-0" style="background: linear-gradient(to right, rgba(11, 17, 32, 0.70), rgba(18, 27, 46, 0.50));"></span>
               </div>
               <div class="relative z-10 flex flex-col h-full w-full items-center">
                   <div class=" absolute top-4 left-4">
                       <?php $logoColorMode = 'white'; $sede = 'IUSF'; ?>
                       <?php include APP_PATH . '/views/components/logo.php'; ?>
                       <?php unset($logoColorMode, $sede); ?>
                   </div>
                   <h2 class=" mt-auto self-end rounded-full bg-black/45 px-3 py-1 text-xl font-semibold tracking-wide text-white backdrop-blur-sm shadow-lg">IUSF</h2>
               </div>
           </a>
            <?php else: ?>
            <div data-card-desactivada data-nombre="IUSF" class="block style-card group lg:col-span-2 card-grit-home relative overflow-hidden min-h-75 @container cursor-pointer hover:scale-[1.02] transition-transform duration-200">
                <div class="container-logo">
                    <img src="<?php echo BASE_URL; ?>/assets/img/IUSF-1024x1024.jpg" alt="IUSF" class="@[930px]:w-1/2 w-full h-full object-cover @[930px]:object-top transition-all duration-500 grayscale">
                    <img src="<?php echo BASE_URL; ?>/assets/img/iusf2.jpg" alt="IUSF" class="w-0 hidden @[710px]:block @[930px]:w-1/2 h-full object-cover transition-all duration-500 grayscale">
                   <span class="style-overlay absolute inset-0" style="background: linear-gradient(to right, rgba(11, 17, 32, 0.70), rgba(18, 27, 46, 0.50));"></span>
               </div>
               <div class="relative z-10 flex flex-col h-full w-full items-center">
                   <div class=" absolute top-4 left-4">
                       <?php $logoColorMode = 'white'; $sede = 'IUSF'; ?>
                       <?php include APP_PATH . '/views/components/logo.php'; ?>
                       <?php unset($logoColorMode, $sede); ?>
                   </div>
                   <h2 class=" mt-auto self-end rounded-full bg-black/45 px-3 py-1 text-xl font-semibold tracking-wide text-white backdrop-blur-sm shadow-lg">IUSF</h2>
               </div>
                <div class="absolute inset-0 z-20 bg-red-900/20 flex flex-col items-center justify-center gap-2">
                    <i class="fa-solid fa-ban text-4xl text-white drop-shadow-md"></i>
                    <span class="text-white text-lg font-semibold drop-shadow-md">Encuestas desactivadas</span>
                    <span class="text-white/80 text-sm drop-shadow">Temporalmente no disponible</span>
                </div>
           </div>
           <?php endif; ?>

        </section>
    </main>

    <div id="modal-desactivada" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" data-close-modal></div>
        <div class="relative bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full text-center animate-[fadeIn_0.2s_ease-out]">
            <button type="button" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors" data-close-modal>
                <i class="fa-solid fa-times text-xl"></i>
            </button>
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fa-solid fa-ban text-4xl text-red-500"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-3">Encuestas Desactivadas</h2>
            <p class="text-gray-600 mb-2">Las encuestas para <strong id="modal-sede-nombre"></strong> están temporalmente desactivadas.</p>
            <p class="text-gray-500 text-sm mb-6">Intente más tarde o comuníquese con la administración.</p>
            <a href="<?php echo BASE_URL; ?>/" class="inline-block bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-lg font-medium transition">
                <i class="fa-solid fa-home mr-2"></i> Volver al inicio
            </a>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modal-desactivada');
        const sedeNombre = document.getElementById('modal-sede-nombre');

        document.querySelectorAll('[data-card-desactivada]').forEach(card => {
            card.addEventListener('click', function () {
                const nombre = this.getAttribute('data-nombre');
                sedeNombre.textContent = nombre;
                modal.classList.remove('hidden');
            });
        });

        document.querySelectorAll('[data-close-modal]').forEach(el => {
            el.addEventListener('click', function () {
                modal.classList.add('hidden');
            });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
            }
        });
    });
    </script>

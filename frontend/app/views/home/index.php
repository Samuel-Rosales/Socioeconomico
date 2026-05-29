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
           <div class="block style-card group lg:col-span-2 card-grit-home relative overflow-hidden min-h-75 @container opacity-60">
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
               <div class="absolute inset-0 z-20 bg-red-900/60 backdrop-blur-sm flex flex-col items-center justify-center gap-2">
                   <i class="fa-solid fa-ban text-4xl text-white"></i>
                   <span class="text-white text-lg font-semibold">Encuestas desactivadas</span>
                   <span class="text-white/80 text-sm">Temporalmente no disponible</span>
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
           <div class="style-card block group card-grit-home lg:row-span-2 relative overflow-hidden min-h-75 @container opacity-60">
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
               <div class="absolute inset-0 z-20 bg-red-900/60 backdrop-blur-sm flex flex-col items-center justify-center gap-2">
                   <i class="fa-solid fa-ban text-4xl text-white"></i>
                   <span class="text-white text-lg font-semibold">Encuestas desactivadas</span>
                   <span class="text-white/80 text-sm">Temporalmente no disponible</span>
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
           <div class="style-card block group card-grit-home lg:row-span-2 relative overflow-hidden min-h-75 @container opacity-60">
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
               <div class="absolute inset-0 z-20 bg-red-900/60 backdrop-blur-sm flex flex-col items-center justify-center gap-2">
                   <i class="fa-solid fa-ban text-4xl text-white"></i>
                   <span class="text-white text-lg font-semibold">Encuestas desactivadas</span>
                   <span class="text-white/80 text-sm">Temporalmente no disponible</span>
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
           <div class="block style-card group card-grit-home relative overflow-hidden min-h-75 @container opacity-60">
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
               <div class="absolute inset-0 z-20 bg-red-900/60 backdrop-blur-sm flex flex-col items-center justify-center gap-2">
                   <i class="fa-solid fa-ban text-4xl text-white"></i>
                   <span class="text-white text-lg font-semibold">Encuestas desactivadas</span>
                   <span class="text-white/80 text-sm">Temporalmente no disponible</span>
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
           <div class="block style-card group lg:col-span-2 card-grit-home relative overflow-hidden min-h-75 @container">
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
               <div class="absolute inset-0 z-20 bg-red-900/30 backdrop-blur-sm opacity-50 flex flex-col items-center justify-center gap-2">
                   <i class="fa-solid fa-ban text-4xl text-white"></i>
                   <span class="text-white text-lg font-semibold">Encuestas desactivadas</span>
                   <span class="text-white/80 text-sm">Temporalmente no disponible</span>
               </div>
           </div>
           <?php endif; ?>

       </section>
   </main>

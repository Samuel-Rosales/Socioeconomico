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

          <a href="<?php echo BASE_URL; ?>/IUJO-BARQUISIMETO/formulario" class=" block style-card group lg:col-span-2 card-grit-home relative overflow-hidden min-h-75 @container">
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

          <a href="<?php echo BASE_URL; ?>/IUJO-PETARE/formulario" class="style-card block group card-grit-home lg:row-span-2 relative overflow-hidden min-h-75 @container">
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

          <a href="<?php echo BASE_URL; ?>/IUJO-CATIA/formulario" class="style-card block group card-grit-home lg:row-span-2 relative overflow-hidden min-h-75 @container">
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

          <a href="<?php echo BASE_URL; ?>/IUJO-GUANARITO/formulario" class=" block style-card group card-grit-home relative overflow-hidden min-h-75 @container">
              <div class="container-logo">
                  <img src="<?php echo BASE_URL; ?>/assets/img/IUJO-Guanarito.jpg" alt="IUJO Guanarito" class="w-full h-full object-cover">
                  <span class="style-overlay absolute inset-0" style="background: linear-gradient(to right, rgba(11, 17, 32, 0.70), rgba(18, 27, 46, 0.50));"></span>
              </div>
              <div class="relative z-10 flex flex-col h-full w-full items-center">
                  <div class=" absolute top-4 left-4">
                      <?php $logoColorMode = 'white'; ?>
                      <?php include APP_PATH . '/views/components/logo.php'; ?>
                      <?php unset($sede, $logoColorMode); ?>
                  </div>
                  <h2 class=" mt-auto self-end rounded-full bg-black/45 px-3 py-1 text-xl font-semibold tracking-wide text-white backdrop-blur-sm shadow-lg">IUJO Guanarito</h2>
              </div>
          </a>

          <a href="<?php echo BASE_URL; ?>/IUSF/formulario" class=" block style-card group lg:col-span-2 card-grit-home relative overflow-hidden min-h-75 @container">
              <div class="container-logo">
                  <img src="<?php echo BASE_URL; ?>/assets/img/IUSF-1024x1024.jpg" alt="IUJO Barquisimeto" class="@[930px]:w-1/2 w-full  h-full object-cover @[930px]:object-top transition-all duration-500">
                  <img src="<?php echo BASE_URL; ?>/assets/img/iusf2.jpg" alt="IUJO Barquisimeto" class="w-0 hidden  @[710px]:block @[930px]:w-1/2 h-full object-cover transition-all duration-500">
                  <span class="style-overlay absolute inset-0" style="background: linear-gradient(to right, rgba(11, 17, 32, 0.70), rgba(18, 27, 46, 0.50));"></span>
              </div>
              <div class="relative z-10 flex flex-col h-full w-full items-center">
                  <div class=" absolute top-4 left-4">
                      <?php $logoColorMode = 'white'; ?>
                      <?php $sede = 'IUSF'; ?>
                      <?php include APP_PATH . '/views/components/logo.php'; ?>
                      <?php unset($sede, $logoColorMode); ?>
                  </div>
                  <h2 class=" mt-auto self-end rounded-full bg-black/45 px-3 py-1 text-xl font-semibold tracking-wide text-white backdrop-blur-sm shadow-lg">IUSF</h2>
              </div>
          </a>
      </section>
  </main>
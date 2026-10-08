</main>

<footer class="bg-inverse-surface text-surface py-20 relative overflow-hidden mt-0">
  <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-primary via-secondary to-primary-strong"></div>
  <div class="grid grid-cols-1 md:grid-cols-12 gap-10 px-gutter max-w-container-max mx-auto relative z-10">
    <div class="md:col-span-4">
      <img alt="<?= e(SITE_NAME) ?>" class="h-14 mb-7 w-auto" src="<?= base_url('assets/img/fotos/logo-light.svg') ?>"
           onerror="this.outerHTML='<div class=\'font-display text-2xl font-extrabold text-white\'>Hotel Playa Cove&ntilde;as</div>'">
      <p class="text-outline-variant text-base max-w-sm mb-7 leading-relaxed"><?= e(SITE_DESC) ?></p>
      <div class="flex gap-3">
        <a class="w-12 h-12 rounded-full border border-white/20 grid place-items-center hover:bg-white hover:text-primary transition-all" href="<?= e(SOCIAL_FACEBOOK) ?>" target="_blank" rel="noopener" aria-label="Facebook"><span class="material-symbols-outlined">public</span></a>
        <a class="w-12 h-12 rounded-full border border-white/20 grid place-items-center hover:bg-white hover:text-primary transition-all" href="<?= e(SOCIAL_INSTAGRAM) ?>" target="_blank" rel="noopener" aria-label="Instagram"><span class="material-symbols-outlined">photo_camera</span></a>
        <a class="w-12 h-12 rounded-full border border-white/20 grid place-items-center hover:bg-white hover:text-primary transition-all" href="<?= whatsapp_link() ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><span class="material-symbols-outlined">chat</span></a>
      </div>
    </div>
    <div class="md:col-span-2">
      <h4 class="text-white font-bold mb-6 text-sm uppercase tracking-[.16em]">Hotel</h4>
      <ul class="space-y-3 text-sm">
        <li><a class="text-outline-variant hover:text-white transition-colors" href="<?= base_url('habitaciones.php') ?>">Habitaciones</a></li>
        <li><a class="text-outline-variant hover:text-white transition-colors" href="<?= base_url('servicios.php') ?>">Servicios</a></li>
        <li><a class="text-outline-variant hover:text-white transition-colors" href="<?= base_url('galeria.php') ?>">Galería</a></li>
        <li><a class="text-outline-variant hover:text-white transition-colors" href="<?= base_url('reservar.php') ?>">Reservar</a></li>
      </ul>
    </div>
    <div class="md:col-span-2">
      <h4 class="text-white font-bold mb-6 text-sm uppercase tracking-[.16em]">Información</h4>
      <ul class="space-y-3 text-sm">
        <li><a class="text-outline-variant hover:text-white transition-colors" href="<?= base_url('faq.php') ?>">Preguntas frecuentes</a></li>
        <li><a class="text-outline-variant hover:text-white transition-colors" href="<?= base_url('contacto.php') ?>">Contacto</a></li>
        <li class="text-outline-variant">Check-in <?= e(CHECKIN_HORA) ?> · Check-out <?= e(CHECKOUT_HORA) ?></li>
      </ul>
    </div>
    <div class="md:col-span-4">
      <div class="glass-panel p-7 rounded-3xl bg-white/5 border-white/10">
        <h4 class="text-white font-bold mb-2 text-lg">¿Listo para tu viaje?</h4>
        <p class="text-outline-variant text-sm mb-2"><?= e(CONTACT_ADDRESS) ?></p>
        <p class="text-outline-variant text-sm mb-5"><a href="tel:<?= e(CONTACT_PHONE) ?>" class="hover:text-white"><?= e(CONTACT_PHONE) ?></a> · <a href="mailto:<?= e(CONTACT_EMAIL) ?>" class="hover:text-white break-all"><?= e(CONTACT_EMAIL) ?></a></p>
        <a href="<?= base_url('reservar.php') ?>" class="inline-block bg-primary text-white px-6 py-3 rounded-full text-[.72rem] font-bold uppercase tracking-[.12em] hover:brightness-110">Reservar ahora</a>
      </div>
    </div>
  </div>
  <div class="max-w-container-max mx-auto px-gutter mt-16 pt-7 border-t border-white/10 flex flex-col md:flex-row justify-between items-center gap-3 text-xs text-outline-variant">
    <p>© <?= date('Y') ?> <?= e(SITE_NAME) ?>. Todos los derechos reservados.</p>
    <div class="flex gap-6">
      <a class="hover:text-white" href="<?= base_url('admin/login.php') ?>">ADMIN</a>
      <a class="hover:text-white" href="<?= base_url('faq.php') ?>">POLÍTICAS</a>
    </div>
  </div>
</footer>

<!-- WhatsApp flotante -->
<a class="fixed bottom-5 right-5 z-50 group" href="<?= whatsapp_link('Cordial saludo, necesito cotizar para las siguientes fechas y número de personas:') ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
  <span class="absolute -inset-3 bg-primary/20 rounded-full blur-xl group-hover:bg-primary/40 transition-all"></span>
  <span class="relative bg-white text-primary pl-3 pr-4 py-3 rounded-full shadow-xl hover:-translate-y-1 transition-all flex items-center gap-3">
    <span class="bg-[#25D366] p-2 rounded-full text-white grid place-items-center"><span class="material-symbols-outlined text-xl">chat</span></span>
    <span class="hidden sm:flex flex-col leading-tight">
      <span class="text-[9px] tracking-widest uppercase font-bold text-outline">Atención 24/7</span>
      <span class="text-[.72rem] font-bold tracking-wider uppercase">Escríbenos</span>
    </span>
  </span>
</a>

</body>
</html>

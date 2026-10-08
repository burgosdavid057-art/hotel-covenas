/* ============================================================
   Hotel Playa Coveñas — interacciones + wizard de reserva
   ============================================================ */
(function () {
  'use strict';
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const money = (n) => '$' + (n | 0).toLocaleString('es-CO');

  /* ---------- Wizard de reserva (Alpine component) ---------- */
  document.addEventListener('alpine:init', () => {
    Alpine.data('booking', (cfg) => ({
      cfg,
      step: 1,
      checkIn:  cfg.preset.checkIn  || '',
      checkOut: cfg.preset.checkOut || '',
      adultos:  cfg.preset.adultos  || 2,
      ninos:    cfg.preset.ninos    || 0,
      presetHab: cfg.preset.habId || null,
      loading: false,
      loadError: '',
      tipos: [],
      sel: null,
      extras: (cfg.extras || []).map(x => ({ ...x, sel: false })),
      guest: { nombre: '', documento: '', email: '', telefono: '', solicitudes: '' },
      errors: {},
      dateError: '',

      get today() { return cfg.today; },
      get huespedes() { return this.adultos + this.ninos; },
      get noches() {
        if (!this.checkIn || !this.checkOut) return 0;
        const a = new Date(this.checkIn + 'T00:00:00');
        const b = new Date(this.checkOut + 'T00:00:00');
        const d = Math.round((b - a) / 86400000);
        return d > 0 ? d : 0;
      },
      get minOut() {
        if (!this.checkIn) return this.today;
        const a = new Date(this.checkIn + 'T00:00:00');
        a.setDate(a.getDate() + 1);
        return a.toISOString().slice(0, 10);
      },
      fmt(n) { return money(n); },

      onCheckInChange() {
        if (this.checkOut && this.checkOut <= this.checkIn) this.checkOut = this.minOut;
        this.dateError = '';
      },
      step1Ok() {
        return this.checkIn && this.checkOut && this.checkIn >= this.today && this.noches > 0;
      },
      async goStep2() {
        if (!this.checkIn || !this.checkOut) { this.dateError = 'Selecciona las fechas de entrada y salida.'; return; }
        if (this.checkIn < this.today) { this.dateError = 'La fecha de entrada no puede ser en el pasado.'; return; }
        if (this.noches <= 0) { this.dateError = 'La salida debe ser posterior a la entrada.'; return; }
        this.dateError = '';
        this.step = 2;
        await this.loadAvail();
      },
      next() {
        if (this.step === 2 && !this.sel) return;
        if (this.step === 4 && !this.validateGuest()) return;
        if (this.step < 5) this.step++;
        this.scrollTop();
      },
      back() { if (this.step > 1) { this.step--; this.scrollTop(); } },
      goTo(s) { if (s < this.step) { this.step = s; this.scrollTop(); } },
      scrollTop() {
        const el = document.getElementById('wizard');
        if (el) el.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
      },

      async loadAvail() {
        this.loading = true; this.loadError = ''; this.tipos = []; this.sel = null;
        try {
          const q = new URLSearchParams({
            check_in: this.checkIn, check_out: this.checkOut,
            adultos: this.adultos, ninos: this.ninos,
          });
          const r = await fetch(cfg.apiUrl + '?' + q.toString(), { headers: { 'Accept': 'application/json' } });
          const data = await r.json();
          if (!r.ok || data.error) throw new Error(data.error || 'Error al consultar disponibilidad');
          this.tipos = data.tipos || [];
          if (this.presetHab) {
            const t = this.tipos.find(x => x.id === this.presetHab);
            const u = t && t.unidades.find(x => x.libre);
            if (u) this.selectUnit(t, u);
            this.presetHab = null;
          }
        } catch (err) {
          this.loadError = err.message || 'No se pudo cargar la disponibilidad.';
        } finally {
          this.loading = false;
        }
      },
      selectUnit(t, u) {
        this.sel = {
          habId: t.id, habNombre: t.nombre, unidadId: u.id, numero: u.numero, piso: u.piso,
          precioNoche: t.precio_noche, vista: t.vista_label, imagen: t.imagen,
        };
      },
      isSel(u) { return this.sel && this.sel.unidadId === u.id; },

      extraCost(x) {
        if (x.tipo === 'por_noche')   return x.precio * Math.max(1, this.noches);
        if (x.tipo === 'por_persona') return x.precio * Math.max(1, this.huespedes);
        return x.precio;
      },
      tipoLabel(x) {
        return x.tipo === 'por_noche' ? '/ noche' : x.tipo === 'por_persona' ? '/ persona' : '/ estancia';
      },
      get extrasSel() { return this.extras.filter(x => x.sel); },
      get extrasTotal() { return this.extrasSel.reduce((s, x) => s + this.extraCost(x), 0); },
      get subtotalHab() { return this.sel ? this.sel.precioNoche * Math.max(1, this.noches) : 0; },
      get total() { return this.subtotalHab + this.extrasTotal; },
      get anticipo() { return Math.round(this.total * cfg.depositPct / 100); },
      get extrasIds() { return JSON.stringify(this.extrasSel.map(x => x.id)); },

      validateGuest() {
        this.errors = {};
        if (!this.guest.nombre.trim()) this.errors.nombre = 'Ingresa el nombre del huésped.';
        if (!this.guest.telefono.trim()) this.errors.telefono = 'Ingresa un teléfono de contacto.';
        if (this.guest.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.guest.email))
          this.errors.email = 'Correo no válido.';
        return Object.keys(this.errors).length === 0;
      },
      canSubmit() { return this.sel && this.guest.nombre.trim() && this.guest.telefono.trim(); },
    }));
  });

  /* ---------- Reveal on scroll (IntersectionObserver) ---------- */
  function initReveal() {
    const els = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window) || reduce) {
      els.forEach(el => el.classList.add('active'));
      return;
    }
    const io = new IntersectionObserver((entries) => {
      entries.forEach(en => { if (en.isIntersecting) { en.target.classList.add('active'); io.unobserve(en.target); } });
    }, { threshold: 0.12 });
    els.forEach(el => io.observe(el));
  }

  /* ---------- Contadores ---------- */
  function initCounters() {
    document.querySelectorAll('[data-count]').forEach(el => {
      const end = parseFloat(el.dataset.count) || 0;
      const suffix = el.dataset.suffix || '';
      if (reduce || !('IntersectionObserver' in window)) { el.textContent = end.toLocaleString('es-CO') + suffix; return; }
      const io = new IntersectionObserver((entries) => {
        entries.forEach(en => {
          if (!en.isIntersecting) return;
          io.unobserve(el);
          let cur = 0; const step = Math.max(1, end / 40);
          const t = setInterval(() => { cur += step; if (cur >= end) { cur = end; clearInterval(t); } el.textContent = Math.round(cur).toLocaleString('es-CO') + suffix; }, 30);
        });
      }, { threshold: 0.6 });
      io.observe(el);
    });
  }

  /* ---------- Parallax sutil del hero ---------- */
  function initParallax() {
    const t = document.getElementById('hero-parallax');
    if (!t || reduce || !window.matchMedia('(hover:hover)').matches) return;
    document.addEventListener('mousemove', (e) => {
      const x = (window.innerWidth / 2 - e.pageX) / 70, y = (window.innerHeight / 2 - e.pageY) / 70;
      t.style.transform = `translate(${x}px, ${y}px)`;
    });
  }

  function init() { initReveal(); initCounters(); initParallax(); }
  if (document.readyState !== 'loading') init();
  else document.addEventListener('DOMContentLoaded', init);
})();

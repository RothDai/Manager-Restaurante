// assets/js/main.js
import { gsap } from "https://cdn.jsdelivr.net/npm/gsap@3.12.2/index.min.js";

// Partículas ligeras con Canvas API
(function initParticles() {
  const canvas = document.createElement('canvas');
  canvas.id = 'particle-canvas';
  document.body.appendChild(canvas);
  const ctx = canvas.getContext('2d');
  let w, h, particles = [];
  function resize(){ w = canvas.width = innerWidth; h = canvas.height = innerHeight; }
  window.addEventListener('resize', resize);
  resize();
  class Particle {
    constructor(){
      this.x = Math.random()*w;
      this.y = Math.random()*h;
      this.vx = (Math.random()-0.5)*0.3;
      this.vy = (Math.random()-0.5)*0.3;
      this.size = Math.random()*1.5+0.5;
    }
    move(){
      this.x += this.vx; this.y += this.vy;
      if(this.x<0||this.x>w) this.vx*=-1;
      if(this.y<0||this.y>h) this.vy*=-1;
    }
    draw(){
      ctx.fillStyle = 'rgba(27,38,59,0.3)';
      ctx.beginPath();
      ctx.arc(this.x,this.y,this.size,0,2*Math.PI);
      ctx.fill();
    }
  }
  function init(){ particles = Array.from({length: Math.floor((w*h)/80000)}, ()=>new Particle()); }
  function animate(){
    ctx.clearRect(0,0,w,h);
    particles.forEach(p=>{ p.move(); p.draw(); });
    requestAnimationFrame(animate);
  }
  init(); animate();
})();

// Animación de secciones al hacer scroll
document.addEventListener('DOMContentLoaded', () => {
  gsap.utils.toArray('section').forEach(section => {
    gsap.fromTo(section, 
      { opacity: 0, y: 40 }, 
      { opacity: 1, y: 0, duration: 0.8, scrollTrigger: {
        trigger: section, start: 'top 80%'
      }}
    );
  });

  // Animación del header
  gsap.from('.nav-menu li', { opacity:0, y:-10, duration:0.6, stagger:0.1 });
});

// Mobile menu toggler
document.querySelector('.menu-toggle')?.addEventListener('click', () => {
  document.getElementById('mobile-menu').classList.toggle('active');
});

// Chart.js animaciones
document.querySelectorAll('canvas').forEach(c => {
  const chart = c.chart;  // si ya lo inicializaste en cada pantalla como antes
  chart?.options?.animation && (chart.options.animation.duration = 1000);
});

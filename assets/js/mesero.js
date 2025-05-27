document.querySelectorAll('.btn').forEach(btn => {
  btn.addEventListener('mouseenter', () => gsap.to(btn, {scale:1.05, duration:0.2}));
  btn.addEventListener('mouseleave', () => gsap.to(btn, {scale:1, duration:0.2}));
});
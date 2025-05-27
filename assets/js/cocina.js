document.querySelectorAll('.alerta-urgente').forEach(item => {
  gsap.to(item, {backgroundColor:'#F87171', repeat:-1, yoyo:true, duration:0.5});
});
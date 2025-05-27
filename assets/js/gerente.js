document.addEventListener('DOMContentLoaded', () => {
  const ctx = document.getElementById('ventasLineChart').getContext('2d');
  const data = JSON.parse(document.getElementById('ventasLineChart').dataset.ventas);
  new Chart(ctx, { type:'line', data:{ labels:data.map(d=>d.hora), datasets:[{ label:'Ventas', data:data.map(d=>d.ventas) }] }, options:{animation:false} });
  gsap.from('#ventasLineChart',{opacity:0,y:30,duration:0.8});
});
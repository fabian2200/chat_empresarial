var x;
var y;
var dragonX;
var dragonY;
var mapa=[
// ... existing code ...

function moverDragon() {
  var direcciones = [
    {dx: 0, dy: -1}, // arriba
    {dx: 0, dy: 1},  // abajo
    {dx: -1, dy: 0}, // izquierda
    {dx: 1, dy: 0}   // derecha
  ];
  
  var direccionAleatoria = direcciones[Math.floor(Math.random() * direcciones.length)];
  var nuevaX = dragonX + direccionAleatoria.dx;
  var nuevaY = dragonY + direccionAleatoria.dy;
  
  if (mapita[nuevaY][nuevaX] !== '*') {
    mapita[dragonY][dragonX] = '_';
    dragonX = nuevaX;
    dragonY = nuevaY;
    mapita[dragonY][dragonX] = 'D';
  }
  
  generarMapa(mapita, 'empezar');
} 

function generarMapa(mapita, imagen) {
  laberinto.innerHTML='';
  var tabla = document.createElement('table');
  tabla.setAttribute("class","celdita");
  for (var i = 0; i < mapita.length; i++) {
    var fila = document.createElement('tr');
    for (var j = 0; j < mapita[i].length; j++) {
        var celda = document.createElement('td');
        if(mapita[i][j]=='*'){
          celda.setAttribute("class","pared");
        } else if(mapita[i][j]=='o'){
          x=j;
          y=i;
          celda.setAttribute("class", imagen);
        } else if (mapita[i][j]=='W') {
          xfinal=j;
          yfinal=i;
          celda.setAttribute("class","final");
        } else if (mapita[i][j]=='D') {
          celda.setAttribute("class","dragon");
        }
        fila.appendChild(celda);
    }
    tabla.appendChild(fila);
  }
  laberinto.appendChild(tabla);

  // Inicializar posición del dragón
  dragonX = 1;
  dragonY = 1;
  mapita[dragonY][dragonX] = 'D';

  // Iniciar el movimiento aleatorio del dragón
  setInterval(moverDragon, 1000);
} 
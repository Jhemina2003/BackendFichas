// Backend Socket.IO server para emitir eventos de ficha actual

const { Server } = require('socket.io');
const http = require('http');


const express = require('express');
const app = express();
app.use(express.json());

const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: '*',
  },
});
// Endpoint HTTP para emitir ficha actual desde Laravel
app.post('/emit-ficha', (req, res) => {
  const { ventanillaId, ficha } = req.body;
  fichaActualPorVentanilla[ventanillaId] = ficha;
  io.emit('ficha-actual', ficha);
  res.json({ ok: true });
});

// Mapa de ventanillaId -> ficha actual
const fichaActualPorVentanilla = {};

io.on('connection', (socket) => {
  console.log('Cliente conectado:', socket.id);

  // Escuchar cuando el frontend actualiza la ficha
  socket.on('actualizar-ficha', (data) => {
    // data debe incluir ventanillaId y ficha
    const { ventanillaId, ficha } = data;
    fichaActualPorVentanilla[ventanillaId] = ficha;
    // Emitir a todos los clientes la ficha actualizada de esa ventanilla
    io.emit('ficha-actual', ficha);
  });

  socket.on('disconnect', () => {
    console.log('Cliente desconectado:', socket.id);
  });
});

const PORT = 3000;
server.listen(PORT, () => {
  console.log(`Socket.IO server escuchando en puerto ${PORT}`);
});

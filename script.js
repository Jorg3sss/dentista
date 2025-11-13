async function cargarPacientes() {
  const res = await fetch("backend.php?accion=listarPacientes");
  const pacientes = await res.json();
  mostrarPacientes(pacientes);
}

function mostrarPacientes(pacientes) {
  const tbody = document.querySelector("#tablaPacientes tbody");
  tbody.innerHTML = "";
  pacientes.forEach(p => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${p.id_paciente}</td>
      <td>${p.nombre}</td>
      <td>${p.telefono || ""}</td>
      <td>${p.correo || ""}</td>
      <td>${p.historial_autorizado ? (p.historial || "Autorizado") : "No autorizado"}</td>
      <td><button onclick="verCitas(${p.id_paciente}, '${p.nombre}')">Ver citas</button></td>
    `;
    tbody.appendChild(tr);
  });
}

document.getElementById("formPaciente").addEventListener("submit", async e => {
  e.preventDefault();
  const paciente = {
    nombre: nombre.value,
    telefono: telefono.value,
    correo: correo.value,
    autoriza: autoriza.checked ? 1 : 0,
    historial: historial.value
  };
  await fetch("backend.php?accion=agregarPaciente", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(paciente)
  });
  alert("Paciente registrado correctamente ");
  e.target.reset();
  cargarPacientes();
});

document.getElementById("btnBuscar").addEventListener("click", async () => {
  const q = document.getElementById("busqueda").value.trim();
  if (q === "") return cargarPacientes();
  const res = await fetch(`backend.php?accion=buscarPaciente&q=${q}`);
  const pacientes = await res.json();
  mostrarPacientes(pacientes);
});

function cerrarCitas() {
  document.getElementById("citasSection").style.display = "none";
}

async function verCitas(id, nombre) {
  document.getElementById("citasSection").style.display = "block";
  document.getElementById("nombrePaciente").textContent = nombre;
  document.getElementById("idPaciente").value = id;
  const res = await fetch(`backend.php?accion=listarCitas&id_paciente=${id}`);
  const citas = await res.json();
  const tbody = document.querySelector("#tablaCitas tbody");
  tbody.innerHTML = "";
  citas.forEach(c => {
    const tr = document.createElement("tr");
    tr.innerHTML = `<td>${c.fecha_cita}</td><td>${c.tratamiento}</td><td>${c.notas_internas}</td>`;
    tbody.appendChild(tr);
  });
}

document.getElementById("formCita").addEventListener("submit", async e => {
  e.preventDefault();
  const cita = {
    id_paciente: idPaciente.value,
    fecha: fecha.value,
    hora: "00:00:00",
    tratamiento: tratamiento.value,
    notas: notas.value
  };
  await fetch("backend.php?accion=agregarCita", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(cita)
  });
  alert("Cita agregada ");
  verCitas(idPaciente.value, nombrePaciente.textContent);
  e.target.reset();
});

cargarPacientes();

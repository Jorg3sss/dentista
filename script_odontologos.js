// Cargar todos los odontólogos al inicio
async function cargarOdontologos() {
  const res = await fetch("backend.php?accion=listarOdontologos");
  const odontologos = await res.json();
  mostrarOdontologos(odontologos);
}

// Mostrar la tabla con los odontólogos
function mostrarOdontologos(odontologos) {
  const tbody = document.querySelector("#tablaOdontologos tbody");
  tbody.innerHTML = "";
  odontologos.forEach(o => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${o.id_odontologo}</td>
      <td>${o.nombre}</td>
      <td>${o.especialidad}</td>
      <td>${o.horario}</td>
      <td>${o.numero_colegiado}</td>
      <td>${o.telefono || ""}</td>
      <td>${o.correo || ""}</td>
      <td>
        <button onclick="eliminarOdontologo(${o.id_odontologo})">🗑️ Eliminar</button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

// Registrar odontólogo
document.getElementById("formOdontologo").addEventListener("submit", async e => {
  e.preventDefault();

  const odontologo = {
    nombre: nombre.value,
    especialidad: especialidad.value,
    horario: horario.value,
    numero_colegiado: numero_colegiado.value,
    telefono: telefono.value,
    correo: correo.value
  };

  await fetch("backend.php?accion=agregarOdontologo", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(odontologo)
  });

  alert("Odontólogo registrado correctamente");
  e.target.reset();
  cargarOdontologos();
});

// Buscar odontólogo
document.getElementById("btnBuscar").addEventListener("click", async () => {
  const q = document.getElementById("busqueda").value.trim();
  if (q === "") return cargarOdontologos();

  const res = await fetch(`backend.php?accion=buscarOdontologo&q=${q}`);
  const odontologos = await res.json();
  mostrarOdontologos(odontologos);
});

// Eliminar odontólogo
async function eliminarOdontologo(id) {
  if (!confirm("¿Seguro que deseas eliminar este odontólogo?")) return;

  await fetch(`backend.php?accion=eliminarOdontologo&id=${id}`);
  alert("Odontólogo eliminado");
  cargarOdontologos();
}

// Cargar datos al iniciar la página
cargarOdontologos();

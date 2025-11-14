// Cargar todos los odontólogos al inicio
async function cargarOdontologos() {
    // ADAPTACIÓN: Apuntar al nuevo backend
    const res = await fetch("backend_odontologos.php?accion=listarOdontologos");
    const odontologos = await res.json();
    mostrarOdontologos(odontologos);
}

// Mostrar la tabla con los odontólogos
function mostrarOdontologos(odontologos) {
    const tbody = document.querySelector("#tablaOdontologos tbody");
    tbody.innerHTML = "";
    
    if (!odontologos || odontologos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6">No se encontraron odontólogos.</td></tr>';
        return;
    }

    odontologos.forEach(o => {
        const tr = document.createElement("tr");
        
        // ADAPTACIÓN: Columnas de la tabla 'doctor'
        tr.innerHTML = `
          <td>${o.Num_colegiado}</td>
          <td>${o.nombre}</td>
          <td>${o.apellido}</td>
          <td>${o.telefono || ""}</td>
          <td>${o.correo || ""}</td>
          <td>
            <!-- ADAPTACIÓN: El ID para eliminar es el Num_colegiado -->
            <button onclick="eliminarOdontologo(${o.Num_colegiado})">🗑️ Eliminar</button>
          </td>
        `;
        tbody.appendChild(tr);
    });
}

// Registrar odontólogo
document.getElementById("formOdontologo").addEventListener("submit", async e => {
    e.preventDefault();

    // ADAPTACIÓN: Leer los nuevos campos del formulario
    const odontologo = {
        nombre: document.getElementById("nombre").value,
        apellido: document.getElementById("apellido").value,
        edad: document.getElementById("edad").value,
        numero_colegiado: document.getElementById("numero_colegiado").value,
        telefono: document.getElementById("telefono").value,
        correo: document.getElementById("correo").value,
        contrasena: document.getElementById("contrasena").value // ID del nuevo campo
    };

    // ADAPTACIÓN: Apuntar al nuevo backend
    const res = await fetch("backend_odontologos.php?accion=agregarOdontologo", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(odontologo)
    });

    const data = await res.json();

    if (data.status === 'ok') {
        // Mensaje de éxito simplificado
        alert(data.info || "Odontólogo registrado correctamente.");
        e.target.reset();
        cargarOdontologos();
    } else {
        alert("Error al registrar: " + (data.message || 'Error desconocido'));
    }
});

// Buscar odontólogo
document.getElementById("btnBuscar").addEventListener("click", async () => {
    const q = document.getElementById("busqueda").value.trim();
    if (q === "") return cargarOdontologos();

    // ADAPTACIÓN: Apuntar al nuevo backend
    const res = await fetch(`backend_odontologos.php?accion=buscarOdontologo&q=${q}`);
    const odontologos = await res.json();
    mostrarOdontologos(odontologos);
});

// Eliminar odontólogo
async function eliminarOdontologo(numColegiado) { // ADAPTACIÓN: El ID es numColegiado
    if (!confirm("¿Seguro que deseas eliminar este odontólogo?")) return;

    // ADAPTACIÓN: Apuntar al nuevo backend y pasar el ID (Num_colegiado)
    const res = await fetch(`backend_odontologos.php?accion=eliminarOdontologo&id=${numColegiado}`);
    const data = await res.json();

    if (data.status === 'ok') {
        alert("Odontólogo eliminado");
        cargarOdontologos();
    } else {
        alert("Error al eliminar: " + (data.message || 'Error desconocido'));
    }
}

// Cargar datos al iniciar la página
cargarOdontologos();
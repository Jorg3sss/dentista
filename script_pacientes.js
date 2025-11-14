// Cargar todos los pacientes (clientes) al inicio
async function cargarPacientes() {
    // ADAPTACIÓN: Apuntar al nuevo backend
    const res = await fetch("backend_pacientes.php?accion=listarClientes");
    const pacientes = await res.json();
    mostrarPacientes(pacientes);
}

// Mostrar la tabla con los pacientes
function mostrarPacientes(pacientes) {
    const tbody = document.querySelector("#tablaPacientes tbody");
    tbody.innerHTML = "";

    if (!pacientes || pacientes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7">No se encontraron pacientes.</td></tr>';
        return;
    }

    pacientes.forEach(p => {
        const tr = document.createElement("tr");
        // ADAPTADO a la tabla 'cliente'
        tr.innerHTML = `
          <td>${p.id}</td>
          <td>${p.nombre}</td>
          <td>${p.apellidos}</td>
          <td>${p.telefono || ""}</td>
          <td>${p.correo || ""}</td>
          <td>${p.historial || ""}</td>
          <td><button onclick="verCitas(${p.id}, '${p.nombre} ${p.apellidos}')">Ver Consultas</button></td>
        `;
        tbody.appendChild(tr);
    });
}

// Registrar paciente (cliente)
document.getElementById("formPaciente").addEventListener("submit", async e => {
    e.preventDefault();

    // ADAPTADO a la tabla 'cliente'
    const paciente = {
        nombre: document.getElementById("nombre").value,
        apellido: document.getElementById("apellido").value,
        telefono: document.getElementById("telefono").value,
        correo: document.getElementById("correo").value,
        historial: document.getElementById("historial").value
    };

    // ADAPTACIÓN: Apuntar al nuevo backend
    const res = await fetch("backend_pacientes.php?accion=agregarCliente", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(paciente)
    });

    const data = await res.json();
    if (data.status === 'ok') {
        alert("Paciente registrado correctamente");
        e.target.reset();
        cargarPacientes();
    } else {
        alert("Error al registrar: " + (data.message || 'Error desconocido'));
    }
});

// Buscar paciente
document.getElementById("btnBuscar").addEventListener("click", async () => {
    const q = document.getElementById("busqueda").value.trim();
    if (q === "") return cargarPacientes();
    
    // ADAPTACIÓN: Apuntar al nuevo backend
    const res = await fetch(`backend_pacientes.php?accion=buscarCliente&q=${q}`);
    const pacientes = await res.json();
    mostrarPacientes(pacientes);
});

// --- Lógica de Citas (Consultas) Modificada ---

function cerrarCitas() {
    document.getElementById("citasSection").style.display = "none";
}

async function verCitas(idCliente, nombre) {
    document.getElementById("citasSection").style.display = "block";
    document.getElementById("nombrePaciente").textContent = nombre;
    
    // ADAPTACIÓN: Apuntar al nuevo backend para listar 'consultas'
    const res = await fetch(`backend_pacientes.php?accion=listarConsultas&id_cliente=${idCliente}`);
    const consultas = await res.json();
    
    const tbody = document.querySelector("#tablaCitas tbody");
    tbody.innerHTML = "";

    if (!consultas || consultas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5">Este paciente no tiene consultas registradas.</td></tr>';
        return;
    }

    consultas.forEach(c => {
        const tr = document.createElement("tr");
        // ADAPTADO a la tabla 'consulta'
        tr.innerHTML = `
          <td>${c.fecha}</td>
          <td>${c.hora}</td>
          <td>${c.num_colegiado}</td>
          <td>${c.num_consultorio}</td>
          <td>${c.estado}</td>
        `;
        tbody.appendChild(tr);
    });
}

// FORMULARIO DE AGREGAR CITA ELIMINADO
// La lógica de 'agregarCita' fue removida por incompatibilidad.

// Cargar datos al iniciar la página
cargarPacientes();
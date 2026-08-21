function obtenerTareas() {
    return JSON.parse(localStorage.getItem("tareas"));
}

function guardarTareas(tareas) {
    localStorage.setItem("tareas", JSON.stringify(tareas));
}
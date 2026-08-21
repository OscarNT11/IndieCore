 function crearTarjeta(tarea, indice, animar = false) {

    const contenedor = document.getElementById("listaTareas");

    const tarjeta = document.createElement("div");
    tarjeta.classList.add("tarjeta");

    if (animar) {
        tarjeta.classList.add("animar");
    }

    const titulo = document.createElement("h3");
    titulo.textContent = tarea.titulo;

    const descripcion = document.createElement("p");
    descripcion.textContent = tarea.descripcion;

    const completar = document.createElement("button");
    completar.textContent = "Completar";

    completar.addEventListener("click", () => {

        tarjeta.classList.toggle("completada");

    });

    const eliminar = document.createElement("button");
    eliminar.textContent = "Eliminar";

    eliminar.addEventListener("click", () => {

        const tareas = obtenerTareas();

        tareas.splice(indice, 1);

        guardarTareas(tareas);

        mostrarTareas();

    });

    tarjeta.appendChild(titulo);
    tarjeta.appendChild(descripcion);
    tarjeta.appendChild(completar);
    tarjeta.appendChild(eliminar);

    contenedor.appendChild(tarjeta);
}

 function mostrarTareas() {

    const contenedor = document.getElementById("listaTareas");
    contenedor.innerHTML = "";

    const tareas = obtenerTareas();

    tareas.forEach((tarea, indice) => {

        crearTarjeta(tarea, indice);

    });

}
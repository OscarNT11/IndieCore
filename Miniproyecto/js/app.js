const boton = document.getElementById("agregar");

boton.addEventListener("click", () => {

    const titulo = document.getElementById("tituloTarea").value.trim();
    const descripcion = document.getElementById("tarea").value.trim();

    if (titulo === "" || descripcion === "") {
        alert("Complete todos los campos.");
        return;
    }

    const tareas = obtenerTareas();

    tareas.push({
        titulo: titulo,
        descripcion: descripcion
    });

    guardarTareas(tareas);

    crearTarjeta(
        tareas[tareas.length - 1],
        tareas.length - 1,
        true
    );

    document.getElementById("tituloTarea").value = "";
    document.getElementById("tarea").value = "";

});

mostrarTareas();
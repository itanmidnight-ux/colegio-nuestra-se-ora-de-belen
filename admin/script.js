document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("uploadModal");
    const openModalBtn = document.getElementById("openModalBtn");
    const closeBtn = document.querySelector(".close-btn");
    const form = document.getElementById("uploadForm");
    const modalTitle = document.getElementById("modal-title");
    const periodicoList = document.getElementById("periodico-list");

    // Modal behavior
    openModalBtn.onclick = () => {
        form.reset();
        modalTitle.textContent = "Subir nuevo periódico";
        document.getElementById("form-action").value = "add";
        document.getElementById("periodico-id").value = "";
        modal.style.display = "block";
        showStep(1); // Reset to first step
    }

    closeBtn.onclick = () => {
        modal.style.display = "none";
    }

    window.onclick = (event) => {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Form steps navigation
    const formSteps = document.querySelectorAll(".form-step");
    const prevBtn = document.getElementById("prevBtn");
    const nextBtn = document.getElementById("nextBtn");
    const submitBtn = document.getElementById("submitBtn");
    let currentStep = 1;

    function showStep(step) {
        formSteps.forEach(s => s.style.display = "none");
        document.querySelector(`[data-step="${step}"]`).style.display = "block";
        currentStep = step;
        prevBtn.style.display = step > 1 ? "inline-block" : "none";
        nextBtn.style.display = step < formSteps.length ? "inline-block" : "none";
        submitBtn.style.display = step === formSteps.length ? "inline-block" : "none";
    }

    nextBtn.onclick = () => {
        if (currentStep < formSteps.length) {
            showStep(currentStep + 1);
        }
    }

    prevBtn.onclick = () => {
        if (currentStep > 1) {
            showStep(currentStep - 1);
        }
    }

    // Form submission with AJAX
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        const action = formData.get('action');

        // Special validation for 'add' action
        if (action === 'add' && !formData.get('archivo').name) {
            alert("⚠️ Por favor, selecciona un archivo PDF.");
            return;
        }

        try {
            const response = await fetch("upload.php", {
                method: "POST",
                body: formData,
            });

            const result = await response.json();

            if (result.status === "ok") {
                alert(result.message);
                modal.style.display = "none";
                location.reload(); // Reload to show new list
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert("❌ Ocurrió un error en la solicitud.");
            console.error(error);
        }
    });

    // Handle Edit and Delete buttons
    periodicoList.addEventListener("click", async (e) => {
        if (e.target.classList.contains("edit-btn")) {
            const li = e.target.closest("li");
            const id = li.dataset.id;
            modalTitle.textContent = "Editar periódico";
            document.getElementById("form-action").value = "edit";
            document.getElementById("periodico-id").value = id;
            document.getElementById("archivo").required = false;

            try {
                const response = await fetch(`get_periodico.php?id=${id}`);
                const result = await response.json();
                if (result.status === "ok") {
                    const data = result.data;
                    document.getElementById("titulo").value = data.titulo;
                    document.getElementById("director").value = data.director;
                    document.getElementById("participantes").value = data.participantes;
                    document.getElementById("descripcion").value = data.descripcion;
                    document.getElementById("fecha").value = data.publicado_en;
                    modal.style.display = "block";
                    showStep(1);
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert("❌ No se pudo cargar los datos para edición.");
                console.error(error);
            }
        }

        if (e.target.classList.contains("delete-btn")) {
            if (confirm("¿Estás seguro de que quieres eliminar este periódico?")) {
                const li = e.target.closest("li");
                const id = li.dataset.id;
                const formData = new FormData();
                formData.append('id', id);

                try {
                    const response = await fetch("delete_periodico.php", {
                        method: "POST",
                        body: formData,
                    });
                    const result = await response.json();
                    if (result.status === "ok") {
                        alert(result.message);
                        li.remove();
                    } else {
                        alert(result.message);
                    }
                } catch (error) {
                    alert("❌ Error al eliminar el periódico.");
                    console.error(error);
                }
            }
        }
    });

    // Logout confirmation
    const logoutBtn = document.querySelector(".btn-logout");
    if (logoutBtn) {
      logoutBtn.addEventListener("click", (e) => {
        if (!confirm("¿Seguro que quieres cerrar sesión?")) {
          e.preventDefault();
        }
      });
    }
});

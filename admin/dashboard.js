document.addEventListener("DOMContentLoaded", () => {
    const addPDFBtn = document.getElementById("addPDFBtn");
    const fileDataModal = document.getElementById("fileDataModal");
    const fileDataForm = document.getElementById("fileDataForm");
    const editDataModal = document.getElementById("editDataModal");
    const editDataForm = document.getElementById("editDataForm");
    const deleteConfirmModal = document.getElementById("deleteConfirmModal");
    const deleteForm = document.getElementById("deleteForm");
    const pdfModal = document.getElementById("pdfModal");
    const mainCard = document.getElementById("mainCard");

    const closeModal = (modal) => {
        if (modal) modal.style.display = "none";
    };

    const renderMainPreview = ({ titulo, director, participantes, fecha, descripcion, pdf }) => {
        if (!mainCard) return;

        const participantesTexto = (participantes || "").trim() || "No registrados";
        const descripcionTexto = (descripcion || "").trim() || "Sin descripción disponible.";

        mainCard.classList.add("preview-card");
        mainCard.innerHTML = `
            <div class="preview-head">
                <p class="preview-kicker">Vista previa de edición</p>
                <h3>${titulo}</h3>
                <p><strong>Director:</strong> ${director}</p>
                <p><strong>Fecha:</strong> ${fecha}</p>
                <p><strong>Participantes:</strong> ${participantesTexto}</p>
                <p><strong>Descripción:</strong> ${descripcionTexto}</p>
            </div>
            <div class="preview-body">
                <embed src="${pdf}" type="application/pdf" width="100%" height="100%" />
                <div class="preview-actions">
                    <a href="${pdf}" target="_blank" rel="noopener" class="btn-view">🔎 Abrir en pestaña nueva</a>
                    <a href="${pdf}" download class="btn-view">⬇ Descargar PDF</a>
                </div>
            </div>
        `;
    };

    document.getElementById("closeModalAdd")?.addEventListener("click", () => closeModal(fileDataModal));
    document.getElementById("closeModalEdit")?.addEventListener("click", () => closeModal(editDataModal));
    document.getElementById("closeModalDelete")?.addEventListener("click", () => closeModal(deleteConfirmModal));
    document.getElementById("closePdfModal")?.addEventListener("click", () => {
        closeModal(pdfModal);
        const embed = document.getElementById("pdfEmbed");
        if (embed) embed.src = "";
    });

    const archivoInput = document.createElement("input");
    archivoInput.type = "file";
    archivoInput.accept = "application/pdf";

    addPDFBtn?.addEventListener("click", () => archivoInput.click());

    archivoInput.addEventListener("change", async () => {
        const file = archivoInput.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append("archivo", file);
        formData.append("action", "upload_only");

        try {
            const res = await fetch("upload.php", { method: "POST", body: formData });
            const result = await res.json();
            if (result.status === "ok") {
                document.getElementById("archivo_pdf").value = result.filename;
                fileDataModal.style.display = "block";
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert("❌ Error de conexión al subir PDF.");
            console.error(error);
        }
    });

    fileDataForm?.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(fileDataForm);
        formData.append("action", "add");

        try {
            const res = await fetch("manage.php", { method: "POST", body: formData });
            const result = await res.json();
            if (result.status === "ok" || result.status === "success") {
                closeModal(fileDataModal);
                alert(result.message);
                setTimeout(() => location.reload(), 250);
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert("❌ Error al guardar datos.");
            console.error(error);
        }
    });

    document.querySelectorAll(".btn-edit").forEach((btn) => {
        btn.addEventListener("click", () => {
            document.getElementById("edit_id").value = btn.dataset.id;
            document.getElementById("edit_titulo").value = btn.dataset.titulo;
            document.getElementById("edit_director").value = btn.dataset.director;
            document.getElementById("edit_fecha").value = btn.dataset.fecha;
            document.getElementById("edit_descripcion").value = btn.dataset.descripcion;
            editDataModal.style.display = "block";
        });
    });

    editDataForm?.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(editDataForm);
        formData.append("action", "edit");

        try {
            const res = await fetch("manage.php", { method: "POST", body: formData });
            const result = await res.json();
            if (result.status === "ok" || result.status === "success") {
                closeModal(editDataModal);
                alert(result.message);
                setTimeout(() => location.reload(), 250);
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert("❌ Error al editar periódico.");
            console.error(error);
        }
    });

    document.querySelectorAll(".btn-delete").forEach((btn) => {
        btn.addEventListener("click", () => {
            document.getElementById("delete_id").value = btn.dataset.id;
            deleteConfirmModal.style.display = "block";
        });
    });

    deleteForm?.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(deleteForm);
        formData.append("action", "delete");

        try {
            const res = await fetch("manage.php", { method: "POST", body: formData });
            const result = await res.json();
            if (result.status === "ok" || result.status === "success") {
                closeModal(deleteConfirmModal);
                alert(result.message);
                setTimeout(() => location.reload(), 250);
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert("❌ Error al eliminar periódico.");
            console.error(error);
        }
    });

    document.querySelectorAll(".periodico-item").forEach((item) => {
        item.addEventListener("click", (event) => {
            if (event.target.closest("button")) return;
            renderMainPreview({
                titulo: item.dataset.titulo,
                director: item.dataset.director,
                participantes: item.dataset.participantes,
                fecha: item.dataset.fecha,
                descripcion: item.dataset.descripcion,
                pdf: item.dataset.pdf,
            });
        });
    });

    document.querySelectorAll(".ver-btn").forEach((btn) => {
        btn.addEventListener("click", function () {
            const parent = this.closest(".periodico-item");
            renderMainPreview({
                titulo: parent?.dataset.titulo || this.dataset.titulo,
                director: parent?.dataset.director || "",
                participantes: parent?.dataset.participantes || "",
                fecha: parent?.dataset.fecha || "",
                descripcion: parent?.dataset.descripcion || "",
                pdf: this.dataset.pdf,
            });
        });
    });
});

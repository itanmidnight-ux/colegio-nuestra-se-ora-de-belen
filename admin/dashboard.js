document.addEventListener("DOMContentLoaded", () => {
    const addPDFBtn = document.getElementById("addPDFBtn");
    const fileDataModal = document.getElementById("fileDataModal");
    const fileDataForm = document.getElementById("fileDataForm");
    const editDataModal = document.getElementById("editDataModal");
    const editDataForm = document.getElementById("editDataForm");
    const deleteConfirmModal = document.getElementById("deleteConfirmModal");
    const deleteForm = document.getElementById("deleteForm");
    const pdfModal = document.getElementById("pdfModal");

    const closeModal = (modal) => {
        if (modal) modal.style.display = "none";
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

    document.querySelectorAll(".ver-btn").forEach((btn) => {
        btn.addEventListener("click", function () {
            const pdfUrl = this.dataset.pdf;
            const titulo = this.dataset.titulo;

            document.getElementById("pdfTitle").innerText = "📖 " + titulo;
            document.getElementById("downloadPdf").href = pdfUrl;
            document.getElementById("openPdfNewTab").href = pdfUrl;
            document.getElementById("pdfFallback").style.display = "none";
            const embed = document.getElementById("pdfEmbed");
            embed.src = pdfUrl;
            pdfModal.style.display = "flex";

            setTimeout(() => {
                document.getElementById("pdfFallback").style.display = "block";
            }, 1200);
        });
    });
});

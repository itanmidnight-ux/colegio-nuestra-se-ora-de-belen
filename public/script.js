document.addEventListener("DOMContentLoaded", () => {
    const commentForm = document.getElementById("commentForm");
    const commentsList = document.getElementById("commentsList");

    if (commentForm && commentsList) {
        commentForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(commentForm);

            try {
                const response = await fetch("comments.php", {
                    method: "POST",
                    body: formData,
                });

                const result = await response.json();
                if (result.status === "ok") {
                    const newCommentDiv = document.createElement("div");
                    newCommentDiv.className = "comment";
                    newCommentDiv.innerHTML = `
                        <strong>${result.usuario}</strong>
                        <p>${result.comentario}</p>
                        <span>${result.fecha}</span>
                    `;
                    const noCommentsMsg = commentsList.querySelector('p');
                    if (noCommentsMsg && noCommentsMsg.textContent.includes('No hay comentarios')) {
                        noCommentsMsg.remove();
                    }
                    commentsList.prepend(newCommentDiv);
                    commentForm.reset();
                } else {
                    alert("Error al enviar el comentario: " + result.message);
                }
            } catch (error) {
                console.error("Error al enviar el comentario:", error);
                alert("Ocurrió un error de conexión al enviar el comentario.");
            }
        });
    }

    const periodicosPanel = document.getElementById('periodicosPanel');
    const togglePeriodicosBtn = document.getElementById('togglePeriodicosBtn');
    const commentsPanel = document.getElementById('commentsPanel');
    const toggleCommentsBtn = document.getElementById('toggleCommentsBtn');

    if (periodicosPanel && togglePeriodicosBtn) {
        togglePeriodicosBtn.addEventListener('click', () => {
            periodicosPanel.classList.toggle('hidden');
            togglePeriodicosBtn.textContent = periodicosPanel.classList.contains('hidden') ? '>' : '<';
        });
    }

    if (commentsPanel && toggleCommentsBtn) {
        toggleCommentsBtn.addEventListener('click', () => {
            commentsPanel.classList.toggle('hidden');
            toggleCommentsBtn.textContent = commentsPanel.classList.contains('hidden') ? '<' : '>';
        });
    }

    const footerClock = document.getElementById('footerClock');
    if (footerClock) {
        const updateClock = () => {
            const now = new Date();
            const hh = String(now.getHours()).padStart(2, '0');
            const mm = String(now.getMinutes()).padStart(2, '0');
            const ss = String(now.getSeconds()).padStart(2, '0');
            footerClock.textContent = `${hh}:${mm}:${ss}`;
        };
        updateClock();
        setInterval(updateClock, 1000);
    }
});

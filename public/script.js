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

    const openModalButtons = document.querySelectorAll('[data-open-modal]');
    const closeModalButtons = document.querySelectorAll('[data-close-modal]');

    openModalButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const modalId = button.getAttribute('data-open-modal');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
                modal.setAttribute('aria-hidden', 'false');
            }
        });
    });

    closeModalButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const modal = button.closest('.footer-modal');
            if (modal) {
                modal.classList.remove('active');
                modal.setAttribute('aria-hidden', 'true');
            }
        });
    });

    document.querySelectorAll('.footer-modal').forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.classList.remove('active');
                modal.setAttribute('aria-hidden', 'true');
            }
        });
    });

    const surveyModal = document.createElement('div');
    surveyModal.className = 'survey-modal';
    surveyModal.innerHTML = `
      <div class="survey-overlay"></div>
      <div class="survey-card anim-card">
        <button class="survey-close" aria-label="Cerrar encuesta">✕</button>
        <h3 id="surveyTitle">Encuesta rápida</h3>
        <p id="surveyQuestion"></p>
        <form id="surveyForm" class="survey-options"></form>
      </div>
    `;
    document.body.appendChild(surveyModal);

    const surveyState = { visible: false, context: '', surveyId: null };
    const surveyForm = surveyModal.querySelector('#surveyForm');
    const surveyTitle = surveyModal.querySelector('#surveyTitle');
    const surveyQuestion = surveyModal.querySelector('#surveyQuestion');

    const closeSurvey = () => {
        surveyModal.classList.remove('active');
        surveyState.visible = false;
    };

    surveyModal.querySelector('.survey-close').addEventListener('click', closeSurvey);
    surveyModal.querySelector('.survey-overlay').addEventListener('click', closeSurvey);

    const renderSurvey = (payload, context) => {
        surveyState.context = context;
        surveyState.surveyId = payload.survey.id;
        surveyTitle.textContent = payload.survey.titulo;
        surveyQuestion.textContent = payload.survey.pregunta;

        surveyForm.innerHTML = '';
        payload.options.forEach((opt) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'survey-option';
            btn.textContent = opt.texto;
            btn.addEventListener('click', () => sendAnswer(payload.survey.id, opt.id));
            surveyForm.appendChild(btn);
        });

        surveyModal.classList.add('active');
        surveyState.visible = true;
    };

    const sendAnswer = async (encuestaId, opcionId) => {
        const fd = new FormData();
        fd.append('action', 'answer');
        fd.append('encuesta_id', encuestaId);
        fd.append('opcion_id', opcionId);
        fd.append('contexto', surveyState.context);

        const res = await fetch('encuestas.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.status === 'ok') {
            surveyQuestion.textContent = '¡Gracias por responder!';
            surveyForm.innerHTML = '';
            setTimeout(closeSurvey, 1200);
        }
    };

    const showSurvey = async (context) => {
        if (surveyState.visible) return;
        const key = `survey-shown-${context}`;
        if (sessionStorage.getItem(key)) return;
        const res = await fetch(`encuestas.php?action=active&context=${encodeURIComponent(context)}`);
        const json = await res.json();
        if (json.status === 'ok' && json.options.length) {
            renderSurvey(json, context);
            sessionStorage.setItem(key, '1');
        }
    };

    setTimeout(() => showSurvey('on_entry'), 900);

    document.querySelectorAll('.main-nav a').forEach((a) => {
        a.addEventListener('click', () => showSurvey('on_header_nav'));
    });

    document.querySelectorAll('.survey-download-link').forEach((a) => {
        a.addEventListener('click', () => showSurvey('on_download'));
    });

    document.querySelectorAll('a[href*="secciones"]').forEach((a) => {
        a.addEventListener('click', () => showSurvey('on_sections_menu'));
    });

    const finishBtn = document.getElementById('finishReadingBtn');
    if (finishBtn) {
        finishBtn.addEventListener('click', () => showSurvey('on_virtual_read_end'));
    }
});

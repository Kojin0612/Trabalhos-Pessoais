
// ============================================
// BIBLIOTECA KOJI - PESQUISA E MODO DE EXIBICAO
// ============================================

// PESQUISA DO CATALOGO

const searchInput = document.getElementById("searchInput");
const searchForm = document.getElementById("searchForm");
const bookTableBody = document.getElementById("bookTableBody");

function filterRows() {
    if (!searchInput || !bookTableBody) return;

    const term = searchInput.value.trim()
        .toLocaleLowerCase("pt-BR");

    const rows = bookTableBody.querySelectorAll(
        "tr:not(.empty-row)"
    );

    rows.forEach(row => {
        const rowText = row.textContent
            .toLocaleLowerCase("pt-BR");

        row.hidden = !rowText.includes(term);
    });
}

if (searchInput) {
    searchInput.addEventListener("input", filterRows);
}

if (searchForm) {
    searchForm.addEventListener("submit", event => {
        event.preventDefault();
        filterRows();

        const catalog = document.getElementById("catalogo");

        if (catalog) {
            catalog.scrollIntoView({
                behavior: "smooth",
                block: "start"
            });
        }
    });
}


// ============================================
// MODO CLARO / MODO ESCURO
// ============================================

(function setupTheme() {

    const themeButton = document.getElementById("themeToggle");
    const storageKey = "kojiTheme";

    // Recupera a preferência salva
    function readSavedTheme() {
        try {
            return localStorage.getItem(storageKey) === "dark"
                ? "dark"
                : "light";
        } catch (error) {
            return "light";
        }
    }

    // Aplica o tema escolhido
    function applyTheme(theme) {

        const darkMode = theme === "dark";

        document.body.classList.toggle(
            "dark-mode",
            darkMode
        );

        document.documentElement.style.colorScheme =
            darkMode ? "dark" : "light";

        // Atualiza o botão
        if (themeButton) {

            themeButton.textContent = darkMode
                ? "☀️ Modo claro"
                : "🌙 Modo escuro";

            themeButton.setAttribute(
                "aria-label",
                darkMode ? "Ativar modo claro" : "Ativar modo escuro"
            );

            themeButton.setAttribute(
                "aria-pressed",
                darkMode ? "true" : "false"
            );
        }
    }

    // Salva a preferência
    function saveTheme(theme) {
        try {
            localStorage.setItem(storageKey, theme);
        } catch (error) {
            // Continua funcionando mesmo sem armazenamento.
        }
    }

    // Inicializa o tema salvo
    applyTheme(readSavedTheme());

    // Alterna os temas ao clicar
    if (themeButton) {

        themeButton.addEventListener("click", () => {

            const isDark = document.body.classList.contains(
                "dark-mode"
            );

            const newTheme = isDark ? "light" : "dark";

            applyTheme(newTheme);
            saveTheme(newTheme);

        });
    }

    // Sincroniza a preferência entre abas
    window.addEventListener("storage", event => {

        if (event.key === storageKey) {
            applyTheme(
                event.newValue === "dark" ? "dark" : "light"
            );
        }

    });

})();

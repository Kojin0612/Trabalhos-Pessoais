// O cadastro e o catálogo agora são controlados pelo PHP/MySQL.
// Este arquivo cuida apenas da busca visual na tabela, sem interceptar o envio do formulário.
const searchInput = document.getElementById("searchInput");
const searchForm = document.getElementById("searchForm");
const bookTableBody = document.getElementById("bookTableBody");

function filterRows() {
  if (!searchInput || !bookTableBody) return;
  const term = searchInput.value.trim().toLocaleLowerCase("pt-BR");
  const rows = bookTableBody.querySelectorAll("tr:not(.empty-row)");
  let visible = 0;

  rows.forEach(row => {
    const matches = row.textContent.toLocaleLowerCase("pt-BR").includes(term);
    row.hidden = !matches;
    if (matches) visible++;
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
    if (catalog) catalog.scrollIntoView({ behavior: "smooth" });
  });
}

let isLoading = false;

const resultContainer = document.querySelector(".user-result");

let debounceTimeout = null;
const debounceTime = 500;
let page = 1
let maxPage = 0

const previous = document.querySelector("#previous")
const next = document.querySelector("#next")

const resultsPerPage = 10;
previous.addEventListener("click",  () => {
    if (page > 1) page--
    resetTimeout(updatePage);
})

next.addEventListener("click",  () => {
    if (page < maxPage) page++
    resetTimeout(updatePage);
})

document.querySelector('#search-user').addEventListener("input", () => {
    resetTimeout(() => {
        page = 1
        updatePage(document.querySelector('#search-user').value);
    })
})

function resetTimeout(func) {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(func, debounceTime);
}

function toggleLoaderState() {
    let loader = document.querySelector(".loader")
    if (loader) {
        if (!isLoading) loader.style.display = 'none';
        else loader.style.display = 'block';
        resultContainer.appendChild(loader);
    }
}

function userSearchHandler() {
    let response = JSON.parse(this.responseText);
    if (this.status === 200) {
        let users = response['users'];
        maxPage = Math.ceil(parseInt(response['count'])/resultsPerPage);
        resultContainer.innerHTML = `<div class="loader"></div>`;
        users.forEach(user => resultContainer.appendChild(createUser(user)));
        isLoading = false;
        toggleLoaderState();
        drawPagination();
    }
    else displayError(response.message);
}

function createUser(user) {
    const main = document.createElement('a');
    main.className = "user-details";
    main.href = `/users/${user['id']}`
    main.innerHTML = `
        <div class="user-info">
            <p class="name">${escapeHtml(user['username'])}</p>
            <p class="email">${escapeHtml(user['email'])}</p>
            <p class="questions">${escapeHtml(user['questions'])}</p>
            <p class="answers">${escapeHtml(user['answers'])}</p>
        </div>
    `;
    return main;
}
function updatePage(search = "") {
    isLoading = true
    resultContainer.innerHTML = `<div class="loader"></div>`;
    toggleLoaderState();
    sendAjaxRequest('get', '/api/users?' + encodeForAjax( {query: search, page: page, limit: resultsPerPage}), {}, userSearchHandler);
}

function createNumberNav(i, current) {
    const number = document.createElement("a");
    number.href="#";
    number.innerHTML = i.toString();
    number.className = "page-number";
    number.id = i.toString();
    if (current) number.classList.add("current");
    return number;
}

function toggleButtonsVisibility() {
    if (page === 1) previous.style.visibility = "hidden";
    if (page >= maxPage) next.style.visibility = "hidden";
    if (page < maxPage) next.style.visibility = "visible";
    if (page > 1) previous.style.visibility = "visible";
}

function drawPagination() {
    const nav = document.querySelector(".pagination");
    nav.innerHTML = "";
    nav.appendChild(createNumberNav(1, page === 1))
    if (page-3 > 1) {
        nav.innerHTML += `<a>...</a>`
    }
    for (let i = Math.max(2, page-2); i <= Math.min(maxPage-1, page+2); i++) {
        nav.appendChild(createNumberNav(i, i === page));
    }
    if (page+3 < maxPage) {
        nav.innerHTML += `<a>...</a>`
    }
    if (maxPage > 1) nav.appendChild(createNumberNav(maxPage, page === maxPage));
    toggleButtonsVisibility();
    (document.querySelectorAll(".page-number")).forEach((number) => {number.addEventListener("click", async () => {
        page = parseInt(number.id);
        updatePage();
    })})
}

updatePage();


let add_user = document.getElementById('add-user');
add_user.addEventListener('click', () => {
    let form = document.getElementById('add-user-form');
    if (add_user.innerHTML === "person_add") {
        add_user.innerHTML = "close";
        form.style.display = 'block';
    }
    else {
        add_user.innerHTML = "person_add";
        form.style.display = 'none';
    }
})


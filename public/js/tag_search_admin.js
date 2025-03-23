(() => {
    let isLoading = false;

    const resultContainer = document.querySelector(".pagination-result");

    let debounceTimeout = null;
    const debounceTime = 500;
    let page = 1
    let maxPage = 0
    const previous = document.querySelector("#previous")
    const next = document.querySelector("#next")

    const resultsPerPage = 20;
    previous.addEventListener("click",  () => {
        if (page > 1) page--
        resetTimeout(updatePage);
    })

    next.addEventListener("click",  () => {
        if (page < maxPage) page++
        resetTimeout(updatePage);
    })
    const searchInput = document.querySelector('#search-tags');
    searchInput.addEventListener("input", () => {
        resetTimeout(() => {
            page = 1
            updatePage(searchInput.value);
        })
    })

    function resetTimeout(func) {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(func, debounceTime);
}

function toggleLoaderState() {
    const loader = document.querySelector('.loader');
    if (!isLoading) loader.style.display = 'none';
    else loader.style.display = 'block';
}

function tagSearchHandler() {
    let response = JSON.parse(this.responseText);
    if (this.status === 200) {
        let tags = response['tags'];
        maxPage = Math.ceil(parseInt(response['count'])/resultsPerPage);
        resultContainer.innerHTML = "";
        if (page == 1 && tags.length === 0) {resultContainer.innerHTML = '<p>No tags found</p>'}
        Promise.all(tags.map(tag => createTag(tag))).then( tags => tags.forEach(tag => resultContainer.appendChild(tag)));
        isLoading = false;
        toggleLoaderState();
        drawPagination();
    }
    else displayError(response.message);
}

async function createTag(tag) {
    const tagLi = document.createElement('li');
    tagLi.setAttribute('data-id', tag['id']);
    tagLi.innerHTML = `
        <a href='/search?query=%5B${escapeHtml(tag['name'])}%5D' class="tag"><p class="tag">${escapeHtml(tag['name'])}</p></a>
        <div class="confirmation">
            <button class="confirm-action delete-tag" title="delete-tag"> 
                <span class="material-symbols-outlined">delete</span>
            </button>
        </div>    `
    tagLi.className = "tag-details";
    const deleteIcon = tagLi.querySelector('.delete-tag');
    await setAsConfirmAction(deleteIcon);
    deleteIcon.addEventListener('click', sendDeleteTagRequestHandler);
    return tagLi;
}

function sendDeleteTagRequestHandler() {
    const tagId = this.parentElement.parentElement.dataset.id;
    sendAjaxRequest('delete', `/api/tags/${tagId}`, {}, tagDeletedHandler);
}

function tagDeletedHandler() {
    const tag = JSON.parse(this.responseText);
    if(this.status === 200) {
        updatePage();
    }
    else displayError(tag.message);
}

function updatePage(search = "") {
    isLoading = true
    toggleLoaderState();
    sendAjaxRequest('get', '/api/tags?' + encodeForAjax( {query: search, page: page, limit: resultsPerPage}), {}, tagSearchHandler);
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

const addTag = document.getElementById('add-tag');
addTag.addEventListener('click', () => {
    const form = document.getElementById('add-tag-form');
    if (addTag.innerHTML === "new_label") {
        addTag.innerHTML = "close";
        form.style.display = 'block';
    }
    else {
        addTag.innerHTML = "new_label";
        form.style.display = 'none';
    }
});
const submitTagButton = document.getElementById('submit-tag');
const submitTagInput = submitTagButton.parentElement.querySelector('input[type="text"]');
submitTagButton.addEventListener('click', (event) => {
    event.preventDefault();
    const name = submitTagInput.value;
    if(!name) return
    sendAjaxRequest('post', '/api/tags', {name: name}, function () {
        let response = JSON.parse(this.responseText);
        if (this.status === 200) {
            updatePage();
            const totalTagsText = (document.getElementById("tags-count"));
            const next = parseInt((totalTagsText.innerText.match(/(\d+)/))[0]) + 1;
            totalTagsText.innerText = `Total tags: ${next}`;
        }
        else displayError(response.message);
    });
})

})();

const searchInput = document.getElementById('search-input');
const suggestionsContainer = document.getElementById('suggestions');
let currentController = null;



searchInput.addEventListener('input', async function () {
    const query = searchInput.value;
    if (query.length < 3) {
        // Query length <= 3: clear suggestions
        suggestionsContainer.innerHTML = '';
        suggestionsContainer.classList.remove('has-content');
        if (currentController) {
            currentController.abort();
        }
        return;
    }
    if (currentController) {
        currentController.abort();
    }
    currentController = new AbortController();
    const { signal } = currentController;
    Promise.all([
        fetch(`/api/questions?${encodeForAjax({query: query})}`, { signal }),
    ])
    .then(responses => {
        return Promise.all(responses.map(response => response.json()));
    })
    .then(([questions]) => {
        const results = { questions };
        createSearchResultsHandler(results);
    })
    .catch(error => {
        if (error.name === 'AbortError') {
            return; // Ignore AbortError, don't treat it as an actual error
        }
        console.error('Error fetching data:', error);
        suggestionsContainer.innerHTML = '';
        const errorMessage = document.createElement('div');
        errorMessage.classList.add('suggestion-item');
        errorMessage.textContent = 'An error occurred. Please try again.';
        errorMessage.style.pointerEvents = 'none';
        suggestionsContainer.appendChild(errorMessage);
        suggestionsContainer.classList.add('has-content');
    });
});

function createSearchResultsHandler(response) {
    suggestionsContainer.innerHTML = '';
    // No data found
    if (response.questions.count === 0) {
        const noResultsItem = document.createElement('li');
        noResultsItem.classList.add('suggestion-item');
        noResultsItem.textContent = 'No suggestions found';
        noResultsItem.style.pointerEvents = 'none'; // Make it unclickable
        suggestionsContainer.appendChild(noResultsItem);
        suggestionsContainer.classList.add('has-content');
        return;
    }

    // Questions found
    else {
        if (response.questions.count > 5) {
            response.questions.questions = response.questions.questions.slice(0, 5);
        }
        const questionsHeader = document.createElement('li');
        questionsHeader.classList.add('suggestion-header');
        questionsHeader.textContent = 'Questions suggested';
        suggestionsContainer.appendChild(questionsHeader);
        function selectSuggestion(title) {
            searchInput.value = title;
            suggestionsContainer.innerHTML = '';
            suggestionsContainer.classList.remove('has-content');
        }
        response.questions.questions.forEach(item => {
            const suggestionItem = document.createElement('li');
            suggestionItem.classList.add('suggestion-item');
            suggestionItem.textContent = item.title;
            suggestionItem.role = "option";
            suggestionItem.tabIndex = 0;
            suggestionItem.addEventListener('click', function () {
                selectSuggestion(item.title)
            });
            suggestionItem.addEventListener('keydown', function (event) {
                if(event.key === 'Enter') selectSuggestion(item.title);
                else if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    if(suggestionsContainer.lastChild !== suggestionItem) {
                        document.activeElement.nextElementSibling.focus();
                    }
                }
                else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    if(suggestionsContainer.querySelector(':nth-child(2)') !== suggestionItem) {
                        document.activeElement.previousElementSibling.focus();
                    }
                }
                else {
                    searchInput.focus();
                }
            });
            suggestionsContainer.appendChild(suggestionItem);
        });
    }
    suggestionsContainer.classList.add('has-content');
    searchInput.onkeydown = (ev) => {
        if (ev.key !== 'ArrowDown') return;
        ev.preventDefault();
        suggestionsContainer.querySelector(':nth-child(2)').focus();
    }
}


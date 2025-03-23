

// insert an answer
function createAnswer(response) {
    const answer = response.answer;
    const id = answer.post_id;
    const li = document.createElement('li');
    li.id = id;
    li.classList.add('answer');
    const isModerator = answer.post.user.account.toLowerCase() === 'moderator';
    const isVerified = answer.post.user.account.toLowerCase() === 'verified'
    let strippedText = document.createElement("div");
    strippedText.innerHTML = answer.post.content;
    let plainText = strippedText.textContent;
    li.innerHTML =
        `
    <div class="edit-zone">
        <button class="button-icon material-symbols-outlined edit-answer" id="${id}-edit-answer" title="Edit Answer">edit</button>
        <div class="confirmation">
            <button class="confirm-action delete-answer ${isModerator ? 'force-delete' : ''}"> 
                <span class="material-symbols-outlined" title="Delete Answer">delete</span>
            </button>
        </div>   
        <form id="${id}_edit_answer_form" class="hide">    
            <label for="edit-content-${id}">Edit your answer: <abbr class="requiredField" title="mandatory field">*</abbr>
                        <textarea id="content-editor-${id}" name="content">${plainText}</textarea></label>            
            <div class="answer-editor" id="editor-${id}">${answer.post.content}</div>
            <button type="submit" class="submit-edit-answer">Save</button>
        </form>  
    </div>
    <div id="${id}-answer-content" class="answer-content" data-id="${id}">
        <div class="answer-text">${answer.post.content}</div>
        <a href="/users/${answer.post.user_id}" class="author">
            <div class="image-container">
                    <img class="profile_photo ${answer.post.user.travelling ? 'travelling' : ''}" src="${answer.post.user.photo ? '/profile/' + answer.post.user.photo : '/profile/default.png'}"
                         alt="${escapeHtml(answer.post.user.username)}'s profile photo">
                        ${isVerified ? '<span class="material-symbols-outlined">verified</span>' : ''}
            </div>
            ${escapeHtml(answer.post.user.name)}
        </a>
        <div class="votes" data-post-id="${id}">
            <button class="button-icon material-symbols-outlined" title="UpVote" data-vote="Up">thumb_up</button>
            <span class="upvotes-count">0</span>
            <button class="button-icon material-symbols-outlined" title="DownVote" data-vote="Down">thumb_down</button>
            <span class="downvotes-count">0</span>
        </div>
        <p class="date">now</p>
    </div>
    <section class="comment-section" data-id="${answer.post_id}">
        <div class="comments-sum">
            <p>0</p>
            <button class="button-icon material-symbols-outlined icon" title="Show comments">mode_comment</button>
        </div>
        <input type="button" id="add-comment" class="add-comment hide" value="add a comment here">
        <form class="create_comment hide">
            <label for="new_comment_content">Write your comment here: <abbr class="requiredField" title="mandatory field">*</abbr></label>
            <textarea name="content" class="new_comment_content" maxlength="100" cols="10" rows="10"></textarea>
            <input type="submit" value="submit content" data-id="${answer.post_id}">
        </form>
    </section>
    `
    const deleteIcon = li.querySelector('.delete-answer');
    setAsConfirmAction(deleteIcon);
    deleteIcon.addEventListener('click', sendDeleteAnswerRequestHandler);
    const voteButtons = li.querySelectorAll('.votes button')
    if (voteButtons) voteButtons.forEach(vote => vote.onclick = voteClickHandler);
    return li;
}

function answerCreateHandler() {
    let response = JSON.parse(this.responseText);
    if (this.status === 200) {
        const answer_list = document.getElementById('answers') ?? (() => {
            const list = document.createElement('ul');
            list.classList.add('answers');
            list.classList.add('container');
            list.id = "answers";
            document.getElementById('answer-thread').appendChild(list);
            return list;
        })();
        const answer = createAnswer(response);
        answer_list.appendChild(answer);
        const submitCommentButton = answer.querySelector('.create_comment input[type="submit"]');
        const commentSection = answer.querySelector('.comment-section');
        commentSectionHandler(commentSection);
        submitCommentHandler(submitCommentButton);
        addAnswerEditListeners(response.answer.post_id);
        let quillEdit = getQuill(`#editor-${response.answer.post_id}`);
        (document.getElementById(`content-editor-${response.answer.post_id}`)).style.display = "none";
        const toolbar = quillEdit.getModule('toolbar');
        toolbar.addHandler('image', imageHandler);
        document.getElementById('new-answer').querySelector('.ql-editor').innerHTML = "";
        const prev = (document.querySelector(".answers h4")).innerText;
        const next = parseInt((prev.match(/(\d+)/))[0]) + 1;
        document.querySelector(".answers h4").innerText = next + " answers";
        displaySuccess('Answer posted successfully');
    }
    else displayError(response.message);
    submit = true;
}
let submit = true;
async function postAnswerEventListener() {
    const form = document.getElementById('create_answer');
    if(form) {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!submit) return;
            submit = false;
            const content = sanitizeHTML(quillAnswer.root.innerHTML);
            const question_id = document.querySelector('.question').id;
            sendAjaxRequest('put', `/api/questions/${question_id}`, {content: content}, answerCreateHandler);
        });
    }
}

postAnswerEventListener();

if (document.getElementById('new-answer')) {
    quillAnswer = getQuill('#new-answer');
    (document.getElementById('answer_content')).style.display = "none";
    const toolbar = quillAnswer.getModule('toolbar');
    toolbar.addHandler('image', imageHandler);
}

const create_question_form = document.getElementById('create_question_form');
if (create_question_form) {
    create_question_form.addEventListener('submit', function(e) {
        e.preventDefault();
        sendAjaxRequest('put', `/api/questions/`, getQuestionFields(), questionCreateHandler, 'application/json');
    });
}

function questionCreateHandler() {
    let response = JSON.parse(this.responseText);
    if (this.status === 200) {
        window.location = `/questions/${response}`
    }
    else {
        displayError(response.message);
        if(this.status === 422) {
            const errors = JSON.parse(this.response).errors;
            placeInputErrors(errors, create_question_form);
        }
    }
}

function togglevisibility(elementToListen, elementToHide, elementToShow, style="block") {
    if (elementToListen.innerHTML === "edit") {
        elementToHide.style.display = "none";
        elementToShow.style.display = style;
        elementToListen.innerHTML = "edit_off";
    }
    else if (elementToListen.innerHTML === "edit_off") {
        elementToHide.style.display = style;
        elementToShow.style.display = "none";
        elementToListen.innerHTML = "edit";
    }
}

function toggleEditQuestionView() {
    togglevisibility(elementById('edit_question'), elementById("question_content"), elementById("edit_question_form"),"grid");
}

function toggleEditAnswerView() {
    togglevisibility(elementById(this + '-edit-answer'), elementById(this + '-answer-content'), elementById(this + "_edit_answer_form"), "grid");
    document.querySelector('li.answer[id="' + this + '"]').style.display = "grid";
}

function toggleEditCommentView() {
    togglevisibility(this, this.parentElement.parentElement.querySelector('.comment-content'), this.parentElement.querySelector('.edit-comment-form'));
    this.parentElement.querySelector('.edit-comment-form textarea').focus();
}

function toggleProfileView() {
    togglevisibility(elementById("edit"), elementById("profile-other-content"), elementById("form-edit-profile"), "block");
}


function addSelectedTags() {
    const tags = document.querySelector("ul.tags");
    tags.innerHTML = '';
    const options = document.querySelectorAll('.multi-select-selected:not(.multi-select-all)');
    options.forEach(function(option) {
        const tag = document.createElement('li');
        tag.innerHTML = `<a href="/search?query=%5B${escapeHtml(option.textContent).trim()}%5D">${escapeHtml(option.textContent)}</a>`;
        tags.appendChild(tag);
    });
}
function addSelectedLocation() {
    const countryEdit = elementById('country');
    const cityEdit = elementById('city');
    const country = countryEdit.value;
    const city = cityEdit.value;
    const cityDisplayedField = elementById('question-city');
    const questionContent = elementById('question_content');
    if (country === '') {
        questionContent.querySelector('.location').remove();
        return;
    }
    const countryName = countryEdit.querySelector('option[value="' + country + '"]').textContent;
    const cityName = cityEdit.querySelector('option[value="' + city + '"]').textContent;
    let countryDisplayedField = elementById('question-country');
    if (countryDisplayedField) {
        countryDisplayedField.textContent = countryName;
    } else {
        const elem = document.createElement('div');
        elem.classList.add('location');
        elem.innerHTML = `
                    <span class="material-symbols-outlined location-icon" title="Location">location_on</span>
                    <a href="http://localhost:8000/countries/${country}" class="country" id="question-country" data-country-id="${country}"> ${countryName} </a>
                                    `;
        questionContent.querySelector('.date').insertAdjacentElement('afterend', elem);
        countryDisplayedField = questionContent.querySelector('.country');
    }
    if (city === '') {
        if(cityDisplayedField) {
            cityDisplayedField.remove();
        }
    }
    else {
        if (cityDisplayedField) {
            cityDisplayedField.textContent = cityName;
        } else {
            countryDisplayedField.insertAdjacentHTML('afterend', `<a href="http://localhost:8000/cities/${city}" class="city" id="question-city" data-city-id="${country}"> ${cityName} </a>`)
        }
    }
}

function questionUpdateHandler() {
    let response = JSON.parse(this.responseText);
    clearInputErrors();
    if (this.status === 200) {
        elementById("question-content").innerHTML = response.content;
        elementById("question-title").textContent = response.title;
        if (!elementById("question_content").querySelector(".edited")) elementById("question_content").innerHTML += `<p class="edited">[Edited]</p>`;
        addSelectedLocation();
        addSelectedTags();
        toggleEditQuestionView();
        displaySuccess('Edited question successfully!');
    }
    else {
        displayError(response.message);
        if(this.status === 422) {
            const errors = JSON.parse(this.response).errors;
            const form = elementById('edit_question_form');
            placeInputErrors(errors, form);
        }
    }
}

function answerUpdateHandler() {
    let response = JSON.parse(this.responseText);
    if (this.status === 200) {
        const id = response.post.id;
        (elementById(id + '-answer-content').querySelector('.answer-text')).innerHTML = response.post.content;
        if (!elementById(id + '-answer-content').querySelector(".edited")) elementById(id + '-answer-content').innerHTML += `<p class="edited">[Edited]</p>`;
        toggleEditAnswerView.call(id);
        displaySuccess('Edited answer successfully!');
    }
    else {
        displayError(response.message);
    }
}

function commentUpdateHandler() {
    let response = JSON.parse(this.responseText);
    if (this.status === 200) {
        const id = response.comment.id;
        const edit = document.querySelector('.comment[data-id="' + id + '"] .edit-comment');
        edit.parentElement.parentElement.querySelector('p.comment-content').textContent = response.comment.content;
        toggleEditCommentView.call(edit);
        displaySuccess('Edited comment successfully!');
    }
    else displayError(response.message);
}

function checkSameQuestion() {
    if (elementById('question-title').textContent !== elementById('title').value) return false;
    if (elementById('question-content').innerHTML !== (elementById('editor').querySelector('.ql-editor')).innerHTML) return false;
    if ((elementById('question-country') ? elementById('question-country').getAttribute('data-country-id') : '') !== (elementById('country') ? elementById('country').value : '')) return false;
    if ((elementById('question-city') ? elementById('question-city').getAttribute('data-city-id') :'') !== (elementById('city') ? elementById('city').value : '')) return false;
    const tags = document.querySelectorAll('.multi-select-selected:not(.multi-select-all)');
    const selectedTags = [...tags].map(tag => tag.getAttribute('data-value'));
    const pTags = document.querySelectorAll('.tags li a');
    const previousTags = [...pTags].map(tag => tag.getAttribute('data-tag-id'));
    return (selectedTags.toString() === previousTags.toString());
}

async function saveQuestionEdit(event) {
    event.preventDefault();
    const question_id = (document.querySelector('article.question')).id;
    if (checkSameQuestion(question_id)) {
        toggleEditQuestionView();
        return;
    }
    sendAjaxRequest('post', `/api/questions/${question_id}`, getQuestionFields(), questionUpdateHandler, 'application/json');
}

async function saveAnswerEdit(event, answer_id) {
    event.preventDefault();
    const content = sanitizeHTML(elementById('editor-' + answer_id).querySelector(".ql-editor").innerHTML);
    if (content === elementById(answer_id + '-answer-content').querySelector(".answer-text").innerHTML) {
        toggleEditAnswerView.call(answer_id);
        return;
    }
    sendAjaxRequest('post', `/api/answers/${answer_id}`, {content: content}, answerUpdateHandler);
}

async function saveCommentEdit(event) {
    event.preventDefault();
    const comment_id = this.parentElement.getAttribute('data-id');
    const content = this.parentElement.querySelector("textarea").value;
    sendAjaxRequest('post', `/api/comments/${comment_id}`, {content: content}, commentUpdateHandler);
}

// EVENT LISTENERS

// Profile page
const edit_profile = elementById("edit");
if(edit_profile){
    edit_profile.addEventListener('click', toggleProfileView);
}


// Questions page
const edit_question = elementById("edit_question");
if(edit_question){
    edit_question.addEventListener('click', toggleEditQuestionView);
}

const question_form = document.getElementById("edit_question_form");
if(question_form){
    question_form.addEventListener("submit", saveQuestionEdit);
}

function addAnswerEditListeners(id) {
    elementById(id + '-edit-answer').addEventListener('click', toggleEditAnswerView.bind(id));
    const submit_button = elementById(id+ '_edit_answer_form').querySelector("button[type='submit']");
    submit_button.addEventListener("click", async (event) => saveAnswerEdit(event, id));
}

const answers = document.querySelectorAll('.edit-answer');
if (answers) {
    [].forEach.call(answers, function(answer) {
        addAnswerEditListeners(answer.parentElement.parentElement.id);
    });
}

const comments = document.querySelectorAll('.comment');
if (comments) {
    [].forEach.call(comments, function(comment) {
        const editButton = comment.querySelector('.edit-comment');
        if (editButton) addCommentEditListeners(editButton, comment['data-id']);
    });
}

function addCommentEditListeners(edit) {
    edit.addEventListener('click', toggleEditCommentView);
    const submit_button = edit.parentElement.querySelector(".submit-edit-comment");
    submit_button.addEventListener("click", async function(event){
        event.preventDefault();
        saveCommentEdit.call(submit_button, event);
    });
}

const answerEditor = document.querySelectorAll('.answer-editor');

if (answerEditor) {
    answerEditor.forEach(function(editor) {
        let quillEdit = getQuill(`#${editor.id}`);
        (document.getElementById(`content-${editor.id}`)).style.display = "none";
        const toolbar = quillEdit.getModule('toolbar');
        toolbar.addHandler('image', imageHandler);
    })
}

async function addEventListeners(){
    const deleteAnswerIcons = document.querySelectorAll('.delete-answer');
    [].forEach.call(deleteAnswerIcons, function(deleteAnswerIcon) {
        deleteAnswerIcon.addEventListener('click', sendDeleteAnswerRequestHandler);
    });
    const deleteQuestionIcon = document.querySelector('.delete-question');
    if (deleteQuestionIcon) {
        deleteQuestionIcon.addEventListener('click', sendDeleteQuestionRequestHandler);
    }
    const removeAuthorShipIcon = document.querySelector('.remove-authorship-question');
    if (removeAuthorShipIcon) {
        removeAuthorShipIcon.addEventListener('click', sendRemoveAuthorshipRequestHandler);
    }
    const deleteCommentIcons = document.querySelectorAll('.delete-comment');
    [].forEach.call(deleteCommentIcons, function(deleteCommentIcon) {
        deleteCommentIcon.addEventListener('click', sendDeleteCommentRequestHandler);
    })
}


function sendDeleteAnswerRequestHandler() {
    const answer_id = this.parentElement.parentElement.parentElement.id;
    if(this.classList.contains('force-delete')) {
        sendAjaxRequest('delete', `/api/answers/${answer_id}`, {force: true}, answerForceDeletedHandler);
    }
    else {
        sendAjaxRequest('delete', `/api/answers/${answer_id}`, {}, answerDeletedHandler);
    }
}

function sendDeleteQuestionRequestHandler() {
    const question_id = this.parentElement.parentElement.id;
    sendAjaxRequest('delete', `/api/questions/${question_id}`, {}, questionDeletedHandler);
}

//to do: change icon when state changes
function sendRemoveAuthorshipRequestHandler() {
    const question_id = this.parentElement.parentElement.id;
    sendAjaxRequest('delete', `/api/questions/${question_id}/author`, {}, removedAuthorHandler);
}

function sendDeleteCommentRequestHandler() {
    const comment_id = this.parentElement.parentElement.parentElement.getAttribute("data-id");
    sendAjaxRequest('delete', `/api/comments/${comment_id}`, {}, commentDeletedHandler);
}

function answerDeletedHandler() {
    const answer = JSON.parse(this.responseText);
    if(this.status === 200) {
        let element = document.querySelector('li.answer[id="' + answer.id + '"]');
        element.removeChild(element.querySelector('div.edit-zone'));
        element.removeChild(element.querySelector('div.answer-content'));
        element.innerHTML = `<p id="${answer.id}-answer-content">This answer was deleted</p>` + element.innerHTML;
        displaySuccess('Successfully deleted answer!');
    }
    else displayError(answer.message);
}

function answerForceDeletedHandler() {
    const answer = JSON.parse(this.responseText);
    if(this.status === 200) {
        document.querySelector('li.answer[id="' + answer.id + '"]').remove();
        displaySuccess('Successfully deleted answer!');
    }
    else displayError(answer.message);
}

function questionDeletedHandler() {
    if(this.status === 200) window.location = "/";
    else displayError((JSON.parse(this.responseText)).message);
}

function removedAuthorHandler() {
    if(this.status === 200) window.location.reload();
    else displayError((JSON.parse(this.responseText)).message);
}

function commentDeletedHandler() {
    const response = JSON.parse(this.responseText);
    if(this.status === 200) {
        let element = document.querySelector('li.comment[data-id="' + response.id + '"]');
        const commentSection = element.parentElement.parentElement;
        element.parentElement.removeChild(element);
        const comment_count = commentSection.querySelector('.comments-sum p');
        comment_count.innerHTML = parseInt(comment_count.innerHTML) - 1;
        if(parseInt(comment_count.innerHTML) === 0) {
            commentSection.querySelector('ul.comments').remove();
        }
        displaySuccess('Successfully deleted comment!');
    }
    else displayError(response.message);
}

addEventListeners();

const selectElement = document.getElementById('country');
if(selectElement){
    selectElement.addEventListener('change', function() {
        const citiesSelect = elementById('city');
        citiesSelect.setAttribute('disabled', true);
        citiesSelect.innerHTML = '<option value="" id="empty-city">Select City</option>';
        if (selectElement.value !== "") {
            fetch(`/api/cities/${selectElement.value}`)
                .then(response => response.json())
                .then(data => {
                    citiesSelect.removeAttribute('disabled');
                    data.forEach(city => {
                        const option = createOption(city.id, city.name);
                        citiesSelect.appendChild(option);
                    });
                })
        }
    });
}

const followButton = document.querySelector('#followButton');
const followIcon = document.querySelector('.material-symbols-outlined.follow');

if (followButton && followIcon) {
    const postId = followButton.getAttribute('data-id');
    const urlCheckFollow = `/api/questions/${postId}/isFollowing`;

    // Verificar se o user segue e atualiza a página
    sendAjaxRequest('GET', urlCheckFollow, {}, function () {
        if (this.status >= 200 && this.status < 300) {
            const data = JSON.parse(this.responseText);
            if (data.isFollowing) {
                followButton.classList.add('followed');
                followIcon.classList.add('followed');
                followIcon.textContent = 'check_circle';
            }
        }
    }, 'application/json');

    // Lógica para seguir e deixar de seguir
    followButton.addEventListener('click', function () {
        const urlToggleFollow = `/api/questions/${postId}/toggleFollow`;

        sendAjaxRequest('POST', urlToggleFollow, {}, function () {
            if (this.status >= 200 && this.status < 300) {
                const data = JSON.parse(this.responseText);
                if (data.message === 'Followed successfully') {
                    followButton.classList.add('followed');
                    followIcon.classList.add('followed');
                    followIcon.textContent = 'check_circle';
                    displaySuccess('Question followed successfully!');
                } else if (data.message === 'Unfollowed successfully') {
                    followButton.classList.remove('followed');
                    followIcon.classList.remove('followed');
                    followIcon.textContent = 'add_circle';
                    displaySuccess('Question unfollowed successfully!');
                }
            } else {
                const response = JSON.parse(this.responseText);
                displayError(response.message);
                if (response.message === 'You need to login first') {
                    openLoginOverlay();
                }
            }
        }, 'application/json');
    });
}

// Lógica de adicioniar up e down votes e remover by click
function voteClickHandler() {
    const voteContainer = this.closest('.votes');
    const postId = voteContainer.getAttribute('data-post-id');
    const voteType = this.getAttribute('data-vote');

    sendAjaxRequest('POST', `/api/posts/${postId}/vote`, {vote: voteType}, function () {
        if (this.status >= 200 && this.status < 300) {
            const data = JSON.parse(this.responseText);
            if (data.message === 'Vote added successfully' || data.message === 'Vote updated successfully' || data.message === 'Vote removed successfully') {
                // Atualizar a contagem de votos
                const upvotesCount = voteContainer.querySelector('.upvotes-count');
                const downvotesCount = voteContainer.querySelector('.downvotes-count');
                const thumbUp = voteContainer.querySelector('.thumb-up');
                const thumbDown = voteContainer.querySelector('.thumb-down');

                if (voteType === 'Up') {
                    if (data.message === 'Vote removed successfully') {
                        thumbUp.classList.remove('upvoted');
                        upvotesCount.textContent = parseInt(upvotesCount.textContent) - 1;
                    } else if (data.message === 'Vote added successfully') {
                        thumbUp.classList.add('upvoted');
                        upvotesCount.textContent = parseInt(upvotesCount.textContent) + 1;
                    } else if (data.message === 'Vote updated successfully') {
                        thumbUp.classList.add('upvoted');
                        upvotesCount.textContent = parseInt(upvotesCount.textContent) + 1;
                        thumbDown.classList.remove('downvoted');
                        downvotesCount.textContent = parseInt(downvotesCount.textContent) - 1;
                    }
                } else {
                    if (data.message === 'Vote removed successfully') {
                        thumbDown.classList.remove('downvoted');
                        downvotesCount.textContent = parseInt(downvotesCount.textContent) - 1;
                    } else if (data.message === 'Vote added successfully') {
                        thumbDown.classList.add('downvoted');
                        downvotesCount.textContent = parseInt(downvotesCount.textContent) + 1;
                    } else if (data.message === 'Vote updated successfully') {
                        thumbDown.classList.add('downvoted');
                        downvotesCount.textContent = parseInt(downvotesCount.textContent) + 1;
                        thumbUp.classList.remove('upvoted');
                        upvotesCount.textContent = parseInt(upvotesCount.textContent) - 1;
                    }
                }
            }
        } else {
            const response = JSON.parse(this.responseText);
            displayError(response.message);
            if (response.message === 'You need to login first') {
                openLoginOverlay();
            }
        }
    }, 'application/json');
}


const voteButtons = document.querySelectorAll('.votes .material-symbols-outlined');
if(voteButtons) {
    voteButtons.forEach(button => {
        button.addEventListener('click', voteClickHandler);
    });
}

// Lógica para ao entrar ver se está votada a question

const voteContainers = document.querySelectorAll('.votes');
if (voteContainers) {
    voteContainers.forEach(container => {
        const postId = container.getAttribute('data-post-id');
        const thumbUp = container.querySelector('.thumb-up');
        const thumbDown = container.querySelector('.thumb-down');

        if (container.closest('.questionVotes')) {
            sendAjaxRequest('GET', `/api/questions/${postId}/hasUserVoted`, {}, function () {
                if (this.status >= 200 && this.status < 300) {
                    const data = JSON.parse(this.responseText);
                    if (data.hasVoted) {
                        // Adicionar upvoted ou downvoted
                        if (data.vote === 'Up') {
                            thumbUp.classList.add('upvoted');
                        } else if (data.vote === 'Down') {
                            thumbDown.classList.add('downvoted');
                        }
                    }
                }
            }, 'application/json');
        }

        if (container.closest('.answers')) {
            sendAjaxRequest('GET', `/api/answers/${postId}/hasUserVoted`, {}, function () {
                if (this.status >= 200 && this.status < 300) {
                    const data = JSON.parse(this.responseText);
                    if (data.hasVoted) {
                        // Adicionar upvoted ou downvoted
                        if (data.vote === 'Up') {
                            thumbUp.classList.add('upvoted');
                        } else if (data.vote === 'Down') {
                            thumbDown.classList.add('downvoted');
                        }
                    }
                }
            }, 'application/json');
        }
    });
}
const submitCommentButtons = document.querySelectorAll('.create_comment button');
if (submitCommentButtons) submitCommentButtons.forEach(function (submitCommentButton) {
    submitCommentHandler(submitCommentButton);
});

const commentSections = document.querySelectorAll('.comment-section');
if (commentSections) commentSections.forEach(function (commentSection) {
    commentSectionHandler(commentSection);
});
function commentSectionHandler(section) {
    const commentsSum = section.querySelector('.comments-sum');
    const addCommentButton = section.querySelector('input.add-comment');
    const addCommentForm = section.querySelector('form.create_comment');
    if (commentsSum) {
        commentsSum.addEventListener('click', () => {
            const commentsList = section.querySelector('.comments');
            let present = false;
            if (commentsList) {
                present = commentsList.classList.toggle('show');
                present ? addCommentButton.classList.add('show') : addCommentButton.classList.remove('show');
            } else {
                addCommentButton.classList.toggle('show');
            }
            addCommentForm.style.display = 'none';
        });
    }
}
function submitCommentHandler(submitCommentButton) {
    const addCommentForm = submitCommentButton.parentElement;
    const addCommentButton = addCommentForm.parentElement.querySelector('input.add-comment');
    addCommentButton.addEventListener('click', (event) => {
        event.preventDefault();
        addCommentButton.classList.remove('show');
        addCommentForm.style.display = 'block';
    });
    submitCommentButton.addEventListener('click', (event) => {
        event.preventDefault();
        const content = addCommentForm.querySelector('.new_comment_content').value;
        const post_id = submitCommentButton.getAttribute('data-id');
        sendAjaxRequest('put', `/api/posts/${post_id}`, {content: content}, commentCreateHandler);
        addCommentForm.style.display = 'none';
        addCommentForm.querySelector('textarea').value = "";
        addCommentForm.querySelector('textarea').innerText = "";
        addCommentButton.classList.add('show');
    });
}
function commentCreateHandler() {
    let response = JSON.parse(this.responseText);
    if (this.status === 200) {
        const comment_section = document.querySelector('.comment-section[data-id="' + response.comment.post_id + '"]');
        const comment_list = comment_section.querySelector('ul.comments') ?? (() => {
            const list = document.createElement('ul');
            list.classList.add('show', 'hide', 'comments');
            comment_section.querySelector('.comments-sum').parentElement.insertAdjacentElement("afterend", list);
            return list;
        })();
        comment_list.appendChild(createComment(response));
        const totalCommentsP =comment_section.querySelector('.comments-sum p');
        totalCommentsP.innerHTML = parseInt(totalCommentsP.innerHTML) + 1;
        comment_section.querySelector('.new_comment_content').value;
    }
    else {
        displayError(response.message);
    }
}

function createComment(response) {
    const comment = response.comment;
    const li = document.createElement('li');
    li.classList.add('comment');
    li.setAttribute('data-id', comment.id);
    li.innerHTML =
        `
        <div class="edit-zone">
            <button class="button-icon edit-comment material-symbols-outlined" data-id="${comment.id}" title="Edit Comment">edit</button>
            <div class="confirmation">
                <button class="confirm-action delete-comment">
                    <span class="material-symbols-outlined" title="Delete Comment">delete</span>
                 </button>
            </div>
            <form data-id="${comment.id}" class="edit-comment-form">
                <label>
                <textarea name="content" maxlength="1000">${escapeHtml(comment.content)}</textarea></label>
                <button type="submit" class="submit-edit-comment">Save</button>
            </form>
        </div>
        <p class="comment-content">${escapeHtml(comment.content)}</p>
        <a href="/users/${comment.user_id}">${escapeHtml(comment.user.name)}</a>
    `
    const deleteIcon = li.querySelector('.delete-comment');
    setAsConfirmAction(deleteIcon);
    deleteIcon.addEventListener('click', sendDeleteCommentRequestHandler);
    const editIcon = li.querySelector('.edit-comment');
    addCommentEditListeners(editIcon);
    return li;
}

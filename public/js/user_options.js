const deleteUser = document.querySelector('#remove-user');
if (deleteUser) {
    deleteUser.addEventListener('click', sendDeleteUserRequestHandler);
}

const blockUser = document.querySelector('#block-user');
if (blockUser) {
    blockUser.addEventListener('click', sendBlockUserRequestHandler);
}

function getUserIdFromURL() {
    const url = window.location.pathname;  
    const regex = /\/users\/(\d+)/;
    const match = url.match(regex);
    return match ? match[1]: null;
}

function sendDeleteUserRequestHandler() {
    const id = getUserIdFromURL();  
    sendAjaxRequest('delete', `/api/users/${id}`, {}, userDeletedHandler);
}

function sendBlockUserRequestHandler() {
    const id = getUserIdFromURL();  
    const blockValue = blockUser.querySelector('span').innerHTML.trim();
    if (blockValue === "block"){
        sendAjaxRequest('post', `/api/users/${id}/block`, {}, userBlockHandler);
    }
    else if (blockValue === "person_check") 
        sendAjaxRequest('post', `/api/users/${id}/unblock`, {}, userUnblockHandler);
}

function userDeletedHandler() {
    if(this.status === 200) window.location = "/";
    else displayError((JSON.parse(this.responseText)).message);
}

function userBlockHandler() {
    if(this.status === 200) {
        blockUser.querySelector('span').innerHTML = "person_check";
        displaySuccess("User blocked successfully.");
    }
    else displayError((JSON.parse(this.responseText)).message);
}

function userUnblockHandler() {
    if(this.status === 200) {
        blockUser.querySelector('span').innerHTML = "block";
        displaySuccess("User unblocked successfully.");
    }
    else displayError((JSON.parse(this.responseText)).message);
}
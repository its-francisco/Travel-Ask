
const followTagButtons = document.querySelectorAll('.followTagButton');

followTagButtons.forEach(button => {
    const followIcon = button.querySelector('.material-symbols-outlined.follow');
    const tag = button.getAttribute('data-id');

    // Verifica se o user já está a segui a tag
    const urlCheckFollow = `/api/tags/${tag}/isFollowing`;
    sendAjaxRequest('GET', urlCheckFollow, {}, function () {
        if (this.status >= 200 && this.status < 300) {
            const data = JSON.parse(this.responseText);
            if (data.isFollowing) {
                button.classList.add('followed');
                followIcon.textContent = 'check_circle';
            } else {
                button.classList.remove('followed');
                followIcon.textContent = 'add_circle';
            }
        }
    }, 'application/json');

    // seguir/nao seguir tag
    button.addEventListener('click', function () {
        const urlToggleFollow = `/api/tags/${tag}/toggleFollow`;

        sendAjaxRequest('POST', urlToggleFollow, {}, function () {
            if (this.status >= 200 && this.status < 300) {
                const data = JSON.parse(this.responseText);
                if (data.message === 'Followed successfully') {
                    button.classList.add('followed');
                    followIcon.textContent = 'check_circle';
                    displaySuccess('Tag followed successfully!');
                } else if (data.message === 'Unfollowed successfully') {
                    button.classList.remove('followed');
                    followIcon.textContent = 'add_circle';
                    displaySuccess('Tag unfollowed successfully!');
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
});

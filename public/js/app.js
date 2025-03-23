
// END OF NOTIFICATION CODE


function elementById(id) {
  return document.getElementById(id);
}


// Found online to escape HTML
const HTML_ESCAPE_MAP = {
  '&': '&amp;',
  '<': '&lt;',
  '>': '&gt;',
  '"': '&quot;',
  "'": '&#039;',
  '/': '&#x2F;',
  '`': '&#x60;',
  '=': '&#x3D;',
  '{': '&#123;',
  '}': '&#125;',
  '(': '&#40;',
  ')': '&#41;',
  '[': '&#91;',
  ']': '&#93;',
  '\\': '&#92;'
};


function escapeHtml(unsafe) {
  return String(unsafe).replace(/[&<>"'/`=(){}[\]\\]/g, function(s) {
    return HTML_ESCAPE_MAP[s];
  });
}

function encodeForAjax(data) {
  if (data == null) return null;
  return Object.keys(data).map(function(k){
    return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])
  }).join('&');
}


function createOption(value, textContent){
      const el = document.createElement('option');
      el.value = value;
      el.textContent = textContent;
      return el;
}

function sendAjaxRequest(method, url, data, handler, contentType = 'application/x-www-form-urlencoded') {
  let request = new XMLHttpRequest();
  request.open(method, url, true);
  request.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
  if (contentType !== 'multipart/form-data') request.setRequestHeader('Content-Type', contentType);
  request.setRequestHeader('Accept', 'application/json'); // this makes sure we get the proper error codes (422) on validation (see laravel validation docs)
  request.addEventListener('load', handler);
  switch (contentType) {
    case 'application/json':
      request.send(JSON.stringify(data));
      break;
    case 'application/x-www-form-urlencoded':
      request.send(encodeForAjax(data));
      break;
    default:
      request.send(data);
      break;
  }
}



function toggleMenu() {
  elementById("mobile-menu").classList.toggle('open-menu');
  if (elementById("mobile-menu").classList.contains('open-menu')) {
    document.body.style.overflow = 'hidden';
    elementById("menu-mobile-hb").textContent = "close";
  } else {
    document.body.style.overflow = '';
    elementById("menu-mobile-hb").innerHTML = "menu";

  }
}
if(elementById("menu-mobile-hb")){
  elementById("menu-mobile-hb").addEventListener('click', toggleMenu);
}

function displayError(error) {
  document.querySelector("#messages").innerHTML = ` 
  <article class="error">
      <i class="material-symbols-outlined" title="Error"> error </i>
      <p>${escapeHtml(error)}</p>
      <div class="message-progress"></div>
      <div class="airplane-icon">✈</div>
  </article>`
}

function displaySuccess(message) {
  document.querySelector("#messages").innerHTML = ` 
  <article class="success">
      <i class="material-symbols-outlined" title="Success"> check_circle </i>
      <p>${escapeHtml(message)}</p>
      <div class="message-progress"></div>
      <div class="airplane-icon">✈</div>
  </article>`
}

function clearInputErrors() {
  [].forEach.call(document.querySelectorAll('.input-error-message'), function(error) {
    error.remove();
  });
}

function placeInputErrors(messages, form) {
  if(!messages) return;
  clearInputErrors();
  let firstInput = null;
  for (const [field, errors] of Object.entries(messages)) {
    const input = form.querySelector('[name="' + field + '"]');
    if (firstInput === null) firstInput = input;
    const error = document.createElement('em');
    error.classList.add('input-error-message');
    error.innerText = errors[0];
    input.insertAdjacentElement('afterend', error);
  }
  if (firstInput) firstInput.focus();
}

// in register, check if passwords match and give strength

function checkPasswordStrength(password) {
  let strength = 0;
  if (password.length >= 10) strength += 4;
  if(password.length >= 16) strength += 2; 
  if (password.match(/[a-z]/)) strength += 1;
  if (password.match(/[A-Z]/)) strength += 1;
  if (password.match(/[0-9]/)) strength += 1;
  if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
  if(strength > 9) strength = 9;
  return strength;
}



// password strength for register
if(elementById("register-form")){
  const passwordInput = elementById('password');
  const confirmPasswordInput = elementById('password-confirm');
  const passwordStrength = elementById('password-strength');
  const passwordMatch = elementById('password-confirmation');
  const submitbutton = document.querySelector("#register-form button[type=submit]");
  passwordInput.addEventListener('input', function() {
    let strength = checkPasswordStrength(passwordInput.value);
    passwordStrength.value = strength;
    let strengthMessage = "";
    switch (strength) {
        case 0:
        case 1:
        case 2:
          strengthMessage = 'Very Weak';
          passwordStrength.style.color = 'red';
          break;
        case 3:
        case 4:
          strengthMessage = 'Moderate';
          passwordStrength.style.color = 'yellow';
          break;
        case 5:
        case 6:
        case 7:
          strengthMessage = 'Strong';
          passwordStrength.style.color = 'lightgreen';
          break;
        case 9:
        case 8:
          strengthMessage = 'Very Strong';
          passwordStrength.style.color = 'green';
          break;
     }

      elementById("password-strength-description").textContent = strengthMessage
  })
  confirmPasswordInput.addEventListener('input', function(){
    if(passwordInput.value === confirmPasswordInput.value){
      passwordMatch.textContent = "Passwords Match";
    }else{
      passwordMatch.textContent = "Passwords don't match";

    }
  })

}

async function setAsConfirmAction(action) {
  action.classList.add('confirm-action');
  action.addEventListener("click", async (event) => {
      if (action.classList.contains("confirm-action") || action.classList.contains("wait")) {
          event.preventDefault();
          event.stopImmediatePropagation();
          await confirmActionHandler(action);
      }
  });
}

async function confirmActionHandler(action) {
  const progressBar = document.createElement("div")
  progressBar.classList.add("message-progress")
  if (action.classList.contains("confirm-action") && !action.classList.contains("wait")) {
      action.innerHTML += "Are you sure?";
      action.classList.add("wait");
      action.parentElement.appendChild(progressBar)
      await new Promise(r => setTimeout(r, 300));
      action.classList.remove("wait");
      action.classList.remove("confirm-action");
      action._interval = setInterval(() => {
          clearConfirmAction(action);
      }, 2700);    
  }   
  const rest = document.querySelectorAll('.confirm-action');
  rest.forEach((other) => {
      if (other !== action) {
          clearConfirmAction(other);
      }
  })
}

function clearConfirmAction(currAction) {
  if (!currAction.classList.contains("confirm-action")){
      currAction.innerHTML = currAction.innerHTML.replace("Are you sure?", "");
      currAction.classList.add("confirm-action");
      currAction.parentElement.removeChild((currAction.parentElement.children)[1]);
      if (currAction._interval) {
          clearInterval(currAction._interval);
          currAction._interval = null;
      }    
  }
}

const actions = document.querySelectorAll('.confirm-action');
Promise.all(
    Array.from(actions).map((action) => setAsConfirmAction(action))
);




/***********  DARK ************/

function toggleDark(){
  document.querySelector("body").classList.toggle("dark");

  document.querySelectorAll('.dark-mode-btn').forEach(button => {
      button.textContent = button.textContent === 'dark_mode' ? 'light_mode' : 'dark_mode';
  });
}

document.querySelectorAll('.dark-mode-btn').forEach(button => {
  button.textContent = localStorage.getItem('theme') === 'dark' ? 'light_mode' : 'dark_mode';
  button.addEventListener('click', function() {
    const theme = this.textContent;
    toggleDark();
    localStorage.setItem('theme', theme === 'dark_mode' ? 'dark' : 'light');
  });
});

/******* LOGIN OVERLAY **************/
const loginoverlay = elementById("overlay-login")
const overlayloginform = elementById("outside-login-overlay");

function openLoginOverlay(){
  loginoverlay.showModal();
}

document.body.addEventListener('click', function(event) {
  if (event.target === overlayloginform) {
    loginoverlay.close();
  }
}
);
/******** SEARCH *************/

const searchoverlay = document.getElementById("search-overlay");
const outsideOverlay = document.getElementById("outside-overlay");

document.querySelectorAll('.search-header-btn').forEach(button => {
  button.addEventListener('click', () => {
    searchoverlay.showModal();
  });
});

if (outsideOverlay) {
  outsideOverlay.addEventListener('click', () => {
    if (searchoverlay.open) {
      searchoverlay.close();
    }
  });
}


/****** NOTIFICATION INBOX ************/


// handle notification

const notificationinbox = elementById("notification-inbox");
const notificationbuttonPC = elementById("notification-inbox-buttonpc");
const notificationbuttonMobile = elementById("notification-inbox-buttonmobile");

function markNotificationViewed(notificationType, notificationId){
  sendAjaxRequest("put", "/api/notification/view", {type: notificationType, id: notificationId});
  const notificationToRemove = document.querySelector(`.notification-item[data-id='${notificationId}']`);
  notificationToRemove.remove();
}


function createNotificationElement(notification){
  let text = "";
  let notificationType = "answer";
  if(notification.voter){
    // we have a vote notification!
    notificationType = "vote";
    text = "New vote on your question by " + notification.username; 
  }else{
    if (notification.question_title.length > 30) {
      text = "New answer on " + notification.question_title.substring(0, 30) + "..." + " by " + notification.username + ".";
    } else {
      text = "New answer on " + notification.question_title + " by " + notification.username + ".";
    }
  }

  const notificationElement = document.createElement('div');
  notificationElement.setAttribute('data-id', notification.id);
  notificationElement.classList.add('notification-item');
  const messageP = document.createElement('p');
  messageP.textContent = text;

  const dateP = document.createElement('span');
  dateP.textContent = `${new Date(notification.date).toLocaleString()}`;

  const viewedP = document.createElement('button');
  viewedP.classList.add('button-icon');
  viewedP.classList.add('normal-text');
  viewedP.textContent = `Mark as viewed `;
  viewedP.style.cursor = 'pointer';

  viewedP.onclick = function() {
    markNotificationViewed(notificationType, notification.id);
  };


  const linkA = document.createElement('a');
  linkA.href = "/questions/" + notification.post_id;
  linkA.textContent = ' View';

  notificationElement.appendChild(dateP);
  notificationElement.appendChild(messageP);
  notificationElement.appendChild(viewedP);
  notificationElement.appendChild(linkA);

  return notificationElement;
}

async function openNotificationBox(mobile){
  if(notificationinbox.classList.contains("display")){
    notificationinbox.classList.toggle("display");
    return;
  }

  if(mobile){
    toggleMenu();
  }
  notificationinbox.classList.toggle("display");

  let notificationsResult;
  try {
    const response = await fetch('/api/notification');
    notificationsResult = await response.json();
  } catch (error) {
    console.error('Error fetching notifications:', error);
    return;
  }

  // clear the notification inbox
  notificationinbox.innerHTML = '';
  const closeSpan = document.createElement('button');
  closeSpan.classList.add("material-symbols-outlined");
  closeSpan.classList.add("button-icon");
  closeSpan.title = "Close Inbox";
  closeSpan.textContent = 'close';
  closeSpan.style.cursor = 'pointer';

  closeSpan.onclick = function() {
    notificationinbox.classList.remove('display');

    mobile ? notificationbuttonMobile.classList.remove('clicked') : notificationbuttonPC.classList.remove('clicked');
  };
  notificationinbox.appendChild(closeSpan);



  if (!notificationsResult || notificationsResult.length === 0) {
    const emptyMessage = document.createElement('p');
    emptyMessage.textContent = 'No notifications available.';
    notificationinbox.appendChild(emptyMessage);
  }
  // iterate over notifications and create notification elements
  notificationsResult.forEach(notification => {
    const notificationElement = createNotificationElement(notification);
    notificationinbox.appendChild(notificationElement);
  });
  
}



if(notificationbuttonPC){
  notificationbuttonPC.addEventListener('click', ()=>{
    notificationbuttonPC.classList.toggle('clicked');
    openNotificationBox(false)});
  notificationbuttonMobile.addEventListener('click', ()=>{
    notificationbuttonMobile.classList.toggle('clicked');
    openNotificationBox(true)});
}


// Info helper div

const infoDivHTML = document.querySelector('#infohelper-div-content');
const infoDiv = document.querySelector('#infohelper-div');

document.querySelectorAll('.infohelper').forEach(helper => {
  helper.onmouseover =  helper.onfocus = function() {
    infoDiv.classList.add('display');
    infoDivHTML.innerHTML = helper.getAttribute('data-html');
  };
  helper.onmouseleave = helper.onblur = function() {
    infoDiv.classList.remove('display');
  };
});

function updatePFImageURL() {
  let response = JSON.parse(this.responseText);
  if (this.status === 200) {
    const image = document.getElementById("profile-photo");
    image.src = `/profile/${response}`;
    const imageSmall = document.querySelector(".profile_photo");
    imageSmall.src = `/profile/${response}`;
  }
  else displayError(response.message);
}

function loadFile(event) {
  const formData = new FormData();
  formData.append('photo', event.target.files[0]); // Add the file to the request body
  sendAjaxRequest('post', `/api/users/${getUserIdFromURL()}/image`, formData, updatePFImageURL, 'multipart/form-data');
}
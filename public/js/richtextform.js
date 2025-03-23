const toolbarOptions = [
    ['bold', 'italic', 'underline', 'strike'],        // toggled buttons

    [{ 'list': 'ordered'}, { 'list': 'bullet' }],

    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
    [ 'image'],          // add's image support
    [{ 'color': [] }],          // dropdown with defaults from theme

    ['clean']                                         // remove formatting button
];

function getQuill(id) {
    return new Quill(id, {
        modules: {
            toolbar: toolbarOptions,
            keyboard: {
                bindings: {
                    tab: {
                        key: 9,
                        handler: function (range, context) {
                            return true;
                        },
                    },
                },
            }
        },
    
        theme: 'snow'
        });
}

if (document.getElementById('editor')) {
    quill = getQuill('#editor');
    (document.getElementById('content')).style.display = "none";
    const toolbar = quill.getModule('toolbar');
    toolbar.addHandler('image', imageHandler);
}

function updateImageURL(quill) { 
    let response = JSON.parse(this.responseText);
    if (this.status === 200) {
        const range = quill.getSelection();
        quill.insertEmbed(range.index, 'image', `/posts/${response}`);
    }
    else displayError(response.message);
}

function imageHandler() {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();
    input.onchange = () => {
      const file = input.files[0];
      if (file) {
            let formData = new FormData();
            formData.append('image', file); 
            const thisQuill = this; 
            sendAjaxRequest('post', '/api/image', formData, function() {updateImageURL.call(this, thisQuill.quill)}, 'multipart/form-data');
        }
    };
}
  

function getQuestionFields() {
    const title = elementById('title').value;
    const content = sanitizeHTML((elementById('editor').querySelector('.ql-editor')) ? (elementById('editor').querySelector('.ql-editor')).innerHTML : elementById('content').value);
    const country = elementById('country').value;
    const city = elementById('city').value;
    let selectedTags = [];
    const tags = document.querySelectorAll('.multi-select-selected.multi-select-option');
    tags.forEach(function(tag) {
        selectedTags.push(tag.getAttribute('data-value'));
    });
    return {content: content, title: title, country: country, city: city, tags: selectedTags};
}

function sanitizeHTML(html) {
    const allowedTags = [
        'strong', 'em', 'u', 's',
        'ol', 'ul',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'div', 'img', 'p'
    ];
    const allowedAttributes = {
        img: ['src'],
        '*': ['style']
    };
    const allowedStyles = ['color'];

    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');

    function sanitizeElement(element) {
        if (!allowedTags.includes(element.tagName.toLowerCase())) {
            element.remove();
            return;
        }

        Array.from(element.attributes).forEach(attr => {
            const tag = element.tagName.toLowerCase();
            const allowedForTag = allowedAttributes[tag] || [];
            const allowedForAll = allowedAttributes['*'] || [];
            
            if (!allowedForTag.includes(attr.name) && !allowedForAll.includes(attr.name)) {
                element.removeAttribute(attr.name);
            }
        });

        if (element.hasAttribute('style')) {
            const allowedStyleRegex = new RegExp(`^(${allowedStyles.join('|')})\\s*:\\s*[^;]+;?$`, 'i');
            const sanitizedStyles = element.style.cssText
                .split(';')
                .map(style => style.trim())
                .filter(style => allowedStyleRegex.test(style))
                .join('; ');

            element.setAttribute('style', sanitizedStyles);
        }

        Array.from(element.children).forEach(child => sanitizeElement(child));
    }

    Array.from(doc.body.children).forEach(child => sanitizeElement(child));

    return doc.body.innerHTML;
}
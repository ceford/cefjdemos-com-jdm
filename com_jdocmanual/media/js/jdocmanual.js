
/**
 * setCookie for consistent cookie handling
 *
 */
function setCookie(name, value, days)
{
    let expires = "";
    let date = new Date();
  // is the siteroot set for this template
    let paths = Joomla.getOptions(["system.paths"], "No good");
    let root = paths.root ? paths.root : '/';
    let path = "; path=" + root;

    const samesite = "; samesite=None; secure=true";
    let baseFull = paths.baseFull; // "http:\/\/localhost\/j4ops\/"

    if (days) {
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    } else {
        date.setTime(date.getTime());
    }
    expires = "; expires=" + date.toGMTString();
    document.cookie = name + "=" + value + expires + path + samesite;
}

/**
 * getCookie - return cookie by name
 */
function getCookie(name)
{
    let nameEQ = name + "=";
    let ca = document.cookie.split(";");
    for (let i = 0; i < ca.length; i += 1) {
        let c = ca[i];
        while (c.charAt(0) === " ") {
            c = c.substring(1,c.length);
        }
        if (c.indexOf(nameEQ) === 0) {
            return c.substring(nameEQ.length,c.length);
        }
    }
    return null;
}

/**
 * eraseCookie by name
 */
function eraseCookie(name)
{
    setCookie(name,'',0);
}

/**
 * Joomla menu toggle - hide or show the Joomla menu
 */

let toggle = document.getElementById('toggle-joomla-menu');

if (toggle) {
    toggle.addEventListener('click', function () {
        let wrapper = document.getElementById('sidebar-wrapper');
        let style = getComputedStyle(wrapper);
        if (style.display === 'none') {
            wrapper.classList.remove('d-none');
        } else {
            wrapper.classList.add('d-none');
        }
    });
}

/**
 * Set the page content by clicking a page item in the index
 */
//let contents = document.getElementsByClassName("content-link");

let getPage = function (event) {
    event.preventDefault();
  // get the current manual
    let jdmcur = getCookie('jdmcur');
  // Set a default current manual.
    let curmanual = 'user';
    if (jdmcur) {
        curmanual = jdmcur.split('-')[0];
    }
  // this contains the full url of the link
    let url = new URL(this);
    let paramsString = url.search;
    let searchParams = new URLSearchParams(paramsString);
  // First check for change of index or page language
    if (searchParams.get('index_language_code')) {
        location = url;
        return;
    }
    if (searchParams.get('page_language_code')) {
        location = url;
        return;
    }
  // the manual maybe in article=user/ or in manual=user
    let manual = searchParams.get('manual');
    let heading = '';
    let filename = '';
    if (!manual) {
        let article = searchParams.get('article');
        if (article) {
            sp = article.split('/');
            manual = sp[0];
            heading = sp[1];
            filename = sp[2].split('.')[0] + '.md';
        }
    } else {
        heading = searchParams.get('heading');
        filename = searchParams.get('filename');
    }
  // if there is a change of manual
    if (curmanual !== manual || heading === null || filename === null) {
      // Is this a Site or Administrator instance?
        if (url.href.indexOf('/administrator/') > 0) {
            // Replace /jdocmanual with option=jdocmanual
            url.href = url.href.replace('/administrator/jdocmanual?', '/administrator/?option=com_jdocmanual&view=manual&');
        }
        location = url;
        return;
    }
    setPanelContent(manual, heading, filename);
  // add the highlight class for the selected index item
  //this.parentElement.classList.add("article-active");
    setlinks();
};

/**
 * Set the page content by clicking a link in the page
 */
setlinks();

function setlinks()
{
    let links = document.querySelectorAll('a[href*="jdocmanual"]');
    for (let i = 0; i < links.length; i += 1) {
        links[i].addEventListener('click', getPage, false);
    }
}

/**
 * Fetch the selected page from source.
 */
async function setPanelContent(manual, heading, filename)
{
    let document_title = document.getElementById('document-title');
    if (!document_title) {
        return;
    }

  // remove the highlight class from the selected index item
  /**
   * ChatGPT Conclusion:
   * call is generally used to control the context (this) within a function,
   * not specifically for array processing. However, it can be applied in cases
   * where you need to invoke a function with a specific this value for each
   * array element. For standard array processing, methods like forEach, map,
   * and reduce are more appropriate.
   *
   * index_items is a list of objects...
   */
    let index_items = document.getElementsByClassName('article-active');
    [].forEach.call(index_items, function (el) {
        el.classList.remove("article-active");
    });

    let document_panel = document.getElementById('document-panel');
    document_panel.innerHTML = `<div class="text-center">
    <div class="spinner-border m-5" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
    </div>`;
    let toc_panel = document.getElementById('toc-panel');

  // get token from javascript loaded in the page
    const token = Joomla.getOptions('csrf.token', '');
    let url = '?option=com_jdocmanual&task=content.fillpanel';
    let data = new URLSearchParams();

    let new_cookie = heading + '--' + filename;
    setCookie('jdm' + manual, new_cookie, 10);

    data.append('manual', manual);
    data.append('heading', heading);
    data.append('filename', filename);
    data.append(token, 1);
    const options = {
        body: data,
        method: 'POST'
    };
    let response = await fetch(url, options);
    if (!response.ok) {
        document_panel.innerHTML = response.status;
        throw new Error(Joomla.Text._('COM_MYCOMPONENT_JS_ERROR_STATUS'));
    } else {
        let result = await response.text();
        let obj = JSON.parse(result);
        toc_panel.innerHTML = obj[0];
        document_panel.innerHTML = obj[1];
        document_title.innerHTML = obj[2];
        setlinks();
        menuHighlight(heading, filename);
        let language = document.getElementById("language").value;
        setFeedback(manual, language, heading, filename);
    }
}

// the default index location - set on page load
let indexLocation = 'oncanvas';

function setIndexLocation()
{
    const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
  // if the width is less than 576 move the index off canvas
    let offcanvasId = document.getElementById('offcanvasMenu');
    let oncanvasId = document.getElementById('oncanvasMenu');
    let jdocmanulId = document.getElementById('jdocmanual-wrapper');
    if (vw < 576) {
        if (indexLocation == 'oncanvas') {
            offcanvasId.appendChild(jdocmanulId);
            indexLocation = 'offcanvas';
        }
    } else {
        if (indexLocation == 'offcanvas') {
            oncanvasId.appendChild(jdocmanulId);
            indexLocation = 'oncanvas';
        }
    }
}

function menuHighlight(heading, filename)
{
    filename = filename.replace('.md', '');
    let link = document.querySelector('a[href*="/' + heading + '/' + filename + '"]');
    if (link) {
        // Set the list item class for the article, the parent of the link.
        link.parentElement.classList.add("article-active");

        // Set the class for the <ul> containing the article <li>
        let currentUL = link.closest('ul');
        currentUL.classList.add('mm-show');

        // Get the parents of the current <ul> and show them too.
        let parent = currentUL.parentElement;
        while (parent && parent.tagName.toLowerCase() === 'li') {
            parent.classList.add('mm-active');
            let parentUL = parent.parentElement;
            if (parentUL.tagName.toLowerCase() === 'ul') {
                parentUL.classList.add('mm-show');
            }
            parent = parentUL.parentElement;
        }

        // Get a highlight value from the url bar if present
        // Example URL: https://example.com?highlight=WyJuZXdzZmVlZHMiXQ==

        // Get the current URL
        const currentUrl = window.location.href;

        // Create a URL object
        const url = new URL(currentUrl);

        // Get a specific query parameter
        const highlight = url.searchParams.get('highlight');

        // Change the browser bar URL
        let jdmcur = getCookie('jdmcur');
        if (jdmcur) {
            // user-en-en
            let parts = jdmcur.split('-');
            let lang = parts[2];
            let href = link.href.replace('jdocmanual?', lang + '/jdocmanual?');
            if (highlight) {
                // This has undesirable side effects!
                //href = href + '&highlight=' + highlight;
            }
            if (url.href.indexOf('/administrator/') < 0) {
                history.replaceState(null, '', href);
            }
        }
    }
    // Syntax highlighting
    hljs.highlightAll();
}

/**
 * Set data in the feedback form.
 */
function setFeedback(manual, language, heading, filename)
{
    // Check this is the Manual page
    if (document.getElementById("manual")) {
        document.getElementById("manual").value = manual;
        document.getElementById("language").value = language;
        document.getElementById("heading").value = heading;
        document.getElementById("filename").value = filename;
        document.getElementById("comment").value = '';
    }
}

/**
 * After page load set the active menu and open its accordion panel.
 */
document.addEventListener('DOMContentLoaded', function (event) {
  // Get the heading and filename from the cookies.
    let jdmcur = getCookie('jdmcur');
    if (jdmcur) {
      // user-en-en
        let parts = jdmcur.split('-');
        let manual = parts[0];
      // handf = heading and filename
        let handf = getCookie('jdm' + manual).split('--');
        menuHighlight(handf[0], handf[1]);
        setIndexLocation();
        setFeedback(manual, parts[2], handf[0], handf[1]);
    }
});

window.addEventListener('resize', setIndexLocation);

var modalbox = document.getElementById("jdmFeedback");
if (modalbox) {
    var modalTitle = modalbox.querySelector('.modal-title');
    var modalBody = modalbox.querySelector('.modal-body');
}

/**
 * Respond to a click of the Feedback Save button
 */
var modalsave = document.getElementById("modal-save");
modalsave && modalsave.addEventListener('click', function (event) {
    sendFeedback('alldone');
})

/**
 * Actions to take when the modal dialog is displayed.
 */
modalbox && modalbox.addEventListener('show.bs.modal', function (event) {
    // Button that triggered the modal
    let button = event.relatedTarget;

    // Extract info from data-bs-* attributes
    // Set the modal title to be the same as the article title
    let title = document.getElementById("document-title").innerText;
    modalTitle.textContent = title;

    // The id will be either like or dislike or comment
    let likeitornot  = button.getAttribute('data-bs-id');
    sendFeedback(likeitornot);
})

/**
 * Send the id of the like or dislike button
 * @param {*} likeitornot
 */
async function sendFeedback(likeitornot)
{
    const token = Joomla.getOptions('csrf.token', '');
    let url = '?option=com_jdocmanual&task=feedback.likeitornot';
    let manual = document.getElementById("manual").getAttribute('value');
    let language = document.getElementById("language").getAttribute('value');
    let heading = document.getElementById("heading").getAttribute('value');
    let filename = document.getElementById("filename").getAttribute('value');
    let comment = document.getElementById("comment");
    let comment_label = document.getElementById("comment_label");
    let modalSave = document.querySelector("#modal-save");


    let data = new URLSearchParams();
    data.append(`likeitornot`, likeitornot);
    data.append(`manual`, manual);
    data.append(`language`, language);
    data.append(`heading`, heading);
    data.append(`filename`, filename);
    data.append(`comment`, comment.value);
    data.append(token, 1);
    const options = {
        method: 'POST',
        body: data
    }
    let response = await fetch(url, options);
    if (!response.ok) {
        throw new Error(Joomla.Text._('COM_MYCOMPONENT_JS_ERROR_STATUS') + `${response.status}`);
    } else {
        let result = await response.json();
        //Set the label
        comment_label.innerText = result.comment_label;
        // result contains comment and stage(1 or 2)
        comment.value = result.comment;
        if (result.stage === 1) {
            modalSave.classList.remove('hidden');
        } else {
            modalSave.classList.add('hidden');
        }
    }
}

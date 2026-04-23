/**
 * Process the Manual build by language selector
 */

let updateHTML = function (event) {
    event.preventDefault();
    if (confirm('On first use this action may take several minutes. On rebuild it should be quick.')) {
        // Set an alert message using the system message container.
        const elem = document.getElementById('system-message-container');
        elem.innerHTML = '<div class="alert alert-info text-center">Please Wait!</div>';
        let cbi = this.getAttribute('data-cbi');
        const token = Joomla.getOptions('csrf.token', '');

        let force = document.querySelector('#force-' + cbi).value;
        let url = '?option=com_jdocmanual&task=manuals.buildhtml&manual=' + this.id + '&language=' + this.value + '&force=' + force + '&1=' + token;
        location = url;
    }
    return false;
}

let updateMenu = function (event) {
    event.preventDefault();

    // Set an alert message using the system message container.
    const elem = document.getElementById('system-message-container');
    elem.innerHTML = '<div class="alert alert-info text-center">Please Wait!</div>';
    let cbi = this.getAttribute('data-cbi');
    const token = Joomla.getOptions('csrf.token', '');

    let url = '?option=com_jdocmanual&task=manuals.buildmenus&manual=' + this.id + '&language=' + this.value + '&1=' + token;
    location = url;

    return false;
}

let links = document.querySelectorAll('.buildhtml');
for (let i = 0; i < links.length; i += 1) {
    links[i].addEventListener('change', updateHTML, false);
}

let menulinks = document.querySelectorAll('.buildmenu');
for (let i = 0; i < menulinks.length; i += 1) {
    menulinks[i].addEventListener('change', updateMenu, false);
}

let gitPull = function (event) {
    event.preventDefault();
    let url = '?option=com_jdocmanual&task=manuals.gitpull&manual=' + this.id + '&language=' + this.value;
    location = url;
}

let plinks = document.querySelectorAll('.gitpull');
for (let i = 0; i < plinks.length; i += 1) {
    plinks[i].addEventListener('change', gitPull, false);
}

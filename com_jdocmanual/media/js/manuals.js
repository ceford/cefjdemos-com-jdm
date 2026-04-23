

/**
 * After page load listen for a change of published state
 */
document.addEventListener('DOMContentLoaded', function () {
    let links = document.querySelectorAll('[id^="jdm-manual-"]');
    for (let i = 0; i < links.length; i += 1) {
        links[i].addEventListener('click', toggleManual, false);
    }
});

/**
 * Select a default Home manual
 */
document.addEventListener('DOMContentLoaded', function () {
    let links = document.querySelectorAll('[id^="jdm-default-"]');
    for (let i = 0; i < links.length; i += 1) {
        links[i].addEventListener('click', setDefault, false);
    }
});

/**
 * Toggle yes/no whether to use an installed language in JDM
 */
function setDefault()
{
    const token = Joomla.getOptions('csrf.token', '');

    // The id of the Manual
    const manual_id = this.getAttribute('data-manual-id')

    const url = '?option=com_jdocmanual&task=manuals.setdefault&manual_id=' + manual_id;

    window.location.replace(url);
}


/**
 * Toggle yes/no whether to use an installed language in JDM
 */
async function toggleManual()
{
    const token = Joomla.getOptions('csrf.token', '');
    let url = '?option=com_jdocmanual&task=manuals.toggle';
    let manual_name = this.getAttribute('data-manual-name');

    // The id of the icon making the request.
    let icon_id = this.getAttribute('id');

    // The id of the Manual
    let manual_id = this.getAttribute('data-manual-id')
    
    // The id of the Build language selector
    let build_manual = document.querySelector('.data-build-name-' + manual_name);
    
    // The id of the Fetch language selector
    let fetch_manual = document.querySelector('.data-fetch-name-' + manual_name);

    // The id of the Build Menu language selector
    let build_menu = document.querySelector('.data-build-menu-' + manual_name);

    let data = new URLSearchParams();
    data.append(token, 1);
    data.append('manual_id', manual_id);
    const options = {
        method: 'POST',
        body: data
    }
    let response = await fetch(url, options);
    if (!response.ok) {
        throw new Error(Joomla.Text._('COM_JDOCMANUAL_JS_ERROR_STATUS') + `${response.status}`);
    } else {
        let result = await response.json();
        data = JSON.parse(result);

        // Select the inner span of the Published icon
        const innerSpan = document.querySelector('#' + icon_id + ' span');

        if (data['result'] === 'No') {
            // Toggle the classes
            innerSpan.classList.remove('icon-publish');
            innerSpan.classList.add('icon-unpublish');
            build_manual.classList.add('d-none');
            build_menu.classList.add('d-none');
            if (fetch_manual) {
                fetch_manual.classList.add('d-none');
            }
        } else {
            // Toggle the classes
            innerSpan.classList.remove('icon-unpublish');
            innerSpan.classList.add('icon-publish');
            build_manual.classList.remove('d-none');
            build_menu.classList.remove('d-none');
            if (fetch_manual) {
                fetch_manual.classList.remove('d-none');
            }
        }
    }
}

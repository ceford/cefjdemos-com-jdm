

/**
 * After page load set the active menu and open its accordion panel.
 */
document.addEventListener('DOMContentLoaded', function () {
    let links = document.querySelectorAll('[id^="jdm-language-"]');
    for (let i = 0; i < links.length; i += 1) {
        links[i].addEventListener('click', toggleLanguage, false);
    }
});

/**
 * Toggle yes/no whether to use an installed language in JDM
 */
async function toggleLanguage()
{
    const token = Joomla.getOptions('csrf.token', '');
    let url = '?option=com_jdocmanual&task=languages.toggle';
    let lang_id = this.getAttribute('data-lang-id');
    // The id of the icon making the request.
    let icon_id = this.getAttribute('id');

    let data = new URLSearchParams();
    data.append(token, 1);
    data.append('lang_id', lang_id);
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

        // Select the inner span
        const innerSpan = document.querySelector('#' + icon_id + ' span');
        if (data['result'] === 'No') {
            // Toggle the classes
            innerSpan.classList.remove('icon-publish');
            innerSpan.classList.add('icon-unpublish');
        } else {
            // Toggle the classes
            innerSpan.classList.remove('icon-unpublish');
            innerSpan.classList.add('icon-publish');
        }
    }
}

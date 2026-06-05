/**
 * For a new article, create the source_url and path from the Display title
 */
if (document.getElementById('jform_page_id')) {
    let pageid = document.getElementById('jform_page_id').value;

if (pageid === '0') {
    let displaytitle = document.getElementById('jform_title');
    displaytitle.readOnly = false;
    let sourceurl = document.getElementById('jform_source_url');
    let manual = document.getElementById('jform_manual');
    let path = document.getElementById('jform_path');
    path.readOnly = false;
    let prefix = 'jdocmanual?article=';

    displaytitle.addEventListener('change', function () {
        // If the jform_page_id is not 0 do not change the source_url.

        // Set the source_url from the display title.
        displaytitle.value = displaytitle.value.trim();
        // Space replaced by underline.
        sourceurl.value = displaytitle.value.replaceAll(' ', '_');

        // Lower case.
        let tmp = sourceurl.value.toLowerCase();
        // Replace non alpha-numeric with dash
        tmp = tmp.replaceAll(/[^a-z0-9]/gi, '-');
        // Replace multiple dashes with one dash
        tmp = tmp.replaceAll(/-{2,}/gi, '');
        // Replace leading or ending dashes
        tmp = tmp.replace(/^-/, '');
        tmp = tmp.replace(/-$/, '');
        path.value = tmp;
        sourceurl.value = prefix + manual.value +  '/' + path.value;
    });
}
}

/**
 * Check that there is a commit message before allowing commit.
 */

let msg = document.getElementById('jform_commit_message');
let commit = document.getElementsByClassName('button-gfm-commit');
if (commit) {
    for (let i = 0; i < commit.length; i++) {
        commit[i].addEventListener('click', committer, false);
    }
}

function committer(event)
{
    // Get the length of the commit message.
    if (msg.value.length < 25) {
        alert('The Commit Message is missing or too short!');
        return false;
    } else {
        // form.submit();
        if (confirm('This action will commit and merge the changes in this PR.')) {
            let form = document.getElementById('adminForm');
            let task = document.getElementById('task');
            task.value = document.getElementById('toolbar-gfm-commit').getAttribute('task');
            form.submit();
            return true;
        }
        return false;
    }
}

// Select all links in the stash menu and disable them
document.querySelectorAll('a[href*="jdocmanual"]').forEach(link => {
    link.addEventListener('click', function(event) {
        event.preventDefault(); 
        console.log('Interpreted click on: ' + this.id);
        // You can add logic here to show "Success" or "Clicked"
    });
});
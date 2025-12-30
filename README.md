## Installation Notes

Jdocmanual is designed to deliver Joomla documentation in an easy to use
reference form. It can also be used for other custom documentation.

Jdocmanual consists of two parts:

* The Jdocmanual component code and an optional plugins to provide Search
functionality and CLI access.
* Data files for each available Manual and Language. There are four manuals and
  eight languages available. You only need one to get started but the process
  is so easy you may as well get the English data for all manuals.

Version 4 of Jdocmanual is not compatible with previous versions. Any previous
version should have the `#__jdm_articles` and `#__jdm_menus` tables emptied.

## Jdocmanual Installation

Install and enable Jdocmanual as you would any other Joomla extension. It can
be obtained from:

https://github.com/ceford/cefjdemos-com-jdm

Select the green **Code** button and then the **Download ZIP** item. Save the
ZIP file in your Downloads directory. In your Joomla installation select
System > Install > Extensions and then select the **Upload Package File** tab.

You may need two plugins:

* A finder plugin ([cefjdemos-plg-jdm-finder](https://github.com/ceford/cefjdemos-plg-finder-jdocmanual/tree/main))
  is used to implement Smart Search for Jdocmanual.
* A CLI plugin ([Jdocmanualcli](https://github.com/ceford/cefjdemos-plg-jdocmanualcli))
  is used to run data installation commands from the command line. It may be
  useful if you wish to automate updates with cron jobs.

Install and enable the plugins.

## Data Files

The data files should be located in your filespace but outside your
website tree. The parent directory must be named **Manuals**. This is
a suggested Linux structure:

```bash
/home/username/manuals/developer/
/home/username/manuals/docs/
/home/username/manuals/help/
/home/username/manuals/user/
/home/username/public_html (where your Joomla website is located)
```

Obtain the English data files for each manual from the GitHub. You can
download ZIP files or use git clone.

### Git Clone

This is a set of commands to use for git clone for English and German:

```bash
    cd /home/username
    mdkdir manuals
    cd manuals
    mkdir developer
    mkdir docs
    mkdir help
    mkdir user

    cd help

    git clone https://github.com/ceford/cefjdemos-data-jdm-help-en
    mv cefjdemos-data-jdm-help-en en
    git clone https://github.com/ceford/cefjdemos-data-jdm-help-de
    mv cefjdemos-data-jdm-help-de de

    cd ../developer
    git clone https://github.com/ceford/cefjdemos-data-jdm-developer-en
    mv cefjdemos-data-jdm-developer-en en
    git clone https://github.com/ceford/cefjdemos-data-jdm-developer-de
    mv cefjdemos-data-jdm-developer-de de

    cd ../docs
    git clone https://github.com/ceford/cefjdemos-data-jdm-docs-en
    mv cefjdemos-data-jdm-docs-en en

    cd ../user
    git clone https://github.com/ceford/cefjdemos-data-jdm-user-en
    mv cefjdemos-data-jdm-user-en en
    git clone https://github.com/ceford/cefjdemos-data-jdm-user-de
    mv cefjdemos-data-jdm-user-de de
```

### Download ZIP

These are the links to download ZIP files in English:

* https://github.com/ceford/cefjdemos-data-jdm-developer-en
* https://github.com/ceford/cefjdemos-data-jdm-docs-en
* https://github.com/ceford/cefjdemos-data-jdm-help-en
* https://github.com/ceford/cefjdemos-data-jdm-user-en

Unpack each in its appropriate directory. For example, unpack the .../user/en
data set in the manuals/user/ directory. You will get a folder with the same
name as the repository

**Change the name of the folder** to the **language** abbreviation. You
should end up with a directory structure like this:

```bash
/home/username/manuals/user/en/articles/
/home/username/manuals/user/en/images/
/home/username/manuals/user/en/menus-index.txt
```

If you add data for another language it will follow the same pattern, except
that the menus-index.txt file is only present in the **/en/** folder.

## Jdocmanual Configuration

From the Administrator menu open the Jdocmanual > Manual page. Until the
component is properly configured you will see this **Installation Notes**
page.

* Select the **Options** button in the Toolbar.
* In the **Settings** tab:
  - in the **Markdown Source** field enter the full path to the data files,
  ending in **manuals/**. For example:

```bash
/home/username/manuals/
```

* If your website is in a sub-directory then enter the sub-directory name
  with a leading slash, for example /jdm4. Otherwise leave this field empty.
* If you are using clones of the original git sources and would like to pull
  updates from time time you cn set **Enable Pull** to *Yes*. This is used
  in the *Sources* page of the component menu.
* **Enable Like or Dislike buttons** These are Thumb up and Thumb down buttons
  at the bottom of every page. An experimental feature to be enabled or disabled
  as reuired.
* **Enable Comments form** the central button allows submission of comments
  without using Thumb up or Thumb down.
* **Save & Close**

## Enable Languages

The language packs for the languages you intend to use should be installed via
the **System/Languages** page. After installation the languages need to be
enabled via the **System/Content Languages** page.

Jdocmanual has its own page to enable the languages you would like to use.
Select the **Components -> Jdocmanual -> Languages** item from the Administrator
menu and edit any that need to be enabled. Do not change anything else. This
form is really intended for users who would like to add a language not in the
Jdocmanual sources.

## Enable Manuals and Build Articles and Menus

The four Manuals available for Jdocmanual are available as separate datasets
for each language. The source data is in Markdown format which is converted to
HTML during the build process. The procedure for each manual is the same as
this example for the User Manual:

* Select **Components > Manuals > Sources** from the Administrator menu.
* Select the *Jooomla User Manual* to open its Edit page.
* Change the *Status* to **Published** and **Save**
* In the *Sources* page the table of manuals has a language selector in the
  row for each language. Select a language to build the database tables for
  that manual and language. The first time this is done the process may take
  a long time and the only indicator is the busy icon in the browser tab. Be
  patient for 5 to 10 minutes or so. Jdocmanual will build the articles and
  menus in the selected language and it will create responsive images in a
  variety of sizes. There will be a summary message, which may include problem
  reports. On the next invocation the build process will be much quicker as
  only changed pages are rebuilt.
* For the *Help* Manual only, the build process also builds the proxy server
  used to deliver help pages. This is very quick and is done for all installed
  languages. To use it, edit your configuration.php and change the `$helpurl`
  domain name to that of this Joomla installation.

## Test

* Select **Components > Manuals > Manuals** from the Administrator menu.

You should see the first page of the User Manual.

## Maintenance

From time to time there will be updates to the Jdocmanual data files. You
can download the source files and repeat the data installation process. This is
very easy to do with **git**: just do `git pull` in the appropriate language
folder, then use the Build selector in the Sources page or switch to the *cli*
folder and run the cli commands. You can build a cron job to do this.

## Build Articles and Menus - Command Line Method

The database jdm_articles and jdm_menus tables are populated by reading
the data files. This can take a long time - at least a few minutes
and perhaps much longer on slow hosts.

Proceed as follows:

1.  Open a terminal window and cd into the cli folder within your Joomla
    installation root. Here is an example:<br>
    `cd /home/username/public_html/optional-subfolder/cli`
2.  Issue the command to convert data from the markdown source files to
    html in the #__jdm_articles table of the database:<br>
    `php joomla.php jdocmanual:action buildarticles user en`
3.  Issue the command to create the menus:<br>
    `php joomla.php jdocmanual:action buildmenus user en`

In these commands, **user** is the name of the manual to be processed and
**en** is a language specifier.

The Markdown format is converted to HTML and stored in the database. Also,
if there are any images in the data source a set of responsive images
is created.

If you encounter **out of memory** problems or **out of time** problems run the
command again.

## Database Population - Cron Method

If you do not have access to the command line, common with shared
hosting, you should be able to call these commands from a cron job. Make
sure you give the full path to the php executable, example:
`/usr/local/opt/php@8.1/bin/php`. You need to find out where the php
executable is located on your host operating system.

But try building from the Sources page first!

## Site Menu

If you want to show the Manuals on the site just create a Jdocmanual
menu item.

**Important:** The menu alias must be **jdocmanual** or internal links
will be broken and lead to frontend problems.

You may wish to place the menu on a page without side modules so that
the full width of the page may be used for content. If so, go to each
Site Module that appears in the left or right side positions and set their
Menu Assignment / Module Assignment to *On all pages except those selected*
and select all instances of Jdocmanual.

### Menu Items

Each language will require its own menu and a new menu item for Jdocmanual.
Give each a name such as *Jdocmanual en-GB*, *Jdocmanual de-DE* and so on
and set the Language value to that for the menu. Optionally, you could make
Jdocmanual the Home page in each selected language.

You will need a Main Menu that also has a link to Jdocmanual but has Languages
set All. Again that could be the Home page for *All* languages.

### Multilingual Sites

The language selected to show the index of pages in Jdocmanual is independent
of the current page language. This feature is useful for maintenance purposes.

The language selected to show the page content is linked to the overall
current site language. It acts as a Language Switcher but is not itself
affected by a language switcher module (so don't show one?).

## Smart Search

There are a number of different ways to set up Smart Search. This is the
solution adopted for the Jdocmanual site.

### Banner Creation

First remove the Template site banner. Go to **System -> Site Template Styles**.
Select **Cassiopeia - Default** and in the **Advanced** tab set *Brand* to *No*.
Then **Save & Close**. The site will have lost its default Cassiopeia banner.

The replacement top banner consists of two modules assigned to the below-top
position:

#### Custom Module for Site Name

This custom module has the following content (add *Local* or *Test* to site-logo
if this is a local test site):

```
<div class="navbar-brand">
<div class="site-logo"><a href="jdocmanual">Jdocmanual</a></div>
<div class="site-description">A site for Joomla! documentation</div>
</div>
```

- Set **Title** to *Hide*.
- Enter **flex-grow-1** in the *Advanced* tab, *Module Class* field.
- Set **Module Style** to *noCard*.

There are some CSS statements to position the Search form to the right and
centre it vertically in the top bar. Create a `user.css` file in your
template and add the following style statements:

```
.site-logo {
    font-weight: 700;
}

.site-logo a {
    text-decoration: none !important;
}

.site-description {
    margin-left: 0rem;
}

.container-below-top {
    align-items: center !important;
}
```

#### Smart Search Module

The Smart Search form has the following settings:
- **Title** set to *Hide*
- Set **Search Field Label** to *Hide*.
- Set **Menu Assignment** to *Only on the pages selected* with the Jdocmanual
  page selected for each Menu.
- **Advanced** Set *Module Style* to *noCard*.

#### No Menu Item

A menu item for results is not needed.

## User Groups

If you wish to allow others to help maintain content this is one way to do it
with two User Groups:

- **JDM Author**: allowed to edit content in English and other
languages.
- **JDM Publisher**: allowed to commit and publish
updated content.

The **JDM Author** group should have Public as its parent. The
**JDM Publisher** group should have **JDM Author** as its
parent.

### Users: Viewing Access Levels

**JDM Author** should be set to the Special Viewing Access level.
From the Users / Access Levels page select `Special` and add `JDM
Author` to the User Groups with Viewing Access.

### Global Options

In the Global Options form select the Permissions tab and then the
**JDM Author** item. Set Administrator login to Allowed.

### Jdocmanual Options

From the JDOC Manual, Manual page select the Options button. In the
Permissions list select **JDM Author** and set the following to
Allowed: - Access Administration Interface - Create - Delete - Edit

Select **JDM Publisher** and set the following to Allowed: - Publish

Save and Close

If you now login as a user in the **JDM Author** group you will see
the Home Dashboard with some modules not relevant for Jdocmanual.

### Turn off the Help menu item

Go to the list of Administrator modules and find the Administrator Menu
module. In the Module tab set the Help Menu item to Hide.

### Unpublish or restrict Access to modules

In the Administrator modules list find the Logged-in Users item used in
the cpanel position (not the cpanel-users position). Either Unpublish it
or set Access to Super Users.

Find and unpublish the Popular Articles and Recently Added Articles
items for the cpanel position (not the cpanel-content position).

There may be other modules needing similar treatment.

The Home Dashboard should now be empty for a **JDM Author**.

## Who can do what?

**JDM Author** and **JDM Publisher** can login but should only
have access to the the Home Dashboard and the Jdocmanual component.

**JDM Author** does not have access to the Commit button in the
Article Edit page toolbar so cannot update the git repository or
displayed page. Otherwise each can use all other features.

**Manager** and **Administrator** can login but will not see the
Jdocmanual component.

**Author**, **Editor** and **Publisher** do not have access
to the Administrator interface.

**Super Users** have complete access.

## Some Reminders

The System - Joomla Accessibility Checker flags the anchors
as having empty links. So comment out line 70 of administrator/components/com_jdocmanual/libraries/vendor/league/commonmark/src/Extension/HeadingPermalink/HeadingPermalinkRenderer.php

```php
        //$attrs->set('href', '#' . $fragmentPrefix . $slug);
        $attrs->append('class', $this->config->get('heading_permalink/html_class'));
```

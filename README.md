## Installation Notes

Jdocmanual is designed to display documentation with a list of articles to the left, article content in the centre and article index to the right. This layout is used by many software projects.

**Version 5** of Jdocmanual is a major revision. It is not backwards compatible with previous versions. The data file structure has been revised too. 

The Jdocmanual software has several parts:

- The [Jdocmanual](https://github.com/ceford/cefjdemos-com-jdm) component code.
- A [Smart Search](https://github.com/ceford/cefjdemos-plg-finder-jdocmanual/tree/main) plugin
- A [System](https://github.com/ceford/cefjdemos-plg-jdocmanualcli) plugin to allow some command line operations.
- Data files in Markdown format, each set constituting a Manual.

To install Jdocmanual in a working Joomla installtion:

- In the Github repository, select the green **Code** button and then the **Download ZIP** item. 
- Save the ZIP file in your Downloads directory. 
- In your Joomla installation select System > Install > Extensions and then select the **Upload Package File** tab.
- Selected the just downloaded zip file.

Install and enable the Jdocmanual plugins as you would any other Joomla extension.

## Data Files

The data files should be located in your filespace but outside your website tree. Suggested procedure:

- Create a folder, for example `/home/username/manuals` (Linux) or `/Users/username/manuals` (Mac).
- Download the Jdocmanual data to experiment with:
    - Either as a zip file
    - Or as a clone of  
    
## Configuration Values

Jdocmanual configuration:

- Markdown source: `/home/username/manuals/`

Manual configuration:

- Manual folder: jdm
- Default language: en
- Initial path: introduction
    
Further information is available at the [Jdocmanual](https://jdocmanual.org/jdocmanual?article=jdm/introduction) demonstration site.

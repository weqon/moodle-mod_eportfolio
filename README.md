# ePortfolio Grading #

This plugin is part of the ePortfolio collection and requires local_eportfolio to work.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/mod/eportfolio

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## Relase notes ##

### Moodle 4.5 ###

**Version 0.3.1**

- Set maturity to stable
- Added new feature feedback file upload.
  - The feedback type can be selected in the activity settings

**Version 0.3.0**

- Set supported Moodle version to 4.5
- Bug fix date output for graded ePortfolio

**Version 0.2.4**

- Fixed incorrect language string key in index.php
- Added missing capability check in view.php

**Version 0.2.3**

- Removed check for existing activity
  - There can now be more than one activity per course
- The activity can now be saved, restored and duplicated

**Version 0.2.2**

- Fixed naming collisions
- Added missing translation
- Removed deprecated constants from message provider
- Reworking the privacy provider
- Optimized DB queries

**Version 0.2.1**

- Added backup and restore classes

**Version 0.2.0**

- Added adhoc task for sending notifications
- Added privacy provider class
- Complete code overhaul and refactoring

## License ##

2024 weQon UG <support@weqon.net>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.

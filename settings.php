<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin administration pages are defined here.
 *
 * @package     mod_eportfolio
 * @category    admin
 * @copyright   2025 weQon UG
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @var $ADMIN
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

if ($hassiteconfig) {

    $settings = new admin_category('mod_eportfolio_settings', new lang_string('pluginname', 'mod_eportfolio'));

    $settingspage = new admin_settingpage('mod_eportfolio_general',
            get_string('settings:general', 'mod_eportfolio'));

    // Set max upload size.
    $choices = get_max_upload_sizes($CFG->maxbytes);

    $settingspage->add(
            new admin_setting_configselect(
                    'mod_eportfolio/maxbytes',
                    get_string('settings:maxuploadfilezise', 'mod_eportfolio'),
                    get_string('settings:maxuploadfilezise:desc', 'mod_eportfolio'),
                    0,
                    $choices
            )
    );

    $settings->add('mod_eportfolio_settings', $settingspage);
}

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
 * Defines backup_eportfolio_activity_task class
 *
 * @package     mod_eportfolio
 * @category    backup
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/eportfolio/backup/moodle2/backup_eportfolio_stepslib.php');

/**
 * Define the complete ePortfolio structure for backup
 *
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_eportfolio_activity_task extends backup_activity_task {

    /**
     * No specific settings for this activity
     */
    protected function define_my_settings() {
        // No specific settings for this activity.
    }

    /**
     * Defines a backup step to store the instance data in the eportfolio.xml file
     */
    protected function define_my_steps() {
        // Add the step to back up eportfolio activity.
        $this->add_step(new backup_eportfolio_activity_structure_step('eportfolio_structure', 'eportfolio.xml'));
    }

    /**
     * Encodes URLs to the index.php and view.php scripts
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts
     * @return string the content with the URLs encoded
     */
    public static function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        // Link to adleteh5p index by course id.
        $search = "/(" . $base . "\/mod\/eportfolio\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@EPORTFOLIOINDEX*$2@$', $content);

        // Link to adleteh5p view by module id.
        $search = "/(" . $base . "\/mod\/eportfolio\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@EPORTFOLIOVIEWBYID*$2@$', $content);

        return $content;
    }
}

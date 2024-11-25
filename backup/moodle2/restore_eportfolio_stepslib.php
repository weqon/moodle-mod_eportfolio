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
 * Restore structure step for ePortfolio content
 *
 * @package     mod_eportfolio
 * @category    backup
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Structure step to restore one ePortfolio activity
 *
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_eportfolio_activity_structure_step extends restore_activity_structure_step {

    /**
     * Defines restore element's structure
     *
     * @return array
     * @throws base_step_exception
     */
    protected function define_structure() {

        $paths = [];

        // Restore activities.
        $paths[] = new restore_path_element('eportfolio', '/activity/eportfolio');

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process ePortfolio, inserting the record into the database.
     *
     * @param object $data
     * @return void
     *
     * @throws base_step_exception
     * @throws dml_exception
     */
    protected function process_eportfolio($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->get_courseid();
        $data->timemodified = time();
        $data->timecreated = time();

        // Insert the ePortfolio record.
        $newitemid = $DB->insert_record('eportfolio', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * No specific steps for this activity.
     */
    protected function after_execute() {
        // No specific steps for this activity.
    }
}

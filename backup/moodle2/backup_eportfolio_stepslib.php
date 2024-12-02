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
 * Defines backup structure steps for ePortfolio content.
 *
 * @package     mod_eportfolio
 * @category    backup
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define the complete ePortfolio structure for backup, with file and id annotations
 *
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_eportfolio_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines backup element's structure
     *
     * @return backup_nested_element
     * @throws base_element_struct_exception
     * @throws base_step_exception
     */
    protected function define_structure() {

        // We won't include any user data, since an eportfolio is always shared for grading with a specific course.

        // Define root element for the activity.
        $eportfolio = new backup_nested_element('eportfolio', ['id'],
                ['course', 'name', 'intro', 'introformat', 'timemodified']);

        // Add the tables to the structure.
        // Mono tree.

        // Define sources.
        $eportfolio->set_source_table('eportfolio', ['id' => backup::VAR_ACTIVITYID]);

        return $this->prepare_activity_structure($eportfolio);
    }
}

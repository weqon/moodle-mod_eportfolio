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
 * Contains the class for fetching the important dates in mod_eportfolio for a given module instance and a user.
 *
 * @package     mod_eportfolio
 * @copyright   2025 weQon UG <support@weqon.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_eportfolio;

use core\activity_dates;

/**
 * Class for fetching the important dates in mod_eportfolio for a given module instance and a user.
 *
 * @copyright   2025 weQon UG <support@weqon.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dates extends activity_dates {

    /**
     * Returns a list of important dates in mod_eportfolio
     *
     * @return array
     */
    protected function get_dates(): array {

        $course = get_course($this->cm->course);

        $timeopen = $this->cm->customdata['allowsubmissionsfromdate'] ?? null;
        $timedue = $this->cm->customdata['submissionduedate'] ?? null;

        $now = time();
        $dates = [];

        if ($timeopen) {
            $openlabelid = $timeopen > $now ? 'activitydate:submissionsopen' : 'activitydate:submissionsopened';
            $date = [
                    'dataid' => 'allowsubmissionsfromdate',
                    'label' => get_string($openlabelid, 'mod_eportfolio'),
                    'timestamp' => (int) $timeopen,
            ];
            if ($course->relativedatesmode) {
                $date['relativeto'] = $course->startdate;
            }
            $dates[] = $date;
        }

        if ($timedue) {
            $date = [
                    'dataid' => 'submissionduedate',
                    'label' => get_string('activitydate:submissionsdue', 'mod_eportfolio'),
                    'timestamp' => (int) $timedue,
            ];
            if ($course->relativedatesmode) {
                $date['relativeto'] = $course->startdate;
            }
            $dates[] = $date;
        }

        return $dates;
    }
}

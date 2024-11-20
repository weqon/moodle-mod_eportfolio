<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Prints an instance of mod_eportfolio.
 *
 * @package     mod_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once('locallib.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$e = optional_param('e', 0, PARAM_INT);

$eportid = optional_param('eportid', 0, PARAM_INT);

$tsort = optional_param('tsort', '', PARAM_ALPHA);
$tdir = optional_param('tdir', 0, PARAM_INT);

// We need this in case an ePortfolio will be deleted.
$confirm = optional_param('confirm', '', PARAM_ALPHANUM);

if ($id) {
    $cm = get_coursemodule_from_id('eportfolio', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('eportfolio', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('eportfolio', ['id' => $e], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('eportfolio', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$params = [
        'id' => $cm->id,
];

if ($tsort || $tdir) {
    $params['tsort'] = $tsort;
    $params['tdir'] = $tdir;
}

$url = new moodle_url('/mod/eportfolio/view.php', $params);

$event = \mod_eportfolio\event\course_module_viewed::create([
        'objectid' => $moduleinstance->id,
        'context' => $modulecontext,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('eportfolio', $moduleinstance);
$event->trigger();

$PAGE->set_url($url);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();

// Check if this course is marked as eportfolio course.
if (check_current_eportfolio_course($course->id)) {
    // Also check, if the assigned roles in local_eportfolio have the right capabilities.
    // ToDo: check_role_capability();.

    // Generate table with all eportfolios shared for grading for this course.
    eportfolio_render_overview_table($course->id, $cm->id, $url, $tsort, $tdir);

} else {
    // This course is not marked as ePortfolio course.
    $data = new stdClass();
    echo $OUTPUT->render_from_template('mod_eportfolio/noeportfolio_course', $data);
}

echo $OUTPUT->footer();

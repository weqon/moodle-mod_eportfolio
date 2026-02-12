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
 * Grade for mod_eportfolio.
 *
 * @package     mod_eportfolio
 * @copyright   2026 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT); // course module id
$userid = optional_param('userid', 0, PARAM_INT);

$cm = get_coursemodule_from_id('eportfolio', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$modeportfolio  = $DB->get_record('eportfolio', ['id' => $cm->instance], '*', MUST_EXIST);
$eport = $DB->get_record('local_eportfolio_share', ['cmid' => $cm->id, 'usermodified' => $userid], '*', MUST_EXIST);

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/eportfolio:grade', $context);

// Ziel: Bewertungsseite deiner Aktivität
$url = new moodle_url('/mod/eportfolio/grading.php', [
        'id' => $cm->id,
        'eportid' => $eport->id,
        'userid' => $userid,
]);

redirect($url);

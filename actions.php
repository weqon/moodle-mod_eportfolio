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
 * Used for all actions related to ePortfolio file.
 *
 * @package     mod_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('locallib.php');

$id = required_param('id', PARAM_INT);  // Course Module.
$eportid = required_param('eportid', PARAM_INT);  // ID eport.
$action = required_param('action', PARAM_ALPHA);
$sesskey = required_param('sesskey', PARAM_ALPHANUM);

$cm = get_coursemodule_from_id('eportfolio', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$moduleinstance = $DB->get_record('eportfolio', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

if (!has_capability('mod/eportfolio:grade_eport', $modulecontext)) {
    redirect(new moodle_url('/mod/eportfolio/view.php', ['id' => $id]),
            get_string('error:missingcapability:actions', 'mod_eportfolio'),
            null, \core\output\notification::NOTIFY_ERROR);
}

require_sesskey();

$urlparams = [
        'id' => $id,
        'eportid' => $eportid,
        'sesskey' => $sesskey,
        'action' => $action,
];

$url = new moodle_url('/mod/eportfolio/actions.php', $urlparams);

// Set page layout.
$PAGE->set_url($url);
$PAGE->set_context($modulecontext);
$PAGE->set_title(get_string('actions:header', 'mod_eportfolio'));
$PAGE->set_heading(get_string('actions:header', 'mod_eportfolio'));
$PAGE->set_pagelayout('base');
$PAGE->add_body_class('limitedwith');

$redirecturl = new moodle_url('/mod/eportfolio/view.php', ['id' => $id]);

if ($action === 'delete') {

    // First, get the record.
    $eport = $DB->get_record('local_eportfolio_share', ['id' => $eportid]);

    // Now delete the main file.
    $fs = get_file_storage();
    $file = $fs->get_file_by_id($eport->fileidcontext);

    if (!empty($file)) {

        // We use the pathnamehash to get the H5P file.
        $pathnamehash = $file->get_pathnamehash();

        $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $pathnamehash]);

        // If H5P, delete it from the H5P table as well.
        // Note: H5P will create an entry when the file was viewed for the first time.
        if (!empty($h5pfile)) {
            $DB->delete_records('h5p', ['id' => $h5pfile->id]);
            // Also delete from files where context = 1, itemid = H5P id component core_h5p, filearea content.
            $fs->delete_area_files('1', 'core_h5p', 'content', $h5pfile->id);
        }

        // Finally delete the selected file.
        $file->delete();

        // Check, if a grade exists.
        $gradeexists = $DB->get_record('eportfolio_grade',
                ['userid' => $eport->usermodified, 'fileidcontext' => $eport->fileidcontext, 'cmid' => $eport->cmid]);

        if (!empty($gradeexists)) {
            $DB->delete_records('eportfolio_grade', ['id' => $gradeexists->id]);
        }

        if ($DB->delete_records('local_eportfolio_share', ['id' => $eport->id])) {

            // Trigger event for withdrawing sharing of ePortfolio.
            $filename = '';
            if (!empty($eport->title)) {
                $filename = $eport->title;
            } else {
                $filename = $file->get_filename();
            }

            $event = \mod_eportfolio\event\deleted_eportfolio::create([
                    'objectid' => $eport->fileidcontext,
                    'context' => $modulecontext,
                    'other' => [
                            'description' => get_string('event:eportfolio:deleted', 'mod_eportfolio',
                                    ['userid' => $USER->id, 'filename' => $filename,
                                            'fileidcontext' => $eport->fileidcontext]),
                    ],
            ]);
            $event->add_record_snapshot('course', $course);
            $event->add_record_snapshot('eportfolio', $moduleinstance);
            $event->trigger();

            redirect($redirecturl, get_string('delete:success', 'local_eportfolio'),
                    null, \core\output\notification::NOTIFY_SUCCESS);

        } else {
            redirect($redirecturl, get_string('delete:error', 'local_eportfolio'),
                    null, \core\output\notification::NOTIFY_ERROR);

        }
    } else {
        redirect($redirecturl, get_string('delete:error', 'local_eportfolio'),
                null, \core\output\notification::NOTIFY_ERROR);
    }
}

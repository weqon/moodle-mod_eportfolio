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
require_once('classes/forms/grade_form.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);
$eportid = required_param('eportid', PARAM_INT);

$action = optional_param('action', 0, PARAM_ALPHA);

$cm = get_coursemodule_from_id('eportfolio', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$moduleinstance = $DB->get_record('eportfolio', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$params = [
        'id' => $cm->id,
];

$url = new moodle_url('/mod/eportfolio/view.php', $params);

$PAGE->set_url($url);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

// Check if this course is marked as eportfolio course.
if (check_current_eportfolio_course($course->id)) {

    // Check, if teacher or student is accessing this page.
    if (has_capability('mod/eportfolio:grade_eport', $modulecontext) || is_siteadmin()) {

        $eport = $DB->get_record('local_eportfolio_share', ['id' => $eportid]);

        // Shouldn't happen, but who knows.
        if (!empty($eport)) {

            // Check, if a grade exists.
            $gradeexists = $DB->get_record('eportfolio_grade',
                    ['userid' => $eport->usermodified, 'fileidcontext' => $eport->fileidcontext, 'cmid' => $cm->id]);

            $setdata = '';

            if (!empty($gradeexists)) {

                $setdata = [
                        'grade' => $gradeexists->grade,
                        'feedbacktext' => $gradeexists->feedbacktext,
                ];

            }

            $customdata = [
                    'eportid' => $eport->id,
                    'userid' => $eport->usermodified,
                    'cmid' => $cm->id,
                    'fileidcontext' => $eport->fileidcontext,
                    'courseid' => $course->id,
            ];

            $gradeurl = new moodle_url('/mod/eportfolio/grade.php', ['id' => $cm->id, 'eportid' => $eport->id]);

            $mform = new grade_form($gradeurl, $customdata);
            $mform->set_data($setdata);

            if ($formdata = $mform->is_cancelled()) {
                // Add cancelled text.
                redirect(new moodle_url('/mod/eportfolio/view.php', ['id' => $cm->id]),
                        get_string('grade:cancelled', 'mod_eportfolio'),
                        null, \core\output\notification::NOTIFY_WARNING);
            } else if ($formdata = $mform->get_data()) {

                // Get activity instance id from table eportfolio.
                $instanceid = $DB->get_record('eportfolio', ['course' => $course->id]);

                $data = new stdClass();

                $data->eportid = $formdata->eportid;
                $data->instance = $instanceid->id;
                $data->fileidcontext = $formdata->fileidcontext;
                $data->courseid = $formdata->courseid;
                $data->cmid = $formdata->cmid;
                $data->userid = $formdata->userid;
                $data->graderid = $USER->id;
                $data->grade = $formdata->grade;
                $data->feedbacktext = $formdata->feedbacktext;
                $data->usermodified = $USER->id;

                if (!empty($gradeexists)) {

                    $data->id = $gradeexists->id;
                    $data->timemodified = time();

                    if ($DB->update_record('eportfolio_grade', $data)) {

                        // Send message to inform user about new or updated grade.
                        $fs = get_file_storage();
                        $file = $fs->get_file_by_id($data->fileidcontext);

                        $filename = '';
                        if (!empty($file)) {
                            $filename = $file->get_filename();
                        }

                        $h5pfilename = get_h5p_title($data->fileidcontext);

                        if (!empty($eport->title)) {
                            $filename = $eport->title;
                        } else if (!empty($h5pfilename)) {
                            $filename = $h5pfilename;
                        }

                        // Prepare task data.
                        $task = new \mod_eportfolio\task\send_messages();

                        $taskdata = new stdClass();

                        $taskdata->courseid = $data->courseid;
                        $taskdata->cmid = $data->cmid;
                        $taskdata->userfrom = $data->graderid;
                        $taskdata->userto = $data->userid;
                        $taskdata->filename = $filename;
                        $taskdata->fileid = $data->fileidcontext;

                        $task->set_custom_data($taskdata);

                        // Queue the task.
                        \core\task\manager::queue_adhoc_task($task);

                        $event = \mod_eportfolio\event\grading_updated::create([
                                'objectid' => $moduleinstance->id,
                                'context' => $modulecontext,
                                'other' => [
                                        'description' => get_string('event:eportfolio:updatedgrade', 'mod_eportfolio',
                                                ['userid' => $USER->id, 'filename' => $filename,
                                                        'fileidcontext' => $eport->fileidcontext]),
                                ],
                        ]);
                        $event->add_record_snapshot('course', $course);
                        $event->add_record_snapshot('eportfolio', $moduleinstance);
                        $event->trigger();

                        redirect(new moodle_url('/mod/eportfolio/view.php', ['id' => $cm->id]),
                                get_string('grade:update:success', 'mod_eportfolio'),
                                null, \core\output\notification::NOTIFY_SUCCESS);

                    } else {

                        redirect(new moodle_url('/mod/eportfolio/view.php', ['id' => $cm->id]),
                                get_string('grade:update:error', 'mod_eportfolio'),
                                null, \core\output\notification::NOTIFY_ERROR);

                    }

                } else {

                    $data->timecreated = time();

                    if ($DB->insert_record('eportfolio_grade', $data)) {

                        // Send message to inform user about new or updated grade.
                        $fs = get_file_storage();
                        $file = $fs->get_file_by_id($data->fileidcontext);

                        $filename = '';
                        if (!empty($file)) {
                            $filename = $file->get_filename();
                        }

                        $h5pfilename = get_h5p_title($data->fileidcontext);

                        if (!empty($eport->title)) {
                            $filename = $eport->title;
                        } else if (!empty($h5pfilename)) {
                            $filename = $h5pfilename;
                        }

                        // Prepare task data.
                        $task = new \mod_eportfolio\task\send_messages();

                        $taskdata = new stdClass();

                        $taskdata->courseid = $data->courseid;
                        $taskdata->cmid = $data->cmid;
                        $taskdata->userfrom = $data->graderid;
                        $taskdata->userto = $data->userid;
                        $taskdata->filename = $filename;
                        $taskdata->fileid = $data->fileidcontext;

                        $task->set_custom_data($taskdata);

                        // Queue the task.
                        \core\task\manager::queue_adhoc_task($task);

                        $event = \mod_eportfolio\event\grading_updated::create([
                                'objectid' => $moduleinstance->id,
                                'context' => $modulecontext,
                                'other' => [
                                        'description' => get_string('event:eportfolio:updatedgrade', 'mod_eportfolio',
                                                ['userid' => $USER->id, 'filename' => $filename,
                                                        'fileidcontext' => $eport->fileidcontext]),
                                ],
                        ]);
                        $event->add_record_snapshot('course', $course);
                        $event->add_record_snapshot('eportfolio', $moduleinstance);
                        $event->trigger();

                        redirect(new moodle_url('/mod/eportfolio/view.php', ['id' => $cm->id]),
                                get_string('grade:insert:success', 'mod_eportfolio'),
                                null, \core\output\notification::NOTIFY_SUCCESS);

                    } else {

                        redirect(new moodle_url('/mod/eportfolio/view.php', ['id' => $cm->id]),
                                get_string('grade:insert:error', 'mod_eportfolio'),
                                null, \core\output\notification::NOTIFY_ERROR);

                    }

                }

            } else {

                // Convert display options to a valid object.
                $factory = new \core_h5p\factory();
                $core = $factory->get_core();
                $config = core_h5p\helper::decode_display_options($core, $modulecontext->id);

                $fs = get_file_storage();
                $file = $fs->get_file_by_id($eport->fileidcontext);

                if (!empty($file)) {

                    $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                            $file->get_filearea(), $file->get_itemid(), $file->get_filepath(),
                            $file->get_filename(), false);

                    // Get the user who shared the ePortfolio for grading.
                    $user = $DB->get_record('user', ['id' => $eport->usermodified]);

                    $data = new stdClass();

                    $data->userfullname = fullname($user);
                    $data->title = (!empty($eport->title)) ? $eport->title : get_h5p_title($eport->fileidcontext);
                    $data->backurl = $url;
                    $data->backurlstring = get_string('gradeform:backbtn', 'mod_eportfolio');
                    $data->timecreated = date('d.m.Y', $eport->timecreated);
                    $data->h5pplayer = \core_h5p\player::display($fileurl, $config, false, 'mod_eportfolio', false);
                    $data->gradeform = $mform->render();

                    $event = \mod_eportfolio\event\grading_viewed::create([
                            'objectid' => $moduleinstance->id,
                            'context' => $modulecontext,
                            'other' => [
                                    'description' => get_string('event:eportfolio:viewgrading', 'mod_eportfolio',
                                            ['userid' => $USER->id, 'filename' => $data->title,
                                                    'fileidcontext' => $eport->fileidcontext]),
                            ],
                    ]);
                    $event->add_record_snapshot('course', $course);
                    $event->add_record_snapshot('eportfolio', $moduleinstance);
                    $event->trigger();

                    echo $OUTPUT->header();
                    echo $OUTPUT->render_from_template('mod_eportfolio/grade_eportfolio', $data);
                    echo $OUTPUT->footer();
                }

            }

        }

    } else {
        // User is directly accessing the grade results from local_eportfolio or block_eportfolio.
        $eport = $DB->get_record('local_eportfolio_share', ['id' => $eportid]);

        // Shouldn't happen, but who knows.
        if (!empty($eport)) {

            // Convert display options to a valid object.
            $factory = new \core_h5p\factory();
            $core = $factory->get_core();
            $config = core_h5p\helper::decode_display_options($core, $modulecontext->id);

            $fs = get_file_storage();
            $file = $fs->get_file_by_id($eport->fileidcontext);

            if (!empty($file)) {

                $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                        $file->get_filearea(), $file->get_itemid(), $file->get_filepath(),
                        $file->get_filename(), false);

                // Get the user who shared the ePortfolio for grading.
                $user = $DB->get_record('user', ['id' => $eport->usermodified]);

                $data = new stdClass();

                $data->userfullname = fullname($user);
                $data->title = (!empty($eport->title)) ? $eport->title : get_h5p_title($eport->fileidcontext);
                $data->backurl = $url;
                $data->backurlstring = get_string('gradeform:backbtn', 'mod_eportfolio');
                $data->timecreated = date('d.m.Y', $eport->timecreated);
                $data->h5pplayer = \core_h5p\player::display($fileurl, $config, false, 'mod_eportfolio', false);

                $params = [
                        'courseid' => $eport->courseid,
                        'cmid' => $eport->cmid,
                        'fileidcontext' => $eport->fileidcontext,
                ];

                $getgrade = $DB->get_record('eportfolio_grade', $params);

                $data->grade = './.';
                $data->gradetext = './.';
                $data->grader = './.';

                if (!empty($getgrade->graderid)) {
                    $grader = $DB->get_record('user', ['id' => $getgrade->graderid]);

                    if (!empty($grader)) {
                        $data->grade = $getgrade->grade . ' %';
                        $data->gradetext = format_text($getgrade->feedbacktext);
                        $data->grader = fullname($grader);
                    }
                }

                $event = \mod_eportfolio\event\grading_viewed::create([
                        'objectid' => $moduleinstance->id,
                        'context' => $modulecontext,
                        'other' => [
                                'description' => get_string('event:eportfolio:viewgrading', 'mod_eportfolio',
                                        ['userid' => $USER->id, 'filename' => $data->title,
                                                'fileidcontext' => $eport->fileidcontext]),
                        ],
                ]);
                $event->add_record_snapshot('course', $course);
                $event->add_record_snapshot('eportfolio', $moduleinstance);
                $event->trigger();

                echo $OUTPUT->header();
                echo $OUTPUT->render_from_template('mod_eportfolio/view_eportfolio', $data);
                echo $OUTPUT->footer();
            } else {
                $data = new stdClass();
                echo $OUTPUT->header();
                echo $OUTPUT->render_from_template('mod_eportfolio/noeportfolio_file_found', $data);
                echo $OUTPUT->footer();
            }

        }

    }
}

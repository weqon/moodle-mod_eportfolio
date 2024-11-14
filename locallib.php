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
 * Locallib eportfolio
 *
 * @package mod_eportfolio
 * @copyright 2023 weQon UG {@link https://weqon.net}
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/eportfolio/locallib.php');
require_once($CFG->libdir . '/tablelib.php');

/**
 * Check, if the current course is marked as ePortfolio course.
 *
 * @param $courseid
 * @return bool
 */
function check_current_eportfolio_course($courseid) {
    global $DB, $USER;

    // Get the field id to identify the custm field data.
    $customfield = $DB->get_record('customfield_field', ['shortname' => 'eportfolio_course']);

    // Get the value for custom field id.
    $customfielddata = $DB->get_records('customfield_data', ['fieldid' => $customfield->id]);

    foreach ($customfielddata as $cd) {

        // True, if eportfolio course.
        if ($cd->value == '1') {

            if ($cd->instanceid === $courseid) {
                return true;
            }
        }
    }

    return false;

}

/**
 * Prepare the overview table display.
 *
 * @param $courseid
 * @param $cmid
 * @param $url
 * @param $tsort
 * @param $tdir
 * @return void
 */
function eportfolio_render_overview_table($courseid, $cmid, $url, $tsort = '', $tdir = '') {
    global $DB, $USER;

    $coursecontext = context_course::instance($courseid);
    $coursemodulecontext = context_module::instance($cmid);

    $actionsallowed = false;

    $actionbtn = '';
    $deletebtn = '';

    // First we have to check, if current user is editingteacher.
    if (is_enrolled($coursecontext, $USER, 'mod/eportfolio:grade_eport')) {

        $actionsallowed = true;

        $entry = get_shared_eportfolios('grade', $courseid, $tsort, $tdir);

    } else {
        $entry = get_my_shared_eportfolios($coursemodulecontext, 'grade', $courseid, $tsort, $tdir);
    }

    // View all ePortfolios shared for grading.
    if (!empty($entry)) {

        // Create overview table.
        $table = new flexible_table('eportfolio:overview');
        $table->define_columns([
                'filename',
                'filetimemodified',
                'userfullname',
                'sharestart',
                'grade',
                'actions',
        ]);

        $actionhelp = html_writer::tag('button', '', ['class' => 'btn btn-default fa fa-question-circle ml-1',
                'data-toggle' => 'popover', 'data-container' => 'body', 'data-placement' => 'bottom',
                'title' => get_string('overview:table:btn:delete', 'mod_eportfolio'),
                'data-content' => get_string('overview:table:btn:delete:help', 'mod_eportfolio')]);

        $table->define_headers([
                get_string('overview:table:filename', 'local_eportfolio'),
                get_string('overview:table:filetimemodified', 'local_eportfolio'),
                get_string('overview:table:sharedby', 'local_eportfolio'),
                get_string('overview:table:sharestart', 'local_eportfolio'),
                get_string('overview:table:grading', 'local_eportfolio'),
                get_string('overview:table:actions', 'local_eportfolio') . $actionhelp,
        ]);
        $table->define_baseurl($url);
        $table->set_attribute('class', 'table-hover');
        $table->sortable(true, 'fullusername', SORT_ASC);
        $table->initialbars(true);
        $table->no_sorting('actions');
        $table->setup();

        foreach ($entry as $ent) {

            // ToDo: Get existing grades/feedback.
            $getgrade = $DB->get_record('eportfolio_grade', ['courseid' => $courseid, 'cmid' => $cmid,
                    'userid' => $ent['userid'], 'itemid' => $ent['fileitemid']]);

            if ($USER->id === $ent['userid'] || has_capability('mod/eportfolio:grade_eport', $coursecontext)) {
                if ($getgrade) {
                    $grade = $getgrade->grade . ' %';

                    // Add additional info icon for showing feedbacktext.
                    $gradefeedback =
                            html_writer::tag('i', '', ['class' => 'fa fa-info-circle ml-3', 'data-toggle' => 'tooltip',
                                    'data-placement' => 'bottom', 'title' => format_string($getgrade->feedbacktext)]);

                    $grade .= $gradefeedback;

                } else {
                    $grade = './.';
                }
            }

            if ($actionsallowed) {

                // Add grade button for teacher.
                $actionbtn = html_writer::link(new moodle_url('/mod/eportfolio/view.php',
                        ['id' => $cmid, 'fileid' => $ent['fileitemid'], 'userid' => $ent['userid'],
                                'action' => 'grade']), get_string('overview:table:btn:grade', 'mod_eportfolio'),
                        ['class' => 'btn btn-primary']);

                $deletebtn = html_writer::link(new moodle_url('/mod/eportfolio/view.php',
                        ['id' => $cmid, 'fileid' => $ent['fileitemid'], 'userid' => $ent['userid'],
                                'action' => 'delete']), '', ['class' => 'btn btn-secondary fa fa-undo ml-3',
                        'title' => get_string('overview:table:btn:delete', 'mod_eportfolio')]);

            } else {

                // Add view button for students.
                $actionbtn = html_writer::link(new moodle_url('/mod/eportfolio/view.php',
                        ['id' => $cmid, 'fileid' => $ent['fileitemid'], 'userid' => $ent['userid'],
                                'action' => 'view']), get_string('overview:table:btn:view', 'mod_eportfolio'),
                        ['class' => 'btn btn-primary']);

            }

            $table->add_data(
                    [
                            $ent['filename'],
                            $ent['filetimemodified'],
                            $ent['userfullname'],
                            $ent['sharestart'],
                            $grade,
                            $actionbtn . $deletebtn,
                    ]
            );
        }

        $table->finish_html();

    } else {

        echo html_writer::start_tag('p', ['class' => 'alert alert-info']);
        echo html_writer::tag('i', '', ['class' => 'fa fa-info-circle mr-1']);
        echo "Aktuell liegen keine ePortfolios vor!";
        echo html_writer::end_tag('p');

    }

}

/**
 * Get all ePortfolios the teacher can access.
 *
 * @param $shareoption
 * @param $courseid
 * @param $tsort
 * @param $tdir
 * @return array
 */
function get_shared_eportfolios($shareoption = 'share', $courseid = '', $tsort = '', $tdir = '') {
    global $USER, $DB;

    $sql = "SELECT * FROM {local_eportfolio_share} WHERE shareoption = ?";

    if ($courseid) {
        $sql .= " AND courseid = ?";
    }

    $params = [
            'shareoption' => $shareoption,
            'courseid' => $courseid,
    ];

    $eportfoliosshare = $DB->get_records_sql($sql, $params);

    $sharedeportfolios = [];

    foreach ($eportfoliosshare as $es) {

        $coursecontext = context_course::instance($es->courseid);

        // First we have to check, if current user is editingteacher in selected course to view shared ePortfolios for grading.
        if ($shareoption === 'grade') {
            if (!is_enrolled($coursecontext, $USER, 'mod/eportfolio:grade_eport')) {
                continue;
            }
        }

        if (is_enrolled($coursecontext, $USER) && $es->usermodified != $USER->id) {

            $sharedeportfolios[$es->id]['id'] = $es->id;
            $sharedeportfolios[$es->id]['itemid'] = $es->fileid;
            $sharedeportfolios[$es->id]['fileidcontext'] = $es->fileidcontext;
            $sharedeportfolios[$es->id]['userid'] = $es->usermodified;
            $sharedeportfolios[$es->id]['courseid'] = $es->courseid;
            $sharedeportfolios[$es->id]['cmid'] = ($shareoption === 'grade') ? $es->cmid : '';
            $sharedeportfolios[$es->id]['fullcourse'] = ($shareoption === 'grade') ? '1' : $es->fullcourse;
            $sharedeportfolios[$es->id]['roles'] = $es->roles;
            $sharedeportfolios[$es->id]['enrolled'] = $es->enrolled;
            $sharedeportfolios[$es->id]['groups'] = $es->coursegroups;
            $sharedeportfolios[$es->id]['enddate'] = $es->enddate;
            $sharedeportfolios[$es->id]['timecreated'] = $es->timecreated;
        }
    }

    // Rearange the array values to return numeric indexes.
    $sharedeportfolios = array_values($sharedeportfolios);

    $eportfolios = [];

    foreach ($sharedeportfolios as $key => $value) {

        $enddate = true;

        if ($value['enddate'] != 0 && $value['enddate'] < time()) {
            $enddate = false;
        }

        // First check if end date for sharing is reached.
        if ($enddate) {

            $coursecontext = context_course::instance($value['courseid']);

            // First, check, if I am eligible to view this eportfolio.
            $eligible = false;

            if ($value['fullcourse'] == '1' && !$eligible) {
                $eligible = true;
            }

            if (!empty($value['roles']) && !$eligible) {

                $roles = explode(', ', $value['roles']);

                foreach ($roles as $ro) {
                    $isenrolled = $DB->get_record('role_assignments',
                            ['contextid' => $coursecontext->id, 'roleid' => $ro, 'userid' => $USER->id]);

                    if (!empty($isenrolled)) {
                        $eligible = true;
                    }

                }
            }

            if (!empty($value['enrolled']) && !$eligible) {

                $enrolledusers = explode(', ', $value['enrolled']);

                if (in_array($USER->id, $enrolledusers)) {
                    $eligible = true;
                }
            }

            if (!empty($value['coursegroups']) && !$eligible) {

                $groups = explode(', ', $value['coursegroups']);

                foreach ($groups as $gr) {
                    $coursegroups = groups_get_all_groups($value['courseid'], $USER->id);

                    if (in_array($gr, $coursegroups)) {
                        $eligible = true;
                    }
                }

            }

            // Get course module context or user context.
            $fs = get_file_storage();

            if ($shareoption === 'grade' && $value['cmid']) {
                $modcontext = context_module::instance($value['cmid']);
                $files = $fs->get_area_files($modcontext->id, 'mod_eportfolio', 'eportfolio');
            } else if ($shareoption === 'share') {
                $context = context_course::instance($value['courseid']);
                $files = $fs->get_area_files($context->id, 'local_eportfolio', 'eportfolio');
            } else if ($shareoption === 'template') {
                $context = context_course::instance($value['courseid']);
                $files = $fs->get_area_files($context->id, 'local_eportfolio', 'eportfolio');
            } else {
                // Just in case.
                continue;
            }

            if (!empty($files) && $eligible) {
                // We use a counter for the array.
                $i = 0;

                foreach ($files as $file) {

                    if ($file->get_filename() != '.') {

                        $fileid = $file->get_id();

                        if ($value['fileidcontext'] == $fileid) {

                            $course = $DB->get_record('course', ['id' => $value['courseid']]);

                            $fileviewurlparams = [
                                    'id' => $value['id'],
                                    'course' => $course->id,
                                    'userid' => $value['userid'],
                            ];

                            if ($value['cmid']) {
                                $fileviewurlparams['cmid'] = $value['cmid'];
                            }

                            $eportfolios[$i]['fileviewurl'] = new moodle_url('/local/eportfolio/view.php',
                                    $fileviewurlparams);
                            $eportfolios[$i]['id'] = $value['id'];
                            $eportfolios[$i]['fileitemid'] = $file->get_id();
                            $eportfolios[$i]['fileidcontext'] = $value['fileidcontext'];
                            $eportfolios[$i]['filename'] = $file->get_filename();
                            $eportfolios[$i]['filenameh5p'] = get_h5p_title($file->get_pathnamehash());
                            $eportfolios[$i]['filesize'] = display_size($file->get_filesize());
                            $eportfolios[$i]['filetimemodified'] = date('d.m.Y', $file->get_timemodified());
                            $eportfolios[$i]['filetimecreated'] = date('d.m.Y', $file->get_timecreated());

                            $eportfolios[$i]['sharestart'] = date('d.m.Y', $value['timecreated']);
                            $eportfolios[$i]['shareend'] = (!empty($value['enddate'])) ? date('d.m.Y', $value['enddate']) : './.';

                            $user = $DB->get_record('user', ['id' => $value['userid']]);

                            $eportfolios[$i]['userid'] = $user->id;
                            $eportfolios[$i]['userfullname'] = fullname($user);

                            $courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);

                            $eportfolios[$i]['courseid'] = $course->id;
                            $eportfolios[$i]['coursename'] = $course->fullname;
                            $eportfolios[$i]['courseurl'] = $courseurl;

                        }

                    }

                    $i++;

                }
            }
        }
    }

    if ($tsort && $tdir) {

        $sortorder = get_sort_order($tdir);

        // Rearange the array values to return numeric indexes.
        $results = array_values($eportfolios);

        $keyvalue = array_column($eportfolios, $tsort);
        if ($keyvalue) {
            array_multisort($keyvalue, $sortorder, $results);
        }

    } else {
        // Rearange the array values to return numeric indexes.
        $results = array_values($eportfolios);
    }

    return $results;
}

/**
 * Get all ePortfolios in case student is viewing the activity.
 *
 * @param $context
 * @param $shareoption
 * @param $courseid
 * @param $tsort
 * @param $tdir
 * @return array
 */
function get_my_shared_eportfolios($context, $shareoption = 'share', $courseid = '', $tsort = '', $tdir = '') {
    global $USER, $DB;

    $sql = "SELECT * FROM {local_eportfolio_share} WHERE usermodified = ? AND shareoption = ?";

    if ($courseid) {
        $sql .= " AND courseid = ?";
    }

    $params = [
            'usermodified' => $USER->id,
            'shareoption' => $shareoption,
            'courseid' => $courseid,
    ];

    $sharedeportfolios = $DB->get_records_sql($sql, $params);

    $eportfolios = [];

    // Default component for files.
    $component = 'local_eportfolio';

    foreach ($sharedeportfolios as $sp) {

        // In case we are in the activity ePortfolio.
        if ($courseid && $context->instanceid == $sp->cmid && $shareoption === 'grade') {
            $component = 'mod_eportfolio';
        }

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, $component, 'eportfolio');

        if (!empty($files)) {
            foreach ($files as $file) {

                if ($file->get_filename() != '.') {

                    $fileid = $sp->fileid;

                    if ($courseid) {
                        $fileid = $sp->fileidcontext;
                    }

                    if ($fileid == $file->get_id() && $sp->shareoption == $shareoption) {

                        // Since I am viewing my own.

                        if ($shareoption == 'grade') {
                            $viewurlid = $sp->fileidcontext;
                        } else {
                            $viewurlid = $file->get_id();
                        }

                        $eportfolios[$sp->id]['fileviewurl'] =
                                new moodle_url('/local/eportfolio/view.php', ['id' => $viewurlid]);
                        $eportfolios[$sp->id]['id'] = $sp->id;
                        $eportfolios[$sp->id]['fileitemid'] = $file->get_id();
                        $eportfolios[$sp->id]['fileidcontext'] = $sp->fileidcontext;
                        $eportfolios[$sp->id]['filename'] = $file->get_filename();
                        $eportfolios[$sp->id]['filenameh5p'] = get_h5p_title($file->get_pathnamehash());
                        $eportfolios[$sp->id]['filesize'] = display_size($file->get_filesize());
                        $eportfolios[$sp->id]['filetimemodified'] = date('d.m.Y', $file->get_timemodified());
                        $eportfolios[$sp->id]['filetimecreated'] = date('d.m.Y', $file->get_timecreated());

                        $eportfolios[$sp->id]['sharestart'] = date('d.m.Y', $sp->timecreated);
                        $eportfolios[$sp->id]['shareend'] = (!empty($sp->enddate)) ? date('d.m.Y', $sp->enddate) : './.';

                        // Removed from mustache template. Maybe we don't need this here as well.
                        switch ($sp->shareoption) {
                            case 'share':
                                $eportfolios[$sp->id]['shareoption'] = get_string('overview:shareoption:share', 'local_eportfolio');
                                break;
                            case 'grade':
                                $eportfolios[$sp->id]['shareoption'] = get_string('overview:shareoption:grade', 'local_eportfolio');
                                break;
                        }

                        $eportfolios[$sp->id]['userid'] = $USER->id;

                        $course = $DB->get_record('course', ['id' => $sp->courseid]);

                        $courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);
                        $eportfolios[$sp->id]['courseid'] = $course->id;
                        $eportfolios[$sp->id]['coursename'] = $course->fullname;
                        $eportfolios[$sp->id]['courseurl'] = $courseurl;

                        $eportfolios[$sp->id]['undourl'] = new moodle_url('/local/eportfolio/index.php',
                                ['id' => $sp->id, 'action' => 'undo']);

                        // Get participants who have access to my shared eportfolios.
                        $participants = get_shared_participants($course->id, $sp->fullcourse,
                                $sp->enrolled, $sp->roles, $sp->coursegroups);

                        $participants = implode(', ', $participants);

                        $eportfolios[$sp->id]['participants'] = $participants;

                    }

                }
            }
        }
    }

    if ($tsort && $tdir) {

        $sortorder = get_sort_order($tdir);

        // Rearange the array values to return numeric indexes.
        $results = array_values($eportfolios);

        $keyvalue = array_column($eportfolios, $tsort);
        if ($keyvalue) {
            array_multisort($keyvalue, $sortorder, $results);
        }

    } else {
        // Rearange the array values to return numeric indexes.
        $results = array_values($eportfolios);
    }

    return $results;

}

// Get H5P title.
/**
 * Get the H5P file title.
 *
 * @param $pathnamehash
 * @return void
 */
function get_h5p_title($pathnamehash) {
    global $DB;

    $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $pathnamehash]);

    if ($h5pfile) {
        $json = $h5pfile->jsoncontent;
        $jsondecode = json_decode($json);

        if (isset($jsondecode->metadata)) {
            if ($jsondecode->metadata->title) {
                $title = $jsondecode->metadata->title;
            }
        } else {
            $title = $jsondecode->title;
        }

        if (!empty($title)) {
            return $title;
        }
    }
}

// ToDo: Should be an adhoc task.

/**
 * Send message to student when a grade was received.
 *
 * @param $courseid
 * @param $userfrom
 * @param $userto
 * @param $filename
 * @param $itemid
 * @param $cmid
 * @return void
 */
function eportfolio_send_grading_message($courseid, $userfrom, $userto, $filename, $itemid, $cmid) {
    global $DB;

    $contexturl = new moodle_url('/mod/eportfolio/view.php', ['id' => $cmid]);

    // Holds values for the string for the email message.
    $a = new stdClass;

    $userfromdata = $DB->get_record('user', ['id' => $userfrom]);
    $a->userfrom = fullname($userfromdata);
    $a->filename = $filename;
    $a->viewurl = (string) $contexturl;

    $course = $DB->get_record('course', ['id' => $courseid]);
    $a->coursename = $course->fullname;

    // Fetch message HTML and plain text formats.
    $messagehtml = get_string('message:emailmessage', 'mod_eportfolio', $a);
    $plaintext = format_text_email($messagehtml, FORMAT_HTML);

    $smallmessage = get_string('message:smallmessage', 'mod_eportfolio', $a);
    $smallmessage = format_text_email($smallmessage, FORMAT_HTML);

    // Subject.
    $subject = get_string('message:subject', 'mod_eportfolio');

    $message = new \core\message\message();

    $message->courseid = $courseid;
    $message->component = 'mod_eportfolio'; // Your plugin's name.
    $message->name = 'grading'; // Your notification name from message.php.

    $message->userfrom = core_user::get_noreply_user();

    $usertodata = $DB->get_record('user', ['id' => $userto]);
    $message->userto = $usertodata;

    $message->subject = $subject;
    $message->smallmessage = $smallmessage;
    $message->fullmessage = $plaintext;
    $message->fullmessageformat = FORMAT_PLAIN;
    $message->fullmessagehtml = $messagehtml;
    $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message.
    $message->contexturl = $contexturl->out(false);
    $message->contexturlname = get_string('message:contexturlname', 'mod_eportfolio');

    // Finally send the message.
    message_send($message);

}

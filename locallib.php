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
 * @copyright 2024 weQon UG {@link https://weqon.net}
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

/**
 * Check, if the current course is marked as ePortfolio course.
 *
 * @param int $courseid
 * @return bool
 */
function check_current_eportfolio_course($courseid) {
    global $DB;

    // Check, if the current course is marked as ePortfolio course.
    $sql = "SELECT cd.*
        FROM {customfield_data} cd
        JOIN {customfield_field} cf ON cf.id = cd.fieldid
        WHERE cf.shortname = :shortname AND cd.instanceid = :instanceid";

    $params = [
            'shortname' => 'eportfolio_course',
            'instanceid' => $courseid,
    ];

    $customfielddata = $DB->get_record_sql($sql, $params);

    if (empty($customfielddata) || !$customfielddata->intvalue) {
        return false;
    } else {
        return true;
    }
}

/**
 * Prepare the overview table display.
 *
 * @param int $courseid
 * @param int $cmid
 * @param string $url
 * @param string $tsort
 * @param int $tdir
 * @return void
 */
function eportfolio_render_overview_table($courseid, $cmid, $url, $tsort = '', $tdir = '') {
    global $DB, $USER, $OUTPUT;

    $coursemodulecontext = context_module::instance($cmid);

    $actionsallowed = false;

    // First we have to check, if current user is grading teacher.
    if (is_enrolled($coursemodulecontext, $USER, 'mod/eportfolio:grade_eport')) {

        $actionsallowed = true;

        $entry = get_eportfolios($courseid, 0, $tsort, $tdir);

    } else {
        $entry = get_eportfolios($courseid, $USER->id, $tsort, $tdir);
    }

    // View all ePortfolios shared for grading.
    if (!empty($entry)) {

        // Create overview table.
        $table = new flexible_table('eportfolio:overview');
        $table->define_columns([
                'title',
                'userfullname',
                'sharestart',
                'grade',
                'actions',
        ]);

        $actionhelp = '';

        if ($actionsallowed) {
            $actionhelp = html_writer::tag('button', '', ['class' => 'btn btn-default fa fa-question-circle ml-1',
                    'data-toggle' => 'popover', 'data-container' => 'body', 'data-placement' => 'bottom',
                    'title' => get_string('overview:table:btn:delete', 'mod_eportfolio'),
                    'data-content' => get_string('overview:table:btn:delete:help', 'mod_eportfolio')]);
        }

        $table->define_headers([
                get_string('overview:table:title', 'mod_eportfolio'),
                get_string('overview:table:userfullname', 'mod_eportfolio'),
                get_string('overview:table:sharestart', 'mod_eportfolio'),
                get_string('overview:table:grade', 'mod_eportfolio'),
                get_string('overview:table:actions', 'mod_eportfolio') . $actionhelp,
        ]);

        $table->define_baseurl($url);
        $table->set_attribute('class', 'table-hover');
        $table->sortable(true, 'fullusername', SORT_ASC);
        $table->initialbars(true);
        $table->no_sorting('actions');
        $table->setup();

        $deletebtn = '';

        foreach ($entry as $ent) {

            $params = [
                    'courseid' => $courseid,
                    'cmid' => $cmid,
                    'fileidcontext' => $ent->fileidcontext,
            ];

            $getgrade = $DB->get_record('eportfolio_grade', $params);

            $grade = './.';

            if (!empty($getgrade)) {
                $grade = $getgrade->grade . ' %';

                // Add additional info icon for showing feedbacktext.
                $gradefeedback =
                        html_writer::tag('i', '', ['class' => 'fa fa-info-circle ml-3', 'data-toggle' => 'tooltip',
                                'data-placement' => 'bottom', 'title' => format_string($getgrade->feedbacktext)]);

                $grade .= $gradefeedback;

            }

            if ($actionsallowed) {
                // Add grade button for teacher.
                $actionbtn = html_writer::link(new moodle_url('/mod/eportfolio/grade.php',
                        ['id' => $cmid, 'eportid' => $ent->eportid]), get_string('overview:table:btn:grade', 'mod_eportfolio'),
                        ['class' => 'btn btn-primary',
                                'title' => get_string('overview:table:btn:grade', 'mod_eportfolio')]);

                $deleteurl = new moodle_url('/mod/eportfolio/actions.php', ['id' => $cmid, 'eportid' => $ent->eportid,
                        'action' => 'delete', 'sesskey' => sesskey()]);

                $deletedata = new \stdClass();
                $deletedata->deleteurl = $deleteurl->out(false);

                $deletebtn = $OUTPUT->render_from_template('mod_eportfolio/button_delete', $deletedata);

            } else {
                // Add view button for students.
                $actionbtn = html_writer::link(new moodle_url('/mod/eportfolio/grade.php',
                        ['id' => $cmid, 'eportid' => $ent->eportid]), get_string('overview:table:btn:view', 'mod_eportfolio'),
                        ['class' => 'btn btn-primary',
                                'title' => get_string('overview:table:btn:view', 'mod_eportfolio')]);
            }

            $table->add_data(
                    [
                            $ent->title,
                            $ent->userfullname,
                            date('d.m.Y', $ent->timecreated),
                            $grade,
                            $actionbtn . $deletebtn,
                    ]
            );
        }

        $table->finish_html();

    } else {
        // No ePortfolios found.
        $data = new stdClass();
        echo $OUTPUT->render_from_template('mod_eportfolio/noeportfolios_found', $data);
    }

}

/**
 * Get ePortfolios for user or grading teacher.
 *
 * @param int $courseid
 * @param int $userid
 * @param string $tsort
 * @param int $tdir
 * @return array
 */
function get_eportfolios($courseid, $userid = '', $tsort = '', $tdir = '') {
    global $DB;

    $sql = "SELECT * FROM {local_eportfolio_share} WHERE shareoption = ? AND courseid = ?";

    $params = [
            'shareoption' => 'grade', // It's always grade at this point.
            'courseid' => $courseid,
    ];

    // If user ID is set, we assume the user is accessing the page.
    if (!empty($userid)) {
        $sql .= " AND usermodified = ?";
        $params['userid'] = $userid;
    }

    // If tsort and tdir is set.
    $sortorder = '';

    if ($tsort) {

        $orderby = get_sort_order($tdir);

        if ($tsort === 'title') {
            $orderbyfield = 'title';
        } else if ($tsort === 'userfullname') {
            $orderbyfield = 'userfullname';
        } else if ($tsort === 'sharestart') {
            $orderbyfield = 'sharestart';
        } else if ($tsort === 'grade') {
            $orderbyfield = 'grade';
        }

        $sortorder = " ORDER BY " . $orderbyfield . " " . $orderby;

    }

    if (!empty($sortorder)) {
        $sql .= $sortorder;
    }

    $eportfoliosshare = $DB->get_records_sql($sql, $params);

    $sharedeportfolios = [];

    foreach ($eportfoliosshare as $es) {

        $eport = new stdClass();

        $user = $DB->get_record('user', ['id' => $es->usermodified]);

        $eport->eportid = $es->id;
        $eport->title = (!empty($es->title)) ? $es->title : get_h5p_title($es->fileidcontext);
        $eport->fileidcontext = $es->fileidcontext;
        $eport->usermodified = $es->usermodified;
        $eport->userfullname = fullname($user);
        $eport->courseid = $es->courseid;
        $eport->timecreated = $es->timecreated;

        $sharedeportfolios[] = $eport;
    }

    return $sharedeportfolios;
}

/**
 *  Output content based on set sort order.
 *
 * @param int $sortorder
 * @return int|void
 */
function get_sort_order($sortorder) {
    switch ($sortorder) {
        case '3':
            return 'DESC';
            break;
        case '4':
            return 'ASC';
            break;
        default:
            return 'ASC';
    }
}

/**
 * Get the H5P file title.
 *
 * @param int $fileidcontext
 * @return void
 */
function get_h5p_title($fileidcontext) {
    global $DB;

    $fs = get_file_storage();
    $file = $fs->get_file_by_id($fileidcontext);

    $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $file->get_pathnamehash()]);

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

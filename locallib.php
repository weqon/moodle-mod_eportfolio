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
function mod_eportfolio_check_current_eportfolio_course($courseid) {
    global $DB;

    // Check, if the current course is marked as ePortfolio course.
    $sql = "SELECT cd.*
        FROM {customfield_data} cd
        JOIN {customfield_field} cf ON cf.id = cd.fieldid
        WHERE cf.shortname = :shortname AND cd.instanceid = :instanceid";

    $params = [
            'shortname' => 'eportfolio_course',
            'instanceid' => (int) $courseid,
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
 * @param int $page
 * @param int $perpage
 * @return void
 */
function mod_eportfolio_render_overview_table($courseid, $cmid, $url, $tsort = null, $tdir = null, $page = null, $perpage = null) {
    global $DB, $USER, $OUTPUT;

    $coursemodulecontext = context_module::instance($cmid);

    $actionsallowed = false;

    // First we have to check, if current user is grading teacher.
    if (is_enrolled($coursemodulecontext, $USER, 'mod/eportfolio:grade_eport')) {

        $actionsallowed = true;

        $entry = mod_eportfolio_get_eportfolios($courseid, $cmid, 0, $tsort, $tdir, $page, $perpage);

    } else {
        $entry = mod_eportfolio_get_eportfolios($courseid, $cmid, $USER->id, $tsort, $tdir, $page, $perpage);
    }

    // View all ePortfolios shared for grading.
    if (!empty($entry)) {

        $count = $DB->count_records('local_eportfolio_share', ['shareoption' => 'grade', 'courseid' => $courseid]);

        echo $OUTPUT->paging_bar($count, $page, $perpage, $url);

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
            $actionhelp = html_writer::tag('button', '', ['class' => 'btn btn-link fa fa-question-circle ml-1',
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
        $table->no_sorting('grade');
        $table->setup();

        $deletebtn = '';

        foreach ($entry as $ent) {

            $grade = $ent->grade;

            // Add additional info icon for showing feedbacktext.
            if ($ent->feedbacktext != './') {
                $gradefeedback =
                        html_writer::tag('i', '', ['class' => 'fa fa-info-circle ml-3', 'data-toggle' => 'tooltip',
                                'data-placement' => 'bottom', 'title' => format_string($ent->feedbacktext)]);

                $grade .= $gradefeedback;
            }

            if ($actionsallowed) {
                // Add grade button for teacher.
                $actionbtn = html_writer::link(new moodle_url('/mod/eportfolio/grade.php',
                        ['id' => $cmid, 'eportid' => $ent->eportid, 'page' => $page]),
                        get_string('overview:table:btn:grade', 'mod_eportfolio'),
                        ['class' => 'btn btn-primary',
                                'title' => get_string('overview:table:btn:grade', 'mod_eportfolio')]);

                $deleteurl = new moodle_url('/mod/eportfolio/actions.php', ['id' => $cmid, 'eportid' => $ent->eportid,
                        'action' => 'delete', 'sesskey' => sesskey(), 'page' => $page]);

                $deletedata = new \stdClass();
                $deletedata->deleteurl = $deleteurl->out(false);

                $deletebtn = $OUTPUT->render_from_template('mod_eportfolio/button_delete', $deletedata);

            } else {
                // Add view button for students.
                $actionbtn = html_writer::link(new moodle_url('/mod/eportfolio/grade.php',
                        ['id' => $cmid, 'eportid' => $ent->eportid, 'page' => $page]),
                        get_string('overview:table:btn:view', 'mod_eportfolio'),
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

        echo $OUTPUT->paging_bar($count, $page, $perpage, $url);

    } else {
        // No ePortfolios found.
        $data = new stdClass();
        
        if ($actionsallowed) {
            $data->nofilefoundteacher = true;
        } else {
            $data->nofilefoundstudent = true;

            $eporturl = new moodle_url('/local/eportfolio/index.php');
            $data->eporturl = $eporturl->out(false);
        }
        
        echo $OUTPUT->render_from_template('mod_eportfolio/noeportfolios_found', $data);
    }

}

/**
 * Get ePortfolios for grading teacher.
 *
 * @param int $courseid
 * @param int $cmid
 * @param int $userid
 * @param string $tsort
 * @param int $tdir
 * @param int $page
 * @param int $perpage
 * @return array
 */
function mod_eportfolio_get_eportfolios($courseid, $cmid, $userid = null, $tsort = null, $tdir = null, $page = null, $perpage = null) {
    global $DB;

    $sql = "SELECT es.id, es.title, es.fileidcontext, es.usermodified, es.courseid, es.timecreated,
            eg.grade, eg.feedbacktext
            FROM {local_eportfolio_share} es
            LEFT JOIN {eportfolio_grade} eg
            ON es.fileidcontext = eg.fileidcontext
            WHERE es.shareoption = :esshareoption AND es.courseid = :escourseid AND es.cmid = :escmid
            ";

    $params = [
            'esshareoption' => 'grade', // It's always grade at this point.
            'escourseid' => (int) $courseid,
            'escmid' => (int) $cmid,
    ];

    // If user ID is set, we assume the user is accessing the page.
    if (!empty($userid)) {
        $sql .= " AND es.usermodified = :esusermodified";
        $params['esusermodified'] = (int) $userid;
    }

    // If tsort and tdir is set.
    $sortorder = '';

    if ($tsort) {
        $orderby = mod_eportfolio_get_sort_order($tdir);

        if ($tsort === 'title') {
            $orderbyfield = 'title';
        } else if ($tsort === 'userfullname') {
            $orderbyfield = 'usermodified';
        } else if ($tsort === 'sharestart') {
            $orderbyfield = 'timecreated';
        }

        $sortorder = " ORDER BY " . $orderbyfield . " " . $orderby;
    }

    if (!empty($sortorder)) {
        $sql .= $sortorder;
    }

    if (!empty($page)) {
        $limitfrom = $page * $perpage;
        $limitnum = $perpage;

        $sql .= "LIMIT " . $limitfrom . ', ' . $limitnum;
    }

    $eportfoliosshare = $DB->get_records_sql($sql, $params);

    $sharedeportfolios = [];

    foreach ($eportfoliosshare as $es) {

        $eport = new stdClass();

        $user = $DB->get_record('user', ['id' => $es->usermodified]);

        $eport->eportid = $es->id;
        $eport->title = (!empty($es->title)) ? $es->title : mod_eportfolio_get_h5p_title($es->fileidcontext);
        $eport->fileidcontext = $es->fileidcontext;
        $eport->usermodified = $es->usermodified;
        $eport->userfullname = fullname($user);
        $eport->courseid = $es->courseid;
        $eport->timecreated = $es->timecreated;
        $eport->grade = (!empty($es->grade)) ? $es->grade . '%' : './';
        $eport->feedbacktext = (!empty($es->feedbacktext)) ? $es->feedbacktext : './';

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
function mod_eportfolio_get_sort_order($sortorder) {
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
function mod_eportfolio_get_h5p_title($fileidcontext) {
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

/**
 * Get the upload max file size.
 *
 * @return int
 */

function mod_eportfolio_get_upload_max_file_size() {
    global $CFG;

    $config = get_config('mod_eportfolio');

    if ($config->maxbytes != 0) {
        // Check, if plugin has an upload limit set.
        $filemaxbytes = $config->maxbytes;
    } else if ($CFG->maxbytes == 0) {
        // In case global setting is set to default.
        $filemaxbytes = get_max_upload_file_size();
    }  else if ($CFG->maxbytes != 0) {
        // check, if the global upload limit was set.
        $filemaxbytes = $CFG->maxbytes;
    } else {
        // Just in case.
        $filemaxbytes = $CFG->maxbytes;
    }

    // Check, if global upload limit is lower than the limit set by plugin.
    // Just in case the global upload limit was lowered afterwards.
    if ($config->maxbytes != 0 && $CFG->maxbytes != 0 && $config->maxbytes > $CFG->maxbytes) {
        $filemaxbytes = $CFG->maxbytes;
    }

    return (int) $filemaxbytes;

}

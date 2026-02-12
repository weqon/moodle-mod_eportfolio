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
 * Library of interface functions and constants.
 *
 * @package     mod_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function eportfolio_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_GROUPMEMBERSONLY:
            return false;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
        case FEATURE_COMPLETION_HAS_RULES:
            return false;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case MOD_PURPOSE_ASSESSMENT:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_eportfolio into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_eportfolio_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function eportfolio_add_instance($moduleinstance, $mform = null) {
    global $DB, $USER;

    $moduleinstance->timecreated = time();
    $moduleinstance->usermodified = $USER->id;

    // In case after creating the instance only feedback file is set, since feedback text is set to default 1 in DB.
    if (!isset($moduleinstance->feedbacktext)) {
        $moduleinstance->feedbacktext = 0;
    }

    $id = $DB->insert_record('eportfolio', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_eportfolio in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_eportfolio_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function eportfolio_update_instance($moduleinstance, $mform = null) {
    global $DB, $USER;

    $moduleinstance->timemodified = time();
    $moduleinstance->usermodified = $USER->id;
    $moduleinstance->id = $moduleinstance->instance;

    if (!isset($moduleinstance->alwaysshowdescription)) {
        $moduleinstance->alwaysshowdescription = 0;
    }

    if (!isset($moduleinstance->feedbacktext)) {
        $moduleinstance->feedbacktext = 0;
    }
    if (!isset($moduleinstance->feedbackfile)) {
        $moduleinstance->feedbackfile = 0;
    }

    return $DB->update_record('eportfolio', $moduleinstance);
}

/**
 * Removes an instance of the mod_eportfolio from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function eportfolio_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('eportfolio', ['id' => $id]);
    if (!$exists) {
        return false;
    }

    $moduleid = $DB->get_field('modules', 'id', ['name' => 'eportfolio']);

    // Get the course module.
    if (!$cm = $DB->get_record('course_modules', ['instance' => $id, 'module' => $moduleid])) {
        return false;
    }

    if (!$DB->delete_records('eportfolio', ['id' => $id])) {
        return false;
    }

    $result = true;

    // Get the module context.
    $modcontext = context_module::instance($cm->id);

    // Delete any dependent records here.

    // Delete all associated H5P files.
    $eportfoliofiles = $DB->get_records('files',
            ['contextid' => $modcontext->id, 'component' => 'mod_eportfolio', 'filearea' => 'eportfolio']);

    foreach ($eportfoliofiles as $eport) {

        // Get H5P files and delete them.
        if ($eport->filename != '.') {

            $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $eport->pathnamehash]);

            if ($h5pfile) {
                $DB->delete_records('h5p', ['id' => $h5pfile->id]);
            }
        }

    }

    // Delete files associated with this ePortfolio.
    $fs = get_file_storage();
    if (!$fs->delete_area_files($modcontext->id, 'mod_eportfolio', 'eportfolio')) {
        $result = false;
    }

    // Delete entries from table local_eportfolio_share when shared for grading for this cm.
    if (!$DB->delete_records('local_eportfolio_share', ['courseid' => $cm->course, 'shareoption' => 'grade',
            'cmid' => $cm->id])) {
        $result = false;
    }

    // Delete entries from table eportfolio_grade.
    if (!$DB->delete_records('eportfolio_grade', ['courseid' => $cm->course, 'cmid' => $cm->id, 'instance' => $cm->instance])) {
        $result = false;
    }

    // Delete feedback files associated with this ePortfolio.
    $fs = get_file_storage();
    if (!$fs->delete_area_files($modcontext->id, 'mod_eportfolio', 'feedbackfile')) {
        $result = false;
    }

    // Delete events.
    if (!$DB->delete_records('event', ['modulename' => 'eportfolio', 'instance' => $id])) {
        $result = false;
    }

    return $result;
}

/**
 * Serves the files.
 *
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - justsend the file
 * @package  local_adleteh5ppool
 * @category files
 */
function eportfolio_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {

    $relativepath = implode('/', $args);
    $fullpath = '/' . $context->id . '/mod_eportfolio/' . $filearea . '/' . $relativepath;

    $fs = get_file_storage();
    $file = $fs->get_file_by_hash(sha1($fullpath));

    if (!$file || $file->is_directory()) {
        return false;
    }

    send_stored_file($file, null, 0, true); // Download MUST be forced - security!

    return;
}

/**
 * Add a get_coursemodule_info function in case any ePortfolio type wants to add 'extra' information
 * for the course.
 *
 * Given a course_module object, this function returns any "extra" information that may be needed
 * when printing this activity in a course listing.  See get_array_of_activities() in course/lib.php.
 *
 * @param stdClass $coursemodule The coursemodule object (record).
 * @return cached_cm_info An object on information that the courses
 *                        will know about (most noticeably, an icon).
 */
function eportfolio_get_coursemodule_info($coursemodule) {
    global $DB;

    $dbparams = ['id' => $coursemodule->instance];
    $fields = 'id, name, alwaysshowdescription, allowsubmissionsfromdate, intro, introformat, submissionduedate,
       alwaysshowdescription';
    if (!$eportfolio = $DB->get_record('eportfolio', $dbparams, $fields)) {
        return false;
    }

    $result = new cached_cm_info();

    $result->name = $eportfolio->name;
    if ($coursemodule->showdescription) {
        if ($eportfolio->alwaysshowdescription || time() > $eportfolio->allowsubmissionsfromdate) {
            // Convert intro to html. Do not filter cached version, filters run at display time.
            $result->content = format_module_intro('eportfolio', $eportfolio, $coursemodule->id, false);
        }
    }

    // Populate some other values that can be used in calendar or on dashboard.
    if ($eportfolio->allowsubmissionsfromdate) {
        $result->customdata['allowsubmissionsfromdate'] = $eportfolio->allowsubmissionsfromdate;
    }
    if ($eportfolio->submissionduedate) {
        $result->customdata['submissionduedate'] = $eportfolio->submissionduedate;
    }

    return $result;
}

/**
 * Create grade item for given eportfolio.
 *
 * @param stdClass $eportfolio record with extra cmidnumber
 * @param array $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function eportfolio_grade_item_update($eportfolio, $grades = null) {
    require_once(__DIR__ . '/../../lib/gradelib.php');

    $params = [
            'itemname' => $eportfolio->name,
            'gradetype' => GRADE_TYPE_VALUE,
            'grademax' => $eportfolio->grade,
            'grademin' => 0,
    ];

    if ($eportfolio->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid'] = -$eportfolio->grade;
    }

    return grade_update(
            'mod/eportfolio',
            $eportfolio->course,
            'mod',
            'eportfolio',
            $eportfolio->id,
            0,
            $grades,
            $params
    );
}

/**
 * Delete grade item for given mod_eportfolio instance.
 *
 * @param stdClass $eportfolio Instance object.
 * @return int Returns GRADE_UPDATE_OK, GRADE_UPDATE_FAILED, GRADE_UPDATE_MULTIPLE or GRADE_UPDATE_ITEM_LOCKED
 */
function eportfolio_grade_item_delete($eportfolio) {
    require_once(__DIR__ . '/../../lib/gradelib.php');

    return grade_update(
            'mod/eportfolio',
            $eportfolio->course,
            'mod',
            'eportfolio',
            $eportfolio->id,
            0,
            null,
            ['deleted' => 1]
    );
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the ePortfolio activity.
 *
 * @param MoodleQuickForm $mform form passed by reference
 */
function eportfolio_reset_course_form_definition($mform) {
    $mform->addElement('header', 'h5pactivityheader', get_string('modulenameplural', 'eportfolio'));
    $mform->addElement('static', 'h5pactivitydelete', get_string('delete'));
    $mform->addElement('advcheckbox', 'reset_eportfolio', get_string('resetplugininstances', 'eportfolio'));
}

/**
 * Course reset form defaults.
 *
 * @param stdClass $course the course object
 * @return array
 */
function eportfolio_reset_course_form_defaults($course) {
    return [
            'reset_eportfolio' => 1,
    ];
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 *
 * This function will remove all ePortfolio grades in the database
 * and clean up any related data.
 *
 * @param stdClass $data the data submitted from the reset course.
 * @return array of reseting status
 */
function eportfolio_reset_userdata($data) {
    global $CFG, $DB;

    $status = [];

    if (!empty($data->reset_eportfolio)) {
        // Delete user data from eportfolio_grade.
        $DB->delete_records_select(
                'eportfolio_grade',
                'instance IN (
                SELECT id FROM {eportfolio} WHERE course = ?
            )',
                [$data->courseid]
        );

        // Delete any dependent records here.
        // Delete all associated H5P files.
        $eportfoliofiles = $DB->get_records('files',
                ['contextid' => $modcontext->id, 'component' => 'mod_eportfolio', 'filearea' => 'eportfolio']);

        foreach ($eportfoliofiles as $eport) {
            // Get H5P files and delete them.
            if ($eport->filename != '.') {

                $h5pfile = $DB->get_record('h5p', ['pathnamehash' => $eport->pathnamehash]);

                if ($h5pfile) {
                    $DB->delete_records('h5p', ['id' => $h5pfile->id]);
                }
            }
        }

        // Get all instances for mod_eportfolio.

        $params = ['courseid' => $data->courseid];
        $sql = "SELECT e.id FROM {eportfolio} e WHERE e.course=:courseid";
        if ($activities = $DB->get_records_sql($sql, $params)) {
            foreach ($activities as $activity) {
                $cm = get_coursemodule_from_instance('eportfolio',
                        $activity->id,
                        $data->courseid,
                        false,
                        MUST_EXIST);
                $context = context_module::instance($cm->id);

                // Delete grade book items.
                require_once($CFG->libdir . '/gradelib.php');
                grade_update('mod/eportfolio', $activity->course, 'mod', 'eportfolio', $activity->id, 0, null, ['deleted' => 1]);
                grade_update('mod/eportfolio', $activity->course, 'mod', 'eportfolio', $activity->id, 1, null, ['deleted' => 1]);

                // Delete files associated with this ePortfolio.
                $fs = get_file_storage();
                if (!$fs->delete_area_files($context->id, 'mod_eportfolio', 'eportfolio')) {
                    $result = false;
                }

                // Delete entries from table local_eportfolio_share when shared for grading for this cm.
                if (!$DB->delete_records('local_eportfolio_share', ['courseid' => $cm->course, 'shareoption' => 'grade',
                        'cmid' => $cm->id])) {
                    $result = false;
                }

                // Delete entries from table eportfolio_grade.
                if (!$DB->delete_records('eportfolio_grade',
                        ['courseid' => $cm->course, 'cmid' => $cm->id, 'instance' => $cm->instance])) {
                    $result = false;
                }

                // Delete feedback files associated with this ePortfolio.
                $fs = get_file_storage();
                if (!$fs->delete_area_files($context->id, 'mod_eportfolio', 'feedbackfile')) {
                    $result = false;
                }

                // Delete events.
                if (!$DB->delete_records('event', ['modulename' => 'eportfolio', 'instance' => $activity->id])) {
                    $result = false;
                }
            }
        }

        $status[] = [
                'component' => get_string('modulenameplural', 'eportfolio'),
                'item' => get_string('resetuserdata', 'eportfolio'),
                'error' => false,
        ];
    }

    return $status;
}
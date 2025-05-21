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
 * Privacy provider for eportfolio activity plugin
 *
 * @package mod_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_eportfolio\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadataprovider;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\plugin\provider as pluginprovider;
use core_privacy\local\request\writer;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\helper;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;
use coding_exception;
use context;
use context_module;
use context_system;
use dml_exception;

/**
 * Privacy provider implementation for mod_eportfolio plugin.
 */
class provider implements metadataprovider, pluginprovider, core_userlist_provider {

    /**
     * Returns metadata about the data stored by the plugin.
     *
     * @param collection $collection The collection to add metadata to.
     * @return collection The updated collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
                'mod_eportfolio',
                [
                        'usermodified' => 'privacy:metadata:mod_eportfolio:usermodified',
                        'usermodified_grade' => 'privacy:metadata:mod_eportfolio:grade:usermodified',
                        'userid_grade' => 'privacy:metadata:mod_eportfolio:grade:userid',
                        'graderid_grade' => 'privacy:metadata:mod_eportfolio:grade:graderid',
                ],
                'privacy:metadata:mod_eportfolio'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information.
     *
     * @param int $userid The user ID.
     * @return contextlist The list of contexts.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        // If user was already deleted, do nothing.
        if (!\core_user::get_user($userid)) {
            return new contextlist();
        }

        $contextlist = new \core_privacy\local\request\contextlist();

        $sql = "
            SELECT DISTINCT ctx.id
              FROM {eportfolio} e
              JOIN {modules} m
                ON m.name = :eportfolio
              JOIN {course_modules} cm
                ON cm.instance = e.id
               AND cm.module = m.id
              JOIN {context} ctx
                ON ctx.instanceid = cm.id
               AND ctx.contextlevel = :modulelevel
              JOIN {eportfolio_grade} eg
                ON eg.instance = e.id
             WHERE eg.userid = :userid OR eg.graderid = :graderid";

        $params = [
                'eportfolio' => 'eportfolio',
                'modulelevel' => CONTEXT_MODULE,
                'userid' => $userid,
                'graderid' => $userid,
        ];
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;

    }

    /**
     * Get the list of users within a specific context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $sql = "SELECT eg.userid
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                  JOIN {eportfolio} e ON e.id = cm.instance
                  JOIN {eportfolio_grade} eg ON eg.instance = e.id
                 WHERE cm.id = :instanceid";

        $params = [
                'instanceid' => $context->instanceid,
                'modulename' => 'eportfolio',
        ];

        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Exports all user data for the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved context list.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist as $context) {
            if ($context->contextlevel == CONTEXT_MODULE) {

                $sql = "SELECT c.id AS contextid, e.id AS eportfolioid, cm.id AS cmid
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid
                  JOIN {eportfolio} e ON e.id = cm.instance
                 WHERE c.id = :contextid";

                $params = [
                        'contextid' => $context->id,
                ];

                $eportfolios = $DB->get_records_sql($sql, $params);

                foreach ($eportfolios as $eportfolio) {

                    // Get user data who received a grade.
                    $sql = "SELECT eg.courseid, eg.userid, eg.grade, eg.feedbacktext, eg.timecreated, eg.timemodified, u.firstname, u.lastname
                    FROM {eportfolio_grade} eg
                    JOIN {user} u ON eg.userid = u.id
                    WHERE eg.userid = :userid AND instance = :instance";

                    $params = [
                            'userid' => $userid,
                            'instance' => $eportfolio->eportfolioid,
                    ];

                    $record = $DB->get_record_sql($sql, $params);

                    if (!empty($record)) {

                        $exportdata = (object) [
                                'courseid' => $record->courseid,
                                'userid' => $record->userid,
                                'user' => $record->firstname . ' ' . $record->lastname,
                                'grade' => $record->grade,
                                'feedbacktext' => $record->feedbacktext,
                                'timecreated' => date('d.m.Y', $record->timecreated),
                                'timemodified' => date('d.m.Y', $record->timemodified),
                        ];

                        writer::with_context($context)->export_data([], $exportdata);
                    }

                    // Get user data for grader.
                    $sqlgr = "SELECT eg.courseid, eg.graderid, eg.timecreated, eg.timemodified, u.firstname, u.lastname
                    FROM {eportfolio_grade} eg
                    JOIN {user} u ON eg.graderid = u.id
                    WHERE eg.graderid = :graderid AND instance = :instance";
                    $paramsgr = [
                            'graderid' => $userid,
                            'instance' => $eportfolio->eportfolioid,
                    ];

                    $recordgr = $DB->get_record_sql($sqlgr, $paramsgr);

                    if (!empty($recordgr)) {

                        $exportdata = (object) [
                                'courseid' => $recordgr->courseid,
                                'graderid' => $recordgr->graderid,
                                'grader' => $recordgr->firstname . ' ' . $recordgr->lastname,
                                'timecreated' => date('d.m.Y', $recordgr->timecreated),
                                'timemodified' => date('d.m.Y', $recordgr->timemodified),
                        ];

                        writer::with_context($context)->export_data([], $exportdata);
                    }

                }
            }
        }
        return;
    }

    /**
     * Deletes all user data for the specified contexts.
     *
     * @param context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        return;
    }

    /**
     * Deletes all user data for the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved context list.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        // Delete all entries from eportfolio_grade for usermodified.
        $DB->delete_records('eportfolio_grade', [
                'usermodified' => $userid,
        ]);

        // Delete all entries from eportfolio_grade for graderid.
        $DB->delete_records('eportfolio_grade', [
                'graderid' => $userid,
        ]);

        // Delete all entries from eportfolio_grade for userid.
        $DB->delete_records('eportfolio_grade', [
                'userid' => $userid,
        ]);

    }

    /**
     * Delete data for all users.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $users = $userlist->get_userids();

        foreach ($users as $userid) {

            // Delete all entries from eportfolio_grade for usermodified.
            $DB->delete_records('eportfolio_grade', [
                    'usermodified' => $userid,
            ]);

            // Delete all entries from eportfolio_grade for graderid.
            $DB->delete_records('eportfolio_grade', [
                    'graderid' => $userid,
            ]);

            // Delete all entries from eportfolio_grade for userid.
            $DB->delete_records('eportfolio_grade', [
                    'userid' => $userid,
            ]);

        }

    }
}

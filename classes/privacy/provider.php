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
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\core_userlist_provider;

/**
 * Privacy provider implementation for mod_eportfolio plugin.
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        core_userlist_provider {

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
        return contextlist();
    }

    /**
     * Get the list of users within a specific context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        return;
    }

    /**
     * Exports all user data for the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved context list.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        // Get user specific data from eportfolio.
        $data = $DB->get_records('eportfolio', [
                'usermodified' => $userid,
        ]);

        if (!empty($data)) {
            $exportdata = [];
            foreach ($data as $record) {
                $exportdata[] = [
                        'usermodified' => $record->usermodified,
                        'instanceid' => $record->id,
                        'courseid' => $record->course,
                        'name' => $record->name,
                ];
            }

            writer::with_context($context)->export_data(
                    [],
                    (object) ['eportfolio' => $exportdata]
            );
        }

        // Get usermodified data from eportfolio_grade.
        $data = $DB->get_records('eportfolio_grade', [
                'usermodified' => $userid,
        ]);

        if (!empty($data)) {
            $exportdata = [];
            foreach ($data as $record) {
                $exportdata[] = $record;
            }

            writer::with_context($context)->export_data(
                    [],
                    (object) ['eportfolio_grade' => $exportdata]
            );
        }

        // Get grader data from eportfolio_grade.
        $data = $DB->get_records('eportfolio_grade', [
                'graderid' => $userid,
        ]);

        if (!empty($data)) {
            $exportdata = [];
            foreach ($data as $record) {
                $exportdata[] = $record;
            }

            writer::with_context($context)->export_data(
                    [],
                    (object) ['eportfolio_grader' => $exportdata]
            );
        }

        // Get graded user data from eportfolio_grade.
        $data = $DB->get_records('eportfolio_grade', [
                'userid' => $userid,
        ]);

        if (!empty($data)) {
            $exportdata = [];
            foreach ($data as $record) {
                $exportdata[] = $record;
            }

            writer::with_context($context)->export_data(
                    [],
                    (object) ['eportfolio_graded' => $exportdata]
            );
        }
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

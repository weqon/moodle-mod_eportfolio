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
 * Task to send messages to inform trainers grading is open.
 *
 * @package mod_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_eportfolio\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Task to send messages to inform trainers grading is open.
 */
class send_messages_grading_duedate extends \core\task\scheduled_task {

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name() {
        return get_string('task:messages:gradingduedate', 'mod_eportfolio');
    }

    /**
     * Executes the process for sending messages.
     */
    public function execute() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/local/eportfolio/locallib.php');

        // Get config to receive set grading teachers.
        $config = get_config('local_eportfolio');

        // Get gradingduedate from DB.
        $sendgradingduedatemessage = $DB->get_records('eportfolio', ['gradingduedate_send' => 0]);

        if (!empty($sendgradingduedatemessage)) {
            // Loop through each entry.
            foreach ($sendgradingduedatemessage as $ssm) {
                // Check, if value for gradingduedate is set.
                if (!empty($ssm->gradingduedate) && $ssm->gradingduedate != 0) {

                    // Remind trainers that grading is due.
                    $now = time();

                    if ($now > $ssm->gradingduedate) {
                        $cm = get_coursemodule_from_instance('eportfolio', $ssm->id, $ssm->course, false, MUST_EXIST);
                        $coursecontext = \context_course::instance($ssm->course);

                        // Get enrolled users by course id.
                        $enrolledusers = get_enrolled_users($coursecontext);

                        if (!empty($enrolledusers)) {
                            mtrace('Send new messages');

                            // Send messages to users.
                            foreach ($enrolledusers as $eu) {
                                // Check if enrolled user is grading teacher.
                                if (local_eportfolio_is_grading_teacher($config, $coursecontext, $eu->id)) {

                                    // Prepare data to send message.
                                    $contexturl = new \moodle_url('/mod/eportfolio/view.php', ['id' => $cm->id]);
                                    $noreplyuser = \core_user::get_noreply_user();

                                    // Holds values for the string for the email message.
                                    $a = new \stdClass;
                                    $a->viewurl = (string) $contexturl;
                                    $a->name = $ssm->name;

                                    $course = $DB->get_record('course', ['id' => $ssm->course]);
                                    $a->coursename = $course->fullname;

                                    // Fetch message HTML and plain text formats.
                                    $messagehtml = get_string('message:gradingduedate:message', 'mod_eportfolio', $a);
                                    $plaintext = format_text_email($messagehtml, FORMAT_HTML);

                                    $smallmessage = get_string('message:gradingduedate:smallmessage', 'mod_eportfolio', $a);
                                    $smallmessage = format_text_email($smallmessage, FORMAT_HTML);

                                    // Subject.
                                    $subject = get_string('message:gradingduedate:subject', 'mod_eportfolio');
                                    $subject .= ' - ' . $ssm->name;

                                    $message = new \core\message\message();

                                    $message->courseid = $ssm->course;
                                    $message->component = 'mod_eportfolio'; // Your plugin's name.
                                    $message->name = 'grading'; // Your notification name from message.php.

                                    $message->userfrom = $noreplyuser;

                                    $usertodata = $DB->get_record('user', ['id' => $eu->id]);
                                    $message->userto = $usertodata;

                                    $message->subject = $subject;
                                    $message->smallmessage = $smallmessage;
                                    $message->fullmessage = $plaintext;
                                    $message->fullmessageformat = FORMAT_PLAIN;
                                    $message->fullmessagehtml = $messagehtml;
                                    $message->notification = 1; // Notification generated from Moodle, not a user-to-user message.
                                    $message->contexturl = $contexturl->out(false);
                                    $message->contexturlname = get_string('message:gradingduedate:contexturlname', 'mod_eportfolio');

                                    // Finally send the message.
                                    $messageid = message_send($message);

                                    if ($messageid) {
                                        mtrace('Message sent to user ID: ' . $usertodata->id);
                                        $ssm->gradingduedate_send = 1;
                                        $DB->update_record('eportfolio', $ssm);
                                    } else {
                                        mtrace('Failed to send message to user ID: ' . $usertodata->id);
                                    }
                                }
                            }
                        } else {
                            mtrace('No enrolled users found');
                        }
                    }
                }
            }
        } else {
            mtrace('No new messages send');
        }

        return true;
    }
}
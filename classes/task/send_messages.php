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
 * Adhoc task for sending messages.
 *
 * @package mod_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_eportfolio\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/eportfolio/locallib.php');

/**
 * Adhoc task for sending messages.
 */
class send_messages extends \core\task\adhoc_task {

    // Use the logging trait.
    use \core\task\logging_trait;

    /**
     * Executes the send messages task.
     */
    public function execute() {
        global $DB;

        $data = $this->get_custom_data();

        $contexturl = new \moodle_url('/mod/eportfolio/view.php', ['id' => $data->cmid]);

        // Holds values for the string for the email message.
        $a = new \stdClass;

        $userfromdata = $DB->get_record('user', ['id' => $data->userfrom]);
        $a->userfrom = fullname($userfromdata);
        $a->filename = $data->filename;
        $a->viewurl = (string) $contexturl;

        $course = $DB->get_record('course', ['id' => $data->courseid]);
        $a->coursename = $course->fullname;

        // Fetch message HTML and plain text formats.
        $messagehtml = get_string('message:emailmessage', 'mod_eportfolio', $a);
        $plaintext = format_text_email($messagehtml, FORMAT_HTML);

        $smallmessage = get_string('message:smallmessage', 'mod_eportfolio', $a);
        $smallmessage = format_text_email($smallmessage, FORMAT_HTML);

        // Subject.
        $subject = get_string('message:subject', 'mod_eportfolio');

        $message = new \core\message\message();

        $message->courseid = $data->courseid;
        $message->component = 'mod_eportfolio'; // Your plugin's name.
        $message->name = 'grading'; // Your notification name from message.php.

        $message->userfrom = \core_user::get_noreply_user();

        $usertodata = $DB->get_record('user', ['id' => $data->userto]);
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
        $messageid = message_send($message);

        if ($messageid) {
            mtrace('Message sent to user ID: ' . $data->userto);
        } else {
            mtrace('Failed to send message to user ID: ' . $data->userto);
        }
    }
}

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
 * The main mod_eportfolio configuration form.
 *
 * @package     mod_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package     mod_eportfolio
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_eportfolio_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('eportfolioname', 'mod_eportfolio'), ['size' => '64']);

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'feedback', get_string('eportfolio:feedback:header', 'mod_eportfolio'));

        // Select for available feedback types.
        $selectvalues = [
                '0' => get_string('eportfolio:feedback:text', 'mod_eportfolio'),
                '1' => get_string('eportfolio:feedback:file', 'mod_eportfolio'),
        ];

        $mform->addElement('select', 'feedbacktype', get_string('eportfolio:feedback:label', 'mod_eportfolio'),
                $selectvalues);

        $mform->addHelpButton('feedbacktype', 'eportfolio:feedback:label', 'mod_eportfolio');
        $mform->setType('feedbacktype', PARAM_INT);

        $mform->addElement('header', 'availability', get_string('eportfolio:availability', 'mod_eportfolio'));
        $mform->setExpanded('availability', true);

        $name = get_string('eportfolio:allowsubmission:fromdate', 'mod_eportfolio');
        $options = ['optional' => true];
        $mform->addElement('date_time_selector', 'allowsubmissionsfromdate', $name, $options);
        $mform->addHelpButton('allowsubmissionsfromdate', 'eportfolio:allowsubmission:fromdate', 'mod_eportfolio');

        $name = get_string('eportfolio:submissionduedate', 'mod_eportfolio');
        $mform->addElement('date_time_selector', 'submissionduedate', $name, ['optional' => true]);
        $mform->addHelpButton('submissionduedate', 'eportfolio:submissionduedate', 'mod_eportfolio');

        $name = get_string('eportfolio:gradingduedate', 'mod_eportfolio');
        $mform->addElement('date_time_selector', 'gradingduedate', $name, ['optional' => true]);
        $mform->addHelpButton('gradingduedate', 'eportfolio:gradingduedate', 'mod_eportfolio');

        $name = get_string('eportfolio:alwaysshowdescription', 'mod_eportfolio');
        $mform->addElement('checkbox', 'alwaysshowdescription', $name);
        $mform->addHelpButton('alwaysshowdescription', 'eportfolio:alwaysshowdescription', 'mod_eportfolio');
        $mform->disabledIf('alwaysshowdescription', 'allowsubmissionsfromdate[enabled]', 'notchecked');

        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }

    /**
     * Perform minimal validation on the settings form
     *
     * @param array $data
     * @param array $files
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['allowsubmissionsfromdate']) && !empty($data['submissionduedate'])) {
            if ($data['submissionduedate'] <= $data['allowsubmissionsfromdate']) {
                $errors['submissionduedate'] = get_string('eportfolio:duedateaftersubmission:validation', 'mod_eportfolio');
            }
        }
        if ($data['gradingduedate']) {
            if ($data['allowsubmissionsfromdate'] && $data['allowsubmissionsfromdate'] > $data['gradingduedate']) {
                $errors['gradingduedate'] = get_string('eportfolio:gradingduefromdate:validation', 'mod_eportfolio');
            }
            if ($data['submissionduedate'] && $data['submissionduedate'] > $data['gradingduedate']) {
                $errors['gradingduedate'] = get_string('eportfolio:gradingdueduedate:validation', 'mod_eportfolio');
            }
        }

        return $errors;
    }
}

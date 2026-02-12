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
 *
 * @package     mod_eportfolio
 * @copyright   2025 weQon UG {@link https://weqon.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . "/mod/eportfolio/locallib.php");

/**
 * Viewing the grade form.
 */
class grade_form_feedback extends moodleform {

    /**
     * Building the form.
     *
     * @return void
     */
    public function definition() {
        global $DB;

        $mform = $this->_form; // Don't forget the underscore!

        $customdata = $this->_customdata;

        $mform->addElement('hidden', 'eportid', $this->_customdata['eportid']);
        $mform->setType('eportid', PARAM_INT);
        $mform->addElement('hidden', 'userid', $this->_customdata['userid']);
        $mform->setType('userid', PARAM_INT);
        $mform->addElement('hidden', 'cmid', $this->_customdata['cmid']);
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'fileidcontext', $this->_customdata['fileidcontext']);
        $mform->setType('fileidcontext', PARAM_INT);
        $mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
        $mform->setType('courseid', PARAM_INT);

        if ($this->_customdata['feedbacktextset']) {
            $mform->addElement('hidden', 'feedbacktextset', $this->_customdata['feedbacktextset']);
            $mform->setType('feedbacktextset', PARAM_BOOL);
        }

        if ($this->_customdata['feedbackfileset']) {
            $mform->addElement('hidden', 'feedbackfileset', $this->_customdata['feedbackfileset']);
            $mform->setType('feedbackfileset', PARAM_BOOL);
        }

        $mform->addElement('html', '<h3>' . get_string('gradeform:header', 'mod_eportfolio') . '</h3><br>');

        if ($this->_customdata['grade'] > 0) {
            // Point/percentage rating -> Simple text field.
            $max = new stdClass();
            $max->grade = $this->_customdata['grade'];

            $mform->addElement('text', 'grade', get_string('gradeform:grade:point', 'mod_eportfolio', $max), ['size' => 3]);
            $mform->setType('grade', PARAM_INT);
            $mform->addHelpButton('grade', 'gradeform:grade:point', 'mod_eportfolio');

        } else if ($this->_customdata['grade'] < 0) {
            // Scale rating -> Dropdown menu with scale values.
            $scaleid = abs($this->_customdata['grade']);
            $scale = $DB->get_record('scale', ['id' => $scaleid]);

            // Moodle stores scale values as CSV/comma-separated (e.g. ‘unsatisfactory, satisfactory, good, very good’).
            $scaleoptions = explode(',', $scale->scale);
            $tempoptions = array_combine(
                    range(1, count($scaleoptions)),
                    $scaleoptions
            );

            $options = [0 => get_string('gradeform:scale:nograde', 'mod_eportfolio')] + $tempoptions;

            $mform->addElement('select', 'grade', get_string('gradeform:grade:scale', 'mod_eportfolio'), $options);
            $mform->setType('grade', PARAM_INT);
        }

        if ($this->_customdata['feedbacktextset']) {
            $mform->addElement('textarea', 'feedbacktext', get_string('gradeform:feedbacktext', 'mod_eportfolio'),
                    'wrap="virtual" rows="10" cols="30"');
            $mform->setType('feedbacktext', PARAM_TEXT);
        }

        if ($this->_customdata['feedbackfileset']) {
            $mform->addElement('filemanager', 'feedbackfile', get_string('gradeform:feedbackfile', 'mod_eportfolio'), null,
                    $customdata['filemanageropts']);
            $mform->setDefault('feedbackfile', $customdata['feedbackfile']);
        }

        $mform->addElement('html', '<hr><hr>');

        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'save', get_string('gradeform:savegrade', 'mod_eportfolio'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
    }

}

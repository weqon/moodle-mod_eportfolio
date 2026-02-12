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
 * Plugin strings are defined here.
 *
 * @package     mod_eportfolio
 * @category    string
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'ePortfolio Grading';
$string['modulename'] = 'ePortfolio Grading';
$string['modulenameplural'] = 'ePortfolio Grading';
$string['pluginadministration'] = 'ePortfolio Grading administration';
$string['resetuserdata'] = 'Reset user data ePortfolio Grading';
$string['resetplugininstances'] = 'Reset ePortfolio Grading';

// Mod form.
$string['eportfolioname'] = 'Title';
$string['eportfolio:feedback:header'] = 'Settings for ePortfolio grading';
$string['eportfolio:feedback:label'] = 'Select feedback type';
$string['eportfolio:feedback:text'] = 'Feedback comments';
$string['eportfolio:feedback:file'] = 'File feedback';
$string['eportfolio:feedback:label_help'] = 'Available types:<br>
<b>Feedback comments:</b> A text field for entering feedback in text form is displayed in the rating overview.<br><br>
<b>File feedback:</b> An input field for uploading a feedback file is displayed in the rating overview.';
$string['eportfolio:feedback:allowedfiletypes'] = 'Accepted file types';
$string['eportfolio:feedback:allowedfiletypes_help'] = 'The accepted file types can be restricted as a comma-separated list with file extensions.
If the field is empty, all file types are allowed.';

$string['eportfolio:availability'] = 'Availability';
$string['eportfolio:allowsubmission:fromdate'] = 'Allow submissions from';
$string['eportfolio:allowsubmission:fromdate_help'] =
        'If enabled, students will not be able to submit before this date. If disabled, students will be able to start submitting right away.';
$string['eportfolio:submissionduedate'] = 'Due date';
$string['eportfolio:submissionduedate_help'] = 'This is when the submission is due.';
$string['eportfolio:gradingduedate'] = 'Remind me to grade by';
$string['eportfolio:gradingduedate_help'] =
        'The expected date that marking of the submissions should be completed by. This date is used to send notifications to trainers.';
$string['eportfolio:alwaysshowdescription'] = 'Always show description';
$string['eportfolio:alwaysshowdescription_help'] =
        'If disabled, the ePortfolio description above will only become visible to students on the "Allow submissions from" date.';
$string['eportfolio:duedateaftersubmission:validation'] = 'Due date must be after the allow submissions from date.';
$string['eportfolio:gradingduefromdate:validation'] =
        'Remind me to grade by date cannot be earlier than the allow submissions from date.';
$string['eportfolio:gradingdueduedate:validation'] = 'Remind me to grade by date cannot be earlier than the due date.';
$string['eportfolio:feedbacktypes:validation'] = 'You must select at least one feedback type..';

// Index page.
$string['noeportfolioinstances'] = 'No ePortfolio activities have been created in this course yet!';
$string['activitydate:submissionsdue'] = 'Due:';
$string['activitydate:submissionsopen'] = 'Opens:';
$string['activitydate:submissionsopened'] = 'Opened:';

// Capabilities - db/access - permissions.
$string['eportfolio:addinstance'] = 'Add new ePortfolio Grading';
$string['eportfolio:grade_eport'] = 'Grade ePortfolio';
$string['eportfolio:view'] = 'View ePortfolio';

$string['error:noeportfoliocourse'] = 'This course has not been marked as an ePortfolio course!';
$string['error:noeportfolios:found'] = 'There are currently no ePortfolios available for grading!';
$string['error:noeportfolios:found:student'] = 'You have not yet submitted an ePortfolio for grading!
Open your ePortfolio and select one for grading.';
$string['error:noeportfolios:found:student:link'] = 'View my ePortfolio';
$string['error:noeportfolio:file:found'] = 'The requested file could not be found!';
$string['error:missingcapability:actions'] =
        'You do not have the required capabilities to perform the selected action!';

$string['actions:header'] = 'Perform action';

// Overview table.
$string['overview:table:title'] = 'Filename/Title';
$string['overview:table:userfullname'] = 'Shared by';
$string['overview:table:sharestart'] = 'Shared on';
$string['overview:table:grade'] = 'Grading';
$string['overview:table:actions'] = 'Actions';

$string['overview:table:btn:grade'] = 'Add grading';
$string['overview:table:btn:view'] = 'View grading';
$string['overview:table:btn:delete'] = 'Allow new submission';
$string['overview:table:btn:delete:help'] = 'Clicking on "Allow new submission" will remove the current submission and delete the existing grade.
Course participants will be given the option to resubmit their submission, e.g. to provide a corrected version.';

// Grading form.
$string['gradeform:header'] = 'Grade & Feedback';
$string['gradeform:grade:point'] = 'Grade (max. {$a->grade})';
$string['gradeform:grade:point_help'] = 'Specify grading as points/percentage.';
$string['gradeform:grade:scale'] = 'Grade';
$string['gradeform:scale:nograde'] = 'No grade';
$string['gradeform:feedbacktext'] = 'Feedback as comment';
$string['gradeform:feedbackfile'] = 'Feedback as file';
$string['gradeform:gradeview'] = 'Grade';
$string['gradeform:grader'] = 'Grading by';
$string['gradeform:timegraded'] = 'Graded on';
$string['gradeform:backbtn'] = 'Back to overview';
$string['gradeform:savegrade'] = 'Save grade';

// Insert & Update grading.
$string['grade:insert:success'] = 'Your grading has been successfully saved!';
$string['grade:insert:error'] = 'An error occurred while saving the grading! Please try again!';
$string['grade:cancelled'] = 'The grading was cancelled!';

// Message provider.
$string['messageprovider:grading'] = 'Notification about new assessments for ePortfolio';
$string['message:emailmessage'] =
        '<p>A new grade has been added for you.<br>ePortfolio: {$a->filename}<br>Course: {$a->coursename}<br>
<br>Grading by: {$a->userfrom}<br>URL:  <a href="{$a->viewurl}">{$a->viewurl}</a></p>';
$string['message:smallmessage'] =
        '<p>A new grade has been added for you.<br>ePortfolio: {$a->filename}<br>Course: {$a->coursename}<br>
<br>Grading by: {$a->userfrom}<br>URL:  <a href="{$a->viewurl}">{$a->viewurl}</a></p>';
$string['message:subject'] = 'Notification about new assessments for ePortfolio';
$string['message:contexturlname'] = 'View grade for ePortfolio';
$string['message:allowsubmission:message'] =
        '<p>You can now submit your ePortfolio in the course {$a->coursename} for the activity {$a->name}.
<br><a href="{$a->viewurl}">{$a->viewurl}</a></p>';
$string['message:allowsubmission:smallmessage'] =
        '<p>You can now submit your ePortfolio in the course {$a->coursename} for the activity {$a->name}.
<br><a href="{$a->viewurl}">{$a->viewurl}</a></p>';
$string['message:allowsubmission:subject'] = 'Activity ePortfolio grading open for submission';
$string['message:allowsubmission:contexturlname'] = 'View ePortfolio grading activity';
$string['message:submissionduedate:message'] =
        '<p>The submission for the activity {$a->name} in the course {$a->coursename} is due soon. Please submit your ePortfolio by {$a->duedate}.
<br><a href="{$a->viewurl}">{$a->viewurl}</a></p>';
$string['message:submissionduedate:smallmessage'] =
        '<p>The submission for the activity {$a->name} in the course {$a->coursename} is due soon. Please submit your ePortfolio by {$a->duedate}.
<br><a href="{$a->viewurl}">{$a->viewurl}</a></p>';
$string['message:submissionduedate:subject'] = 'Activity ePortfolio grading submission is due soon';
$string['message:submissionduedate:contexturlname'] = 'View ePortfolio grading activity';
$string['message:gradingduedate:message'] =
        '<p>Please grade the submitted ePortfolios in the activity {$a->name} in the course {$a->coursename}.
<br><a href="{$a->viewurl}">{$a->viewurl}</a></p>';
$string['message:gradingduedate:smallmessage'] =
        '<p>Please grade the submitted ePortfolios in the activity {$a->name} in the course {$a->coursename}.
<br><a href="{$a->viewurl}">{$a->viewurl}</a></p>';
$string['message:gradingduedate:subject'] = 'Reminder activity ePortfolio grading';
$string['message:gradingduedate:contexturlname'] = 'View ePortfolio grading activity';

// Delete shared ePortfolio.
$string['delete:header'] = 'Allow new submission?';
$string['delete:confirm'] = 'Confirm';
$string['delete:checkconfirm'] = '<b>Do you really want to allow a new submission for this file?</b><br><br>
<b>The submitted file and any existing grades will also be deleted!</b>';
$string['delete:success'] = 'The selected file was deleted successfully!';
$string['delete:error'] = 'There was an error while deleting the file! Please try again!';

// Events.
$string['event:eportfolio:deleted:name'] = 'ePortfolio deleted';
$string['event:eportfolio:deleted'] =
        'The user with the id \'{$a->userid}\' deleted ePortfolio {$a->filename} (fileidcontext: \'{$a->fileidcontext}\')';
$string['event:eportfolio:viewgrading:name'] = 'ePortfolio grade viewed';
$string['event:eportfolio:viewgrading'] =
        'The user with the id \'{$a->userid}\' viewed grade for ePortfolio {$a->filename} (fileidcontext: \'{$a->fileidcontext}\')';
$string['event:eportfolio:newgrading:name'] = 'ePortfolio new grade';
$string['event:eportfolio:newgrading'] =
        'The user with the id \'{$a->userid}\' added new grade for ePortfolio {$a->filename} (fileidcontext: \'{$a->fileidcontext}\')';
$string['event:eportfolio:updatedgrade:name'] = 'ePortfolio updated grade';
$string['event:eportfolio:updatedgrade'] =
        'The user with the id \'{$a->userid}\' updated the grade for ePortfolio {$a->filename} (fileidcontext: \'{$a->fileidcontext}\')';

// Tasks.
$string['task:messages:allowsubmission'] = 'Send messages to inform students submission is open';
$string['task:messages:gradingduedate'] = 'Send messages to inform trainers grading is open';
$string['task:messages:submissionduedate'] = 'Send messages to inform students submission is due';

// Settings.
$string['settings:general'] = 'Settings';
$string['settings:maxuploadfilezise'] = 'Maximum file size';
$string['settings:maxuploadfilezise:desc'] = 'This setting sets the maximum file size allowed for uploading feedback files.';

// Privacy provider.
$string['privacy:metadata:mod_eportfolio'] = 'Data shared by the ePortfolio plugin';
$string['privacy:metadata:mod_eportfolio:usermodified'] = 'The ID of the user who created/updated the ePortfolio activity';
$string['privacy:metadata:mod_eportfolio:grade:usermodified'] = 'The ID of the user who created/updated the ePortfolio grade';
$string['privacy:metadata:mod_eportfolio:grade:userid'] = 'The ID of the user who received the ePortfolio grade';
$string['privacy:metadata:mod_eportfolio:grade:graderid'] = 'The ID of the user who created/updated the ePortfolio grade';

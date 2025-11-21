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

$string['pluginname'] = 'ePortfolio Bewertung';
$string['modulename'] = 'ePortfolio Bewertung';
$string['modulenameplural'] = 'ePortfolio Bewertung';
$string['pluginadministration'] = 'ePortfolio Bewertung Administration';

// Mod form.
$string['eportfolioname'] = 'Titel';
$string['eportfolio:feedback:header'] = 'Einstellungen ePortfolio Bewertung';
$string['eportfolio:feedback:label'] = 'Feedback Typ auswählen';
$string['eportfolio:feedback:text'] = 'Feedback als Kommentar';
$string['eportfolio:feedback:file'] = 'Feedback als Datei';
$string['eportfolio:feedback:label_help'] = 'Verfügbare Typen:<br>
<b>Feedback als Kommentar:</b> In der Bewertungsübersicht wird ein Textfeld zur Eingabe eines Feedbacks in Textform eingeblendet.<br><br>
<b>Feedback als Datei:</b> In der Bewertungsübersicht wird ein Eingabefeld zum Hochladen einer Feedback-Datei eingeblendet.';

$string['eportfolio:availability'] = 'Verfügbarkeit';
$string['eportfolio:allowsubmission:fromdate'] = 'Abgabebeginn';
$string['eportfolio:allowsubmission:fromdate_help'] =
        'Wenn diese Option aktiviert ist, können ePortfolios nicht vor diesem Zeitpunkt eingereicht werden.
        Wenn diese Option deaktiviert ist, ist die Einreichung sofort möglich.';
$string['eportfolio:submissionduedate'] = 'Fälligkeitsdatum';
$string['eportfolio:submissionduedate_help'] =
        'Zum Fälligkeitsdatum wird die Abgabe des ePortfolios fällig. Eine Einreichung danach ist nicht mehr möglich.';
$string['eportfolio:gradingduedate'] = 'An Bewertung erinnern';
$string['eportfolio:gradingduedate_help'] =
        'Dieser voraussichtliche Termin markiert den Abschluss der Einreichung. Dieses Datum wird verwendet, um Benachrichtigungen an Trainer/innen zu verschicken.';
$string['eportfolio:alwaysshowdescription'] = 'Beschreibung immer anzeigen';
$string['eportfolio:alwaysshowdescription_help'] =
        'Wenn diese Option deaktiviert ist, wird die Beschreibung für Teilnehmer/innen nur ab dem Abgabebeginn angezeigt.';
$string['eportfolio:duedateaftersubmission:validation'] = 'Das Fälligkeitsdatum muss nach dem Abgabebeginn liegen.';
$string['eportfolio:gradingduefromdate:validation'] =
        'An die Bewertung erinnern kann nicht vor dem Datum für Abgabebeginn liegen.';
$string['eportfolio:gradingdueduedate:validation'] = 'An die Bewertung erinnern kann nicht vor dem Fälligkeitsdatum liegen.';

// Index page.
$string['noeportfolioinstances'] = 'In diesem Kurs wurden noch keine ePortfolio Aktivitäten angelegt!';
$string['activitydate:submissionsdue'] = 'Fällig:';
$string['activitydate:submissionsopen'] = 'Öffnet:';
$string['activitydate:submissionsopened'] = 'Geöffnet:';

// Capabilities - db/access - permissions.
$string['eportfolio:addinstance'] = 'Neues ePortfolio Bewertung hinzufügen';
$string['eportfolio:grade_eport'] = 'ePortfolio bewerten';
$string['eportfolio:view'] = 'ePortfolio anzeigen';

$string['error:noeportfoliocourse'] = 'Dieser Kurs wurde nicht als ePortfolio Kurs markiert!';
$string['error:noeportfolios:found'] = 'Aktuell liegen keine ePortfolios zur Bewertung vor!';
$string['error:noeportfolios:found:student'] = 'Sie haben bisher noch kein ePortfolio zur Bewertung eingereicht!
Öffnen Sie Ihr ePortfolio und reichen Sie eine Datei zur Bewertung ein.';
$string['error:noeportfolios:found:student:link'] = 'Mein ePortfolio anzeigen';
$string['error:noeportfolio:file:found'] = 'Die aufgerufene Datei konnte nicht gefunden werden!';
$string['error:missingcapability:actions'] =
        'Sie haben nicht die erforderlichen Berechtigungen, um die gewählte Aktion auszuführen!';

$string['actions:header'] = 'Aktion ausführen';

// Overview table.
$string['overview:table:title'] = 'Titel';
$string['overview:table:userfullname'] = 'Geteilt von';
$string['overview:table:sharestart'] = 'Geteilt am';
$string['overview:table:grade'] = 'Bewertung';
$string['overview:table:actions'] = 'Aktionen';

$string['overview:table:btn:grade'] = 'Bewerten';
$string['overview:table:btn:view'] = 'Anzeigen';
$string['overview:table:btn:delete'] = 'Neue Freigabe erlauben';
$string['overview:table:btn:delete:help'] = 'Mit Klick auf "Neue Freigabe erlauben" wird die aktuelle Einreichung entfernt und die bisher gesetzten Bewertungen gelöscht.
Die Kursteilnehmer/innen erhalten die Möglichkeit, ihre Einreichung erneut durchzuführen, z. B. um eine korrigierte Version bereitzustellen.';

// Grading form.
$string['gradeform:header'] = 'Benotung & Feedback';
$string['gradeform:grade'] = 'Benotung (in %)';
$string['gradeform:grade_help'] = 'Benotung in Prozent angeben.';
$string['gradeform:feedbacktext'] = 'Feedback als Kommentar';
$string['gradeform:feedbackfile'] = 'Feedback als Datei';
$string['gradeform:gradeview'] = 'Benotung';
$string['gradeform:grader'] = 'Bewertet durch';
$string['gradeform:timegraded'] = 'Bewertet am';
$string['gradeform:backbtn'] = 'Zurück zur Übersicht';
$string['gradeform:savegrade'] = 'Bewertung abgeben';

// Insert & Update grading.
$string['grade:insert:success'] = 'Ihre Bewertung wurde erfolgreich gespeichert!';
$string['grade:insert:error'] = 'Beim Speichern der Benotung ist ein Fehler aufgetreten! Bitte versuchen Sie es erneut!';
$string['grade:update:success'] = 'Ihre Bewertung wurde erfolgreich aktualisiert!';
$string['grade:update:error'] = 'Beim Aktualisieren der Benotung ist ein Fehler aufgetreten! Bitte versuchen Sie es erneut!';
$string['grade:cancelled'] = 'Die Bewertung wurde abgebrochen!';

// Message provider.
$string['messageprovider:grading'] = 'Mitteilung über neue Bewertungen für ePortfolio';
$string['message:emailmessage'] =
        '<p>Für Sie wurde eine neue Bewertung hinterlegt.<br>Eingereichtes ePortfolio: {$a->filename}<br>Kurs: {$a->coursename}<br>
<br>Bewertet durch: {$a->userfrom}<br>URL zur Einreichung: <a href="{$a->viewurl}">{$a->viewurl}</a></p>';
$string['message:smallmessage'] =
        '<p>Für Sie wurde eine neue Bewertung hinterlegt.<br>Eingereichtes ePortfolio: {$a->filename}<br>Kurs: {$a->coursename}<br>
<br>Bewertet durch: {$a->userfrom}<br>URL zur Einreichung:  <a href="{$a->viewurl}">{$a->viewurl}</a></p>';
$string['message:subject'] = 'Mitteilung über eine neue Bewertung für Ihr ePortfolio';
$string['message:contexturlname'] = 'Bewertung für ePortfolio anzeigen';
$string['message:allowsubmission:message'] =
        '<p>Sie können jetzt Ihr ePortfolio im Kurs {$a->coursename} in der Aktivität {$a->name} einreichen.
<br><a href="{$a->viewurl}">{$a->viewurl}</a></p>';
$string['message:allowsubmission:smallmessage'] =
        '<p>Sie können jetzt Ihr ePortfolio im Kurs {$a->coursename} in der Aktivität {$a->name} einreichen.
<br><a href="{$a->viewurl}">{$a->viewurl}</a></p>';
$string['message:allowsubmission:subject'] = 'Einreichungen für die Aktivität ePortfolio Bewertung';
$string['message:allowsubmission:contexturlname'] = 'Aktivität ePortfolio Bewertung anzeigen';
$string['message:submissionduedate:message'] =
        '<p>Die Einreichung für die Aktivität {$a->name} im Kurs {$a->coursename} ist bald fällig. Bitte reichen Sie Ihr ePortfolio bis zum {$a->duedate} ein.
<br><a href="{$a->viewurl}">{$a->viewurl}</a></p>';
$string['message:submissionduedate:smallmessage'] =
        '<p>Die Einreichung für die Aktivität {$a->name} im Kurs {$a->coursename} ist bald fällig. Bitte reichen Sie Ihr ePortfolio bis zum {$a->duedate} ein.
<br><a href="{$a->viewurl}">{$a->viewurl}</a></p>';
$string['message:submissionduedate:subject'] = 'Die Einreichung für die Aktivität ePortfolio Bewertung ist bald fällig';
$string['message:submissionduedate:contexturlname'] = 'Aktivität ePortfolio Bewertung anzeigen';
$string['message:gradingduedate:message'] =
        '<p>Bitte bewerten Sie die eingereichten ePortfolios in der Aktivität {$a->name} im Kurs {$a->coursename}.
<br><a href="{$a->viewurl}">{$a->viewurl}</a></p>';
$string['message:gradingduedate:smallmessage'] =
        '<p>Bitte bewerten Sie die eingereichten ePortfolios in der Aktivität {$a->name} im Kurs {$a->coursename}.
<br><a href="{$a->viewurl}">{$a->viewurl}</a></p>';
$string['message:gradingduedate:subject'] = 'Erinnerung zur Bewertung in der Aktivität ePortfolio Bewertung';
$string['message:gradingduedate:contexturlname'] = 'Aktivität ePortfolio Bewertung anzeigen';

// Delete shared ePortfolio.
$string['delete:header'] = 'Neue Freigabe erlauben?';
$string['delete:confirm'] = 'Löschen bestätigen';
$string['delete:checkconfirm'] = '<b>Möchten Sie für die ausgewählte Datei wirklich eine neue Freigabe erlauben?</b><br><br>
<b>Die eingereichte Datei und bestehende Bewertungen werden gelöscht!</b>';
$string['delete:success'] = 'Datei wurde erfolgreich gelöscht!';
$string['delete:error'] = 'Beim Löschen der Datei ist ein Fehler aufgetreten! Bitte versuchen Sie es erneut!';

// Events.
$string['event:eportfolio:deleted:name'] = 'ePortfolio aus Bewertung gelöscht';
$string['event:eportfolio:deleted'] =
        'The user with the id \'{$a->userid}\' deleted ePortfolio {$a->filename} (fileidcontext: \'{$a->fileidcontext}\')';
$string['event:eportfolio:viewgrading:name'] = 'ePortfolio Bewertung angezeigt';
$string['event:eportfolio:viewgrading'] =
        'The user with the id \'{$a->userid}\' viewed grade for ePortfolio {$a->filename} (fileidcontext: \'{$a->fileidcontext}\')';
$string['event:eportfolio:newgrading:name'] = 'ePortfolio neue Bewertung';
$string['event:eportfolio:newgrading'] =
        'The user with the id \'{$a->userid}\' added new grade for ePortfolio {$a->filename} (fileidcontext: \'{$a->fileidcontext}\')';
$string['event:eportfolio:updatedgrade:name'] = 'ePortfolio Bewertung aktualisiert';
$string['event:eportfolio:updatedgrade'] =
        'The user with the id \'{$a->userid}\' updated the grade for ePortfolio {$a->filename} (fileidcontext: \'{$a->fileidcontext}\')';

// Tasks.
$string['task:messages:allowsubmission'] = 'Mitteilung an Teilnehmer:innen, dass die Einreichung begonnen hat';
$string['task:messages:gradingduedate'] = 'Mitteilung an Trainer:innen über anstehende Bewertungen';
$string['task:messages:submissionduedate'] = 'Mitteilung an Teilnehmer:innen über anstehende Einreichefrist';

// Settings.
$string['settings:general'] = 'Einstellungen';
$string['settings:maxuploadfilezise'] = 'Maximale Dateigröße';
$string['settings:maxuploadfilezise:desc'] =
        'Diese Einstellung legt fest, welche maximale Dateigröße für das Hochladen von Feedback-Dateien erlaubt ist.';

// Privacy provider.
$string['privacy:metadata:mod_eportfolio'] = 'Vom ePortfolio-Plugin freigegebene Daten';
$string['privacy:metadata:mod_eportfolio:usermodified'] = 'Nutzer:innen ID, die die ePortfolio Aktivität angelegt/bearbeitet hat';
$string['privacy:metadata:mod_eportfolio:grade:usermodified'] =
        'Nutzer:innen ID, die die ePortfolio Bewertung angelegt/bearbeitet hat';
$string['privacy:metadata:mod_eportfolio:grade:userid'] = 'Nutzer:innen ID, die die ePortfolio Bewertung erhalten hat';
$string['privacy:metadata:mod_eportfolio:grade:graderid'] = 'Nutzer:innen ID, die die ePortfolio Bewertung durchgeführt hat';

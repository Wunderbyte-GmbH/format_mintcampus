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
 * Mintcampus Format.
 *
 * @package    format_mintcampus
 * @version    See the value of '$plugin->version' in version.php.
 * @author     Based on code originally written by Paul Krix and Julian Ridden.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['topic'] = 'Section';
$string['topic0'] = 'General';
$string['module'] = 'Modul';

$string['addsection'] = 'Modul hinzufügen';
$string['hidefromothers'] = 'Modul verbergen'; // No longer used kept for legacy versions.
$string['showfromothers'] = 'Modul anzeigen'; // No longer used kept for legacy versions.
$string['currentsection'] = 'Dieses Modul'; // No longer used kept for legacy versions.
$string['markedthissection'] = 'Dieses Modul ist als aktuelles Modul markieren';
$string['markthissection'] = 'Dieses Modul als das aktuelle Modul markieren';

// Moodle 3.0 Enhancement.
$string['editsection'] = 'Modul anpassen';
$string['deletesection'] = 'Modul löschen';

// MDL-51802.
$string['editsectionname'] = 'Modulnamen anpassen';
$string['newsectionname'] = 'Neuer Modulname {$a}';

// Moodle 2.4 Course format refactoring - MDL-35218.
$string['numbersections'] = 'Anzahl der Module';

// Setting general.
$string['default'] = 'Default - {$a}';

// Section image.
$string['sectionimage'] = 'Modulbild';
$string['sectionimage_help'] = 'Das Bild für die Modulkachel im Kurs';
$string['sectionimagealttext'] = 'Image alt text';
$string['sectionimagealttext_help'] = 'This text will be set as the image alt attribute.';

// Image container width.
$string['imagecontainerwidth'] = 'Set the image container width';
$string['imagecontainerwidth_help'] = 'Set the image container width to one of: 128, 192, 210, 256, 320, 384, 448, 512, 576, 640, 704 or 768';
$string['defaultimagecontainerwidth'] = 'Default width of the image container';
$string['defaultimagecontainerwidth_desc'] = 'The default width of the image container.';

// Image container ratio.
$string['imagecontainerratio'] = 'Set the image container ratio relative to the width';
$string['imagecontainerratio_help'] = 'Set the image container ratio to one of: 3-2, 3-1, 3-3, 2-3, 1-3, 4-3 or 3-4.';
$string['defaultimagecontainerratio'] = 'Default ratio of the image container relative to the width';
$string['defaultimagecontainerratio_desc'] = 'The default ratio of the image container relative to the width.';

// Image resize method.
$string['scale'] = 'Scale';
$string['crop'] = 'Crop';
$string['imageresizemethod'] = 'Set the image resize method';
$string['imageresizemethod_help'] = "Set the image resize method to: 'Scale' or 'Crop' when resizing the image to fit the container.";
$string['defaultimageresizemethod'] = 'Default image resize method';
$string['defaultimageresizemethod_desc'] = 'The default method of resizing the image to fit the container.';

// Displayed image type.
$string['original'] = 'Original';
$string['webp'] = 'WebP';
$string['defaultdisplayedimagefiletype'] = 'Displayed image type';
$string['defaultdisplayedimagefiletype_desc'] = 'Set the displayed image type.';

// Course contents input
$string['modulecontent_1'] = 'Kursinhalt 1';
$string['modulecontent_2'] = 'Kursinhalt 2';
$string['modulecontent_3'] = 'Kursinhalt 3';
$string['modulecontents_help'] = 'Hier sollten Sie einer der Kursinhalte eingeben. 
Ein Inhalt kann ein Kursthema, ein Modul oder eine andere relevante Information über den Kurs sein. 
Die Liste der Inhalte wird später in der Modulkachel angezeigt.';

// Activity header
$string['coursecompletionbar'] = 'Lernfortschritt Kurs';
$string['sectioncompletionbar'] = 'Lernfortschritt Modul';
$string['captionnext'] = 'Weiter';
$string['captionback'] = 'Zurück';

// Modal.
$string['popup'] = 'Use a popup';
$string['popup_help'] = "Display the section in a popup instead of navigating to a single section page.";
$string['defaultpopup'] = 'Use a popup';
$string['defaultpopup_desc'] = "Default display the section in a popup instead of navigating to a single section page.";

// Other.
$string['information'] = 'Information';
$string['informationsettings'] = 'Information settings';
$string['informationsettingsdesc'] = 'Mintcampus format information';
$string['informationchanges'] = 'Changes';
$string['sectionchangecoursesettings'] = 'Change the number of sections in the course settings';
$string['settings'] = 'Settings';
$string['settingssettings'] = 'Settings settings';
$string['settingssettingsdesc'] = 'Mintcampus format settings';
$string['startcourse'] = "Kurs starten";
$string['continuecourse'] = "Kurs fortsetzen";
$string['coursecompleted'] = "Kurs abgeschlossen";
$string['love'] = 'love';
$string['videocourse'] = 'Video file';
$string['courserating'] = 'Bewertung';
$string['courseratingquestion'] = 'Wie hat Dir dieser Kurs gefallen?';
$string['courseratingcomment'] = 'Hinterlasse einen Kommentar:';
$string['placeholder'] = 'Schreibe einen Kommentar hier';
$string['ratingsavesuccessheader'] = 'Bewertung';
$string['ratecourse'] = 'Bewertung';
$string['ratingsavesuccess'] = 'Die Kursbewertung wurde erfolgreich gespeichert.';
$string['close'] = 'Schließen';
$string['noactivity'] = 'Bitte Aktivität für das erste Modul hinzufügen';
$string['noforumpost'] = 'Noch kein Beitrag im Forum veröffentlicht.';
$string['nocoursesummary'] = 'Bitte Kursbeschreibung hinzufügen.';
$string['nosectionactivity'] = 'Bitte Aktivität hinzufügen';
$string['hidefrommenu'] = 'Hide from menu';
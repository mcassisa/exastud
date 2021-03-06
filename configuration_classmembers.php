﻿<?php
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

// All rights reserved
/**
 * @package moodlecore
 * @subpackage blocks
 * @copyright 2013 gtn gmbh
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
*/

require("inc.php");
global $DB, $THEME;
define("MAX_USERS_PER_PAGE", 5000);

$courseid = optional_param('courseid', 1, PARAM_INT); // Course ID
$showall        = optional_param('showall', 0, PARAM_BOOL);
$searchtext     = optional_param('searchtext', '', PARAM_TEXT); // search string
$add            = optional_param('add', 0, PARAM_BOOL);
$remove         = optional_param('remove', 0, PARAM_BOOL);

require_login($courseid);

//$context = get_context_instance(CONTEXT_COURSE,$courseid);
$context = context_course::instance($courseid);
require_capability('block/exastud:use', $context);
require_capability('block/exastud:headteacher', $context);

$curPeriod = block_exabis_student_review_get_active_period(true);

if (!$class = $DB->get_record('block_exastudclass', array('userid'=>$USER->id,'periodid' => $curPeriod->id))) {
	print_error('noclassfound', 'block_exastud');
}

$header = block_exabis_student_review_get_string('configmember', 'block_exastud', $class->class);
$url = '/blocks/exastud/configuration_classmembers.php';
$PAGE->set_url($url);
block_exabis_student_review_print_header(array('configuration', '='.$header));

if ($frm = data_submitted()) {
	if(!confirm_sesskey()) {
		print_error("badsessionkey","block_exastud");
	}
	if ($add and !empty($frm->addselect)) {
		foreach ($frm->addselect as $adduser) {
			if (!$adduser = clean_param($adduser, PARAM_INT)) {
				continue;
			}
			
			$newuser = new stdClass();
			$newuser->studentid = $adduser;
			$newuser->classid = $class->id;
			$newuser->timemodified = time();
			
			if (!$DB->insert_record('block_exastudclassstudents', $newuser)) {
				error('errorinsertingstudents', 'block_exastud');
			}
		}
	} else if ($remove and !empty($frm->removeselect)) {
		foreach ($frm->removeselect as $removeuser) {
			if (!$removeuser = clean_param($removeuser, PARAM_INT)) {
				continue;
			}
			
			if (!$DB->delete_records('block_exastudclassstudents', array('studentid'=>$removeuser, 'classid'=>$class->id))) {
				error('errorremovingstudents', 'block_exabis_student_review');
			}
		}
	} else if ($showall) {
		$searchtext = '';
	}
}

$select  = "username <> 'guest' AND deleted = 0 AND confirmed = 1";
	
if ($searchtext !== '') {   // Search for a subset of remaining users
	//$LIKE      = $DB->sql_ilike();
        $LIKE      = "LIKE";
	$FULLNAME  = $DB->sql_fullname();

	$selectsql = " AND ($FULLNAME $LIKE '%$searchtext%' OR email $LIKE '%$searchtext%') ";
	$select  .= $selectsql;
} else { 
	$selectsql = ""; 
}

$availableusers = $DB->get_records_sql('SELECT id, firstname, lastname, email
									 FROM {user}
									 WHERE '.$select.'
									 AND id NOT IN (
											 SELECT studentid
											 FROM {block_exastudclassstudents}
												   WHERE classid = '.$class->id.'
												   '.$selectsql.')
									 ORDER BY lastname ASC, firstname ASC');

$usertoclasses = $DB->get_records('block_exastudclassstudents', array('classid'=>$class->id), 'studentid');

$classusers = array();
if ($usertoclasses) {
	foreach($usertoclasses as $usertoclass) {
		$classusers[] = $DB->get_record('user', array('id'=>$usertoclass->studentid));
	}
}

echo $OUTPUT->box_start();
$form_target = 'configuration_classmembers.php?courseid='.$courseid;
$userlistType = 'members';
require dirname(__FILE__).'/lib/configuration_userlist.inc.php';
echo $OUTPUT->box_end();
	
echo $OUTPUT->single_button($CFG->wwwroot . '/blocks/exastud/configuration.php?courseid='.$courseid,
					block_exabis_student_review_get_string('back', 'block_exastud'));

block_exabis_student_review_print_footer();

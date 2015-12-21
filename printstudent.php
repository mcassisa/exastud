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
global $DB;
$courseid = optional_param('courseid', 1, PARAM_INT); // Course ID
$periodid = optional_param('periodid', 0, PARAM_INT); // Course ID
$pdf = optional_param('pdf', false, PARAM_BOOL); // Course ID
$detail = optional_param('detailedreport', false, PARAM_BOOL);
$studentid = required_param('studentid', PARAM_INT); // Course ID
$classid = optional_param('classid',0,PARAM_INT);
require_login($courseid);

block_exastud_require_global_cap(block_exastud::CAP_HEADTEACHER);

$actPeriod = block_exastud_get_period($periodid);

if($classid > 0) $class = $DB->get_record("block_exastudclass", array("id"=>$classid),"*",MUST_EXIST);
else if(!$class = $DB->get_record_sql("SELECT c.* FROM {block_exastudclass} c, {block_exastudclassteachers} ct, {block_exastudclassstudents} cs
		WHERE ct.teacherid = ? AND ct.classid = cs.classid AND cs.studentid = ? GROUP BY c.id",array($USER->id,$studentid),IGNORE_MULTIPLE))
	print_error('noclassfound', 'block_exastud');

if(!$DB->record_exists("block_exastudclassteachers", array("teacherid"=>$USER->id,"classid"=>$class->id))) {
	print_error('noclassfound', 'block_exastud');
}

if(!$pdf) block_exastud_print_student_report_header();
block_exastud_print_student_report($studentid, $actPeriod->id, $class, $pdf, $detail);
if(!$pdf) block_exastud_print_student_report_footer();
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

$courseid = optional_param('courseid', 1, PARAM_INT); // Course ID
$periodid = optional_param('periodid', 0, PARAM_INT); // Period ID
global $DB,$CFG;

if($CFG->block_exastud_project_based_assessment)
	redirect('report_project.php?courseid=' . $courseid);

require_login($courseid);

block_exastud_require_global_cap(block_exastud\CAP_VIEW_REPORT);

$actPeriod = ($periodid==0 || $periodid==block_exastud_check_active_period()->id) ? block_exastud_check_active_period() : $DB->get_record('block_exastudperiod', array('id'=>$periodid));

if (!$class = $DB->get_record('block_exastudclass', array('userid'=>$USER->id,'periodid' => $actPeriod->id))) {
	print_error('noclassfound', 'block_exastud');
}

$url = '/blocks/exastud/report.php';
$PAGE->set_url($url);
$blockrenderer = $PAGE->get_renderer('block_exastud');

block_exastud_print_header('report');

$categories = ($periodid==0 || $periodid==block_exastud_check_active_period()->id) ? block_exastud_get_class_categories($class->id) : block_exastud_get_period_categories($periodid);

if(!$classusers = $DB->get_records_sql('
		SELECT s.id, s.studentid, sum(rp.value) as total
		FROM {block_exastudclassstudents} s
		JOIN {block_exastudclass} c ON s.classid = c.id
		JOIN {block_exastudreview} r ON r.periodid = c.periodid AND r.studentid = s.studentid
		JOIN {block_exastudreviewpos} rp ON rp.reviewid = r.id
		WHERE s.classid=?
		GROUP BY s.studentid
		ORDER BY total DESC', array($class->id))) {
	print_error('nostudentstoreview','block_exastud');
}


/* Print the Students */
$table = new html_table();

$table->head = array();
$table->head[] = '#'; //userpic
$table->head[] = ''; //userpic
$table->head[] = \block_exastud\get_string('name');
foreach($categories as $category)
	$table->head[] = $category->title;
$table->head[] = ''; //action

$table->align = array();
$table->align[] = 'center';
$table->align[] = 'center';
$table->align[] = 'left';
for($i=0;$i<count($categories);$i++)
	$table->align[] = 'center';
$table->align[] = 'center';
$table->width = "90%";

$i = 1;
foreach($classusers as $classuser) {
	$user = $DB->get_record('user', array('id'=>$classuser->studentid));

	if (!$user)
		continue;

	$userReport = block_exastud_get_report($user->id, $actPeriod->id);

	$link = '<a href="' . $CFG->wwwroot . '/blocks/exastud/printstudent.php?courseid=' . $courseid . '&amp;studentid=' . $user->id . '&periodid='.$periodid.'">';
	$icons = $link.'<img src="' . $CFG->wwwroot . '/blocks/exastud/pix/print.png" width="16" height="16" alt="' . \block_exastud\get_string('printversion', 'block_exastud'). '" /></a>';
	
	if($CFG->block_exastud_detailed_review) {
		$link = '<a href="' . $CFG->wwwroot . '/blocks/exastud/printstudent.php?courseid=' . $courseid . '&amp;studentid=' . $user->id . '&periodid='.$periodid.'&detailedreport=true">';
		$icons .= $link.'<img src="' . $CFG->wwwroot . '/blocks/exastud/pix/print_detail.png" width="16" height="16" alt="' . \block_exastud\get_string('printversion', 'block_exastud'). '" /></a>';
	}
	//$link = '<a href="' . $CFG->wwwroot . '/blocks/exastud/printstudent.php?courseid=' . $courseid . '&amp;studentid=' . $user->id . '&amp;sesskey=' . sesskey() . '&periodid='.$periodid.'&pdf=true">';
	//$icons .= $link.'<img src="' . $CFG->wwwroot . '/blocks/exastud/pix/pdf.png" width="23" height="16" alt="' . \block_exastud\get_string('printversion', 'block_exastud'). '" /></a>';
	
	$studentdesc = $link.fullname($user, $user->id).'</a>';
	//$studentdesc = print_user_picture($user->id, $courseid, $user->picture, 0, true, false) . ' ' . $link.fullname($user, $user->id).'</a>';

	//$table->data[] = array($studentdesc, $userReport->team, $userReport->resp, $userReport->inde, $icons);

	$data = array();
	$data[] = $i++;
	$data[] = $OUTPUT->user_picture($user,array("courseid"=>$courseid));
	$data[] = $studentdesc;

	foreach($categories as $category) {
		$data[] = @$userReport->{$category->title};
	}

	$data[] = $icons;
	$table->data[] = $data;
}

echo $blockrenderer->print_esr_table($table);

echo '<a href="' . $CFG->wwwroot . '/blocks/exastud/printclass.php?courseid=' . $courseid . '&amp;classid=' . $class->id . '&periodid='.$periodid.'"><img src="' . $CFG->wwwroot . '/blocks/exastud/pix/print.png" width="16" height="16" alt="' . \block_exastud\get_string('printall', 'block_exastud'). '" /></a>';
echo '<a href="' . $CFG->wwwroot . '/blocks/exastud/printclass.php?courseid=' . $courseid . '&amp;classid=' . $class->id . '&periodid='.$periodid.'&detailedreport=true"><img src="' . $CFG->wwwroot . '/blocks/exastud/pix/print_detail.png" width="16" height="16" alt="' . \block_exastud\get_string('printall', 'block_exastud'). '" /></a>';

echo '<form name="periodselect" action="'.$CFG->wwwroot.$url.'?courseid='.$courseid.'" method="POST">
<select name="periodid" onchange="this.form.submit();">';
foreach($DB->get_records('block_exastudperiod',null,'endtime desc') as $period) {
	$select = ($period->id==$periodid) ? " selected " : "";
	echo '<option value="'.$period->id.'"'.$select.'>'.$period->description.'</option>';
}
echo '</select></form>';

block_exastud_print_footer();
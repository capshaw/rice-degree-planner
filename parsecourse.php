<?php

class Course {

	function Course ($c)
	{
		foreach ($c as $k=>$v)
			$this->$k = $c[$k];

	}

	public function getTitle(){
		//print_r($this);//$this["course-number"];
		$vars = get_object_vars($this);
		return $vars["subject"].$vars["course-number"];
	}

	public function getHours(){
		$vars = get_object_vars($this);
		return $vars["credit-hours"];	
	}

	public function getDeptName(){
		$vars = get_object_vars($this);
		return $vars["department"];	
	}
}

function readCourses($filename)
{
	// read the XML database of courses
	$courses = implode("", file($filename));
	$parser = xml_parser_create();
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, $courses, $values, $tags);
	xml_parser_free($parser);

	// loop through the structures
	foreach ($tags as $key=>$val) {
		if ($key == "course") {
			$ranges = $val;
			// each contiguous pair of array entries are the lower and upper
			// range of each course definition
			for ($i=0; $i < count($ranges); $i+=2) {
				$offset = $ranges[$i] + 1;
				$len = $ranges[$i + 1] - $offset;
				$tdb[] = parseCourse(array_slice($values, $offset, $len));
			}
		} else {
				continue;
		}
		return $tdb;
	}

}

function parseCourse($cvalues)
{
	for($i=0; $i < count($cvalues); $i++) {
		if(isset($cvalues[$i]["value"])){
			$course[$cvalues[$i]["tag"]] = $cvalues[$i]["value"];
		}
	}
	return new Course($course);
}

function getDept($dept)
{
	$fall = readCourses("http://courses.rice.edu/admweb/!SWKSECX.main?term=201110&title=&course=&crn=&coll=&dept=&subj=$dept");
	$spring = readCourses("http://courses.rice.edu/admweb/!SWKSECX.main?term=201220&title=&course=&crn=&coll=&dept=&subj=$dept");
	if (!is_array($fall)) {
		$fall = array();
	}
	if (!is_array($spring)) {
		$spring = array();
	}
	return array_merge($fall, $spring);

}

// function removeDups($array)
// {
	
// }

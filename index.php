<?php
	include_once("parsecourse.php");

	$semesters = array("Freshman Fall", 
		 			   "Freshman Spring",
		 			   "Sophomore Fall",
		 			   "Sophomore Spring",
		 			   "Junior Fall",
		 			   "Junior Spring",
		 			   "Senior Fall",
		 			   "Senior Spring");
	$semester = array("Both", "Fall", "Spring");

	# Test data - probably will be formatted differently
	$majorcourses = array(array("title"=>"COMP140", "term"=>1, "hours" =>4),
						  array("title"=>"COMP160", "term"=>1, "hours" =>4),
						  array("title"=>"COMP182", "term"=>2, "hours" =>4),
						  array("title"=>"COMP215", "term"=>1, "hours" =>4),
						  array("title"=>"COMP310", "term"=>1, "hours" =>4),
						  array("title"=>"STAT310", "term"=>2, "hours" =>3),
						  array("title"=>"MATH211", "term"=>0, "hours" =>3),
						  array("title"=>"ELEC220", "term"=>2, "hours" =>4),
						  array("title"=>"MATH101", "term"=>0, "hours" =>3),
						  array("title"=>"MATH102", "term"=>0, "hours" =>3));

	$courseareas = array("AFSC","ARCR","ANTH","ARAB","ARCH","ASIA","ASTR","BIOC","BIOE","BUSI","CHBE","CHEM","CHIN","CEVE","CLAS","CSCI","COLL","COMM","CAAM","COMP","ESCI",	
					"EBIO","ECON","EDUC","ELEC","ENGI","ENGL","ENST","FILM","FREN","FSEM","GERM","GLHT","GREE",	"HEAL","HEBR","HIND","HIST","HART","HONS","HUMA","HURC",
					"ITAL","JAPA","KECK","KINE","KORE","LATI","LEAD","MLSC","LPCR",	"LPAP","LING","MGMP","MGMW","MGMT","MANA","MSCI","MATH","MECH","MDST","MILI","MUSI",
					"NSCI","NAVA","NEUR","PHIL","FOTO",	"PHYS","POST","POLI","PORT","PSYC","RELI","RUSS","SOSC","SOCI","SPAN","SMGT","STAT","THEA","TIBT","UNIV","ARTS","SWGS");

	?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Rice Degree Planner</title>
	<link rel='stylesheet' type='text/css' href='main.css'>
	<script type="text/javascript" src="utility.js"></script>
	<script type='text/javascript'>
		var dragged = null; 							/* The currently being dragged object */
		var hours = null;								/* The currently being dragged hours */
		var hoursCount = new Array(0,0,0,0,0,0,0,0,0);	/* An array of hour counts */
		var locs = {};									/* Dictionary mapping course to location */
		var begloc = null;
		var targ = null;	

		/* Changes string from #DEPTNUM to DEPT NUM */
		 function doctorStr(sick) {
		 	 return sick.substr(1, 4) + " " + co.substr(5);
		 }
		/* Given a string of a column (ex. "S1"), parse and update the correct hours count .
		 * Increments upon insertion into a column */
		function incHours(columnID){
			var whichColumn = columnID[columnID.length-1];
			hoursCount[parseInt(whichColumn)] += parseInt(hours);
			document.getElementById("s"+whichColumn+"-2").innerHTML = "<div class='in'>"+hoursCount[parseInt(whichColumn)]+" hours</div>";
			if(hoursCount[parseInt(whichColumn)] > 20){
				document.getElementById("s"+whichColumn+"-2").style.color = "red";
				document.getElementById("s"+whichColumn+"-2").style.background = "pink";
			}
		}
		/* Given a string of a column (ex. "S1"), parse and update the correct hours count .
		 * Decrements upon removal from a column */
		function decHours(columnID){
			var whichColumn = columnID[columnID.length-1];
			hoursCount[parseInt(whichColumn)] -= parseInt(hours);
			document.getElementById("s"+whichColumn+"-2").innerHTML = "<div class='in'>"+hoursCount[parseInt(whichColumn)]+" hours</div>";
			if(hoursCount[parseInt(whichColumn)] <= 20){
				document.getElementById("s"+whichColumn+"-2").style.color = "#fff";
				document.getElementById("s"+whichColumn+"-2").style.background = "#aaa";
			}
		}
		/* Returns a string detailing what classes are currently on the grid
		 * It will be of the form "DEPT NUM DEPT NUM DEPT NUM", as this is how my utility function needs it
		 * This is still open for discussion though TODO */
		 function currentCourses() {
		 	 var out = "";
		 	 for (co in locs) {
		 	 	 if (locs[co].length < 4) { // A hack, I know, but it fits
		 	 	 	 // Doctor our string
		 	 	 	 out += doctorStr(co) + " ";
		 	 	 }
		 	 }
		 	 out = (out.length == 0 ? "" : out.substring(0, out.length-1)); // Remove extra space at end
		 	 return out;
		 }

		 // changes the visable div of classes to me
		 var last = "starttext";
		 function changeDepartment(){
		 	drop = document.getElementById("p_subj");
		 	me = drop.options[drop.selectedIndex].value;
		 	document.getElementById(me).style.display = "block";
		 	document.getElementById(me).className = "major";
		 	document.getElementById(last).style.display = "none";
		 	last = me;
		 }

		 function destroyLoader(){
		 	document.getElementById("loading").style.display = "none";
		 }
	</script>
<body>
	<div class='headers'>
		<h1><img src='icon.png' style='vertical-align:middle; margin:12px;'>Rice Degree Planner</h1>
		<?php include_once("hugeAssChart.php"); ?>
	</div>
	<div id='loading' class='loading'>
		Loading. Please be patient; there are a lot of classes at Rice.
	</div>
    <div id="board">

    	<?php
    	/* Print out the semester containers with titles */
    	for($i=0; $i<8; $i++){
    		$print = $i+1;
    		echo "<div id='s$print'><div class='title'>".$semesters[$i]."</div></div>\n";
    	}

    	//echo "<div class='graduation'><div>Total hours = 0</div></div>";
    	echo "<br style='clear:both'>";

    	/* Print out the hour total divs */
    	for($i=0; $i<8; $i++){
    		$print = $i+1;
    		echo "<div id='s$print-2'><div class='in'>0 hours</div></div>\n";
    	}

    	?>
    </div>

    	<?php

    	echo "<h2 style='color:#fff' id='starttext'>Pick a department to start adding classes</h2>";

    	/* Print out the courses in a specified major area - also accumulate information for JS 
    	 * (needs to be fixed for dynamic major switching) */
    	$listofdivs = "";
    	$listoflocs = "";
    	foreach($courseareas as &$area){
	    	$first = true;
	    	$dept = getDept($area);
	    	$listoflocs .= "#".$area.",";

	    	$seen = array();

	    	foreach($dept as &$course){

	    		$title = $course->getTitle();
	    		$hours = $course->getHours();

	    		if(!in_array($title, $seen)){

		    		if($first){
		    			echo "\n<div id='$area' class='major' style='display:none;'><h2> ".$course->getDeptName()." ($area) Classes </h2>";
		    			$first = false;
		    		}

		    		$whichLevel = $title[4];
		    		$intLevel = (int)$whichLevel;
		    		$addClass = "";
		    		switch($intLevel){
		    			case 1:
		    				$addClass = "fresh";
		    				break;
		    			case 2:
		    				$addClass = "soph";
		    				break;
		    			case 3:
		    				$addClass = "junior";
		    				break;
		    			case 4:
		    				$addClass = "senior";
		    				break;
		    			default:
		    				$addClass = "grad";
		    				break;
		    		}

		    		echo('<div id="'.$title.'" draggable="true" name="'.$hours.'" onLoad="Javascript:makeDropable('.$title.');" class="'.$addClass.'" ><div class="cardTitle">'.$title.' ('.$hours.')</div></div>');
		    		$listofdivs .= "#".$title.",";

		    		echo "<script>locs['#$title'] = '#$dept';</script>";

		    		$seen[] = $title;
		    	}
	    	}

	    	echo "<br style='clear:both;padding-bottom:100px;'></div>";
	    }

    	$listofdivs .= "#dummy"; // Dummy element (could be fixed by removing or preventing last comma too)	
    	$listoflocs .= "#dummy"; 

    	?>
    

    <script type="text/javascript" src="jquery-1.7.1.js"></script>

    <script type="text/javascript">
		var sections = "#s1,#s2,#s3,#s4,#s5,#s6,#s7,#s8,#major,<?php echo $listoflocs; ?>";
		$('document').ready(init);


		function init(){
           var cs = "<?php echo $listofdivs; ?>".split(",");
           for (c in cs) {
                   $(cs[c]).bind('dragstart', {nd : cs[c]}, function(event) {
                           event.originalEvent.dataTransfer.setData("text/plain", event.target.getAttribute('id'));
                           event.originalEvent.dataTransfer.effectAllowed = 'copy';


                           dragged = event.target.getAttribute('id');
                           hours = event.target.getAttribute('name');
                           begloc = locs[event.data.nd];

                   });
                   $(cs[c]).bind('dragenter', {nd : cs[c]}, function(event) {
                           targ = event.data.nd;
                   });
                   $(cs[c]).bind('dragover', {nd : cs[c]}, function(event) {
                           targ = event.data.nd;
                           event.originalEvent.dataTransfer.dropEffect = "none";
                   });
                   $(cs[c]).bind('dragleave', {nd : cs[c]}, function(event) {
                           targ = null;
                   });
                   $(cs[c]).bind('dragend', {nd : cs[c]}, function(event) {
                                   if (targ != null && targ.length < 7) {
                                           decHours(begloc);
                                   }
                                   targ = null;

                   });
                   $(cs[c]).bind('drop', {nd : cs[c]}, function(event) {
                           event.preventDefault();
                   });
           }

           // bind the dragover event on the board sections
           var secs = sections.split(",");
           for (sec in secs) {
                   $(secs[sec]).bind('dragenter', {nd : secs[sec]}, function(event) {
                           event.preventDefault();
                           targ = event.data.nd;
                   })
                   $(secs[sec]).bind('dragover', function(event) {
                             event.preventDefault();
                   });
                   $(secs[sec]).bind('dragleave', {nd : secs[sec]}, function(event) {
                           targ = null;
                            event.preventDefault();
                   });

                   // bind the drop event on the board sections
                   $(secs[sec]).bind('drop', function(event) {
                                   var notecard = event.originalEvent.dataTransfer.getData("text/plain");
                                   event.target.appendChild(document.getElementById(notecard));

                                   // Turn off the default behaviour
                                   // without this, FF will try and go to a URL with your id's name
                                   targ = event.target.getAttribute('id');
                                   event.preventDefault();
                                   locs["#"+notecard] = event.target.getAttribute('id');
                                   incHours(event.target.getAttribute('id'));

                                   /***********************************
                                   * TODO
                                   * Verification code here
                                   * call getReqTree(<pre-reqs>).fulfill(currentCourses());
                                   ***********************************/

                   });
           }
   }
   destroyLoader();
	</script>
</body>
</html>
<?php
//echo "<!-- Begin session.php at ".date("H:i:s", microtime(true))." -->\r\n";
$loadstart = date("g:i:s A", microtime(true));
$loadmicrostart = explode(' ', microtime());
$loadmicrostart = $loadmicrostart[1] + $loadmicrostart[0];
ini_set('memory_limit', '-1');
require_once("./db.php");
require_once("./auth_user.php");
require_once("./del_session.php");
//require_once("./merge_sessions.php");
require_once("./get_sessions.php");
require_once("./get_columns.php");
require_once("./plot.php");

$_SESSION['recent_session_id'] = strval(max($sids));
// Check if there is time set in the session; if not, set it
if ( isset($_SESSION['time'] ) ) {
        $timezone = $_SESSION['time'];
} else {
  date_default_timezone_set(date_default_timezone_get());
  $timezone = "GMT ".date('Z')/3600;
}

// Capture the session ID if one has been chosen already
if (isset($_GET["id"])) {
  $session_id = preg_replace('/\D/', '', $_GET['id']);
}

// Call exit function
if (isset($_GET['logout'])) {
    logout_user();
}

// Define and capture variables for maintaining the year and month filters between sessions.
$filteryearmonth = "";
if (isset($_GET["yearmonth"])) {
  $filteryearmonth = $_GET['yearmonth'];
}

// Define some variables to be used in variable management later, specifically when choosing default vars to plot
$i=1;
$var1 = "";
while ( isset($_POST["s$i"]) || isset($_GET["s$i"]) ) {
  ${'var' . $i} = "";
  if (isset($_POST["s$i"])) {
    ${'var' . $i} = $_POST["s$i"];
  }
  elseif (isset($_GET["s$i"])) {
    ${'var' . $i} = $_GET["s$i"];
  }
  $i = $i + 1;
}

// From the output of the get_sessions.php file, populate the page with info from
//  the current session. Using successful existence of a session as a trigger,
//  populate some other variables as well.
if (isset($sids[0])) {
  if (!isset($session_id)) {
    $session_id = $sids[0];
  }
  //For the merge function, we need to find out, what would be the next session
  $idx = array_search( $session_id, $sids);
  $session_id_next = "";
  if($idx>0) {
    $session_id_next = $sids[$idx-1];
  }
  $tableYear = date( "Y", $session_id/1000 );
  $tableMonth = date( "m", $session_id/1000 );
  $db_table_full = "{$db_table}_{$tableYear}_{$tableMonth}";
  // Get GPS data for the currently selectedsession
  $sessionqry = mysqli_query($con, "SELECT kff1006, kff1005 FROM $db_table_full
              WHERE session=$session_id
              ORDER BY time DESC") or die(mysqli_error($con));
  $geolocs = array();
  while($geo = mysqli_fetch_array($sessionqry)) {
    if (($geo["0"] != 0) && ($geo["1"] != 0)) {
      $geolocs[] = array("lat" => $geo["0"], "lon" => $geo["1"]);
    }
  }

  // Create array of Latitude/Longitude strings in Google Maps JavaScript format
  $mapdata = array();
  foreach($geolocs as $d) {
    $mapdata[] = "ol.proj.fromLonLat([".$d['lon'].", ".$d['lat']."])";
  }
  $imapdata = implode(",\n          ", $mapdata);

  // Don't need to set zoom manually
  $setZoomManually = 0;

  // Query the list of years and months where sessions have been logged, to be used later
  $yearmonthquery = mysqli_query($con, "SELECT DISTINCT CONCAT(YEAR(FROM_UNIXTIME(session/1000)), '_', DATE_FORMAT(FROM_UNIXTIME(session/1000),'%m')) as Suffix, 
		CONCAT(MONTHNAME(FROM_UNIXTIME(session/1000)), ' ', YEAR(FROM_UNIXTIME(session/1000))) as Description 
		FROM $db_sessions_table ORDER BY Suffix DESC") or die(mysqli_error($con));
  $yearmonthsuffixarray = array();
  $yearmonthdescarray = array();
  $i = 0;
  while($row = mysqli_fetch_assoc($yearmonthquery)) {
    $yearmonthsuffixarray[$i] = $row['Suffix'];
    $yearmonthdescarray[$i] = $row['Description'];
    $i = $i + 1;
  }

  // Query the list of profiles where sessions have been logged, to be used later
  $profilequery = mysqli_query($con, "SELECT distinct profileName FROM $db_sessions_table ORDER BY profileName asc") or die(mysqli_error($con));
  $profilearray = array();
  $i = 0;
  while($row = mysqli_fetch_assoc($profilequery)) {
    $profilearray[$i] = $row['profileName'];
    $i = $i + 1;
  }

  //Close the MySQL connection, which is why we can't query years later
  mysqli_free_result($sessionqry);
  mysqli_close($con);
} else {
  //Default map in case there's no sessions to query.  Very unlikely this will get used.
  $imapdata = "new google.maps.LatLng(37.235, -115.8111)";
  $setZoomManually = 1;
}

//** graph variables for export
$i=1;
while ( isset(${'var'.$i}) && !empty(${'var'.$i}) ) {
	$plotvariables[]=${'var'.$i};
	$i+=1;
}
if ($plotvariables) $plotvariables=implode(",", $plotvariables);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EV Charge Cost - Open Torque</title>
    <meta name="description" content="Open Torque Viewer">
    <meta name="author" content="Matt Nicklay">
    <meta name="author" content="Joe Gullo (surfrock66)">
	<meta name="author" content="Ingo Nehls">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.0/chosen.min.css">
    <link rel="stylesheet" href="static/css/torque.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.peity.min.js"></script>
    <script language="javascript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.1.0/chosen.jquery.min.js"></script>
    <!-- Include OpenStreetMap Openlayers -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.4.3/css/ol.css" type="text/css">
    <script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.4.3/build/ol.js"></script>

    <script language="javascript" type="text/javascript">
	<!-- Array of Drive Path Coordinates -->
    var coordinates = [
		<?php echo $imapdata; ?>
    ];
    </script>
<?php if ($setZoomManually === 0) { ?>
    <!-- Flot Local Javascript files -->
    <script language="javascript" type="text/javascript" src="static/js/jquery.flot.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.flot.axislabels.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.flot.hiddengraphs.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.flot.multihighlight-delta.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.flot.selection.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.flot.time.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.flot.tooltip.min.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.flot.updater.js"></script>
    <script language="javascript" type="text/javascript" src="static/js/jquery.flot.resize.min.js"></script>
    <!-- Configure Jquery Flot graph and plot code -->
    <script language="javascript" type="text/javascript">
      $(document).ready(function(){
<?php   $i=1; ?>
<?php   while ( isset(${'var' . $i }) && !empty(${'var' . $i }) ) { ?>
        var <?php echo "s$i"; ?> = [<?php foreach(${"d".$i} as $b) {echo "[".$b[0].", ".$b[1]."],";} ?>];
<?php     $i = $i + 1; ?>
<?php   } ?>

        var flotData = [
<?php   $i=1; ?>
<?php   while ( isset(${'var' . $i }) && !empty(${'var' . $i }) ) { ?>
            { data: <?php echo "s$i"; ?>, label: <?php echo "${'v'.$i.'_label'}"; ?> }<?php if ( isset(${'var'.($i+1)}) ) echo ","; ?>
<?php     $i = $i + 1; ?>
<?php   } ?>
        ];
        function doPlot(position) {
          $.plot("#placeholder", flotData, {
            xaxes: [ {
              mode: "time",
              timezone: "browser",
              axisLabel: "Time",
              timeformat: "%H:%M%",
              twelveHourClock: false
            } ],
            yaxes: [ { axisLabel: "" }, {
              alignTicksWithAxis: position == "right" ? 1 : null,
              position: position,
              axisLabel: ""
            } ],
            legend: {
              position: "nw",
              hideable: true,
              backgroundOpacity: 0.1,
              margin: 0
            },
            selection: { mode: "x" },
            grid: {
              hoverable: true,
              clickable: false
            },
            multihighlightdelta: { mode: 'x' },
            tooltip: false,
            tooltipOpts: {
              //content: "%s at %x: %y",
              content: "%x",
              xDateFormat: "%m/%d/%Y %I:%M:%S%p",
              twelveHourClock: false,
              onHover: function(flotItem, $tooltipEl) {
                console.log(flotItem, $tooltipEl);
              }
            }
          }
        )}
<?php   if ( $var1 <> "" ) { ?>
        doPlot("right");
<?php   } ?>
        $("button").click(function () {
          doPlot($(this).text());
        });
      });
    </script>
    <script language="javascript" type="text/javascript" src="static/js/torquehelpers.js"></script>
<?php } else { ?>
    <script language="javascript" type="text/javascript" src="static/js/torquehelpers.js"></script>
<?php } ?>
  </head>
  <body>
	<div class="container-fluid">
	  <header class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom">
		<a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-dark text-decoration-none">
		  <span class="fs-4">EV-Charge-Cost Torque Viewer</span>
		</a>

		<ul class="nav nav-pills">
		  <?php if (!empty($homelink)) { ?><li class="nav-item"><a href="<?php echo $homelink; ?>" class="nav-link active">Home</a></li><?php } ?>
		  <li class="nav-item"><a href="session.php" class="nav-link">Torque</a></li>
		  <?php    if ( $_SESSION['torque_user'] ) { ?>
			<li class="nav-item"><a href="signup.php" class="nav-link"><?php echo $_SESSION['torque_user'] ?></a></li>
			<li class="nav-item"><a href="latest.php" class="nav-link">Latest</a></li>
			<li class="nav-item"><a href="session.php?logout=true" class="nav-link">Logout</a></li>
		  <?php    } ?>
		</ul>
	  </header>
	</div>
	<div class="container-fluid h-100">
		<div class="row h-100">
			<!-- left column with map -->
			<div class="col-sm-7 col-xxl-9 mb-3" id="map-container">
				<div id="map-canvas"></div>
				<script type="text/javascript">
				<!-- Initialize drive path layer -->
				var layerLines = new ol.layer.Vector({
					source: new ol.source.Vector({
						features: [new ol.Feature({
							geometry: new ol.geom.LineString(coordinates),
							name: 'Line'
						})]
					}),
					style: new ol.style.Style({
						stroke: new ol.style.Stroke({
							color: '#ff0000',
							width: 3
						})
					})
				});

				<!-- Initialize map -->
				var map = new ol.Map({
					target: 'map-canvas',
					layers: [
						new ol.layer.Tile({
							source: new ol.source.OSM()
						})
					],
					view: view
				});

				<!-- Initialize marker to start of log -->
				var markericon = new ol.layer.Vector({
					source: new ol.source.Vector({
						features: [
							new ol.Feature({
								geometry: new ol.geom.Point(coordinates[coordinates.length-1])
							})
						]
					})
				});
				map.addLayer(markericon);

				<!-- Add drive path layer -->
				map.addLayer(layerLines);
				
				<!-- Center view on drive path -->
				var view = new ol.View({});
				var extent = layerLines.getSource().getExtent();
				map.getView().fit(extent, {padding: [50, 50, 50, 50]});
				</script>
			</div>
			
			<!-- right column with controls -->
			<div id="right-container" class="col-sm-5 col-xxl-3">
			
			
			
				<div id="right-cell">
					<h5>Select Session</h5>
					<div class="row center-block" style="padding-bottom:4px;">
					  <!-- Filter the session list by year and month -->
					  Filter Sessions (Default date filter is current year/month)
					  <form method="post" class="form-horizontal" role="form" action="url.php?id=<?php echo $session_id; ?>">
						<table width="100%">
						  <tr>
							<!-- Profile Filter -->
							<td width="34%">
							  <select id="selprofile" name="selprofile" class="form-control chosen-select btn-sm" data-placeholder="Select Profile">
								<option value=""></option>
								<option value="ALL"<?php if ($filterprofile == "ALL") echo ' selected'; ?>>Any Profile</option>
			<?php $i = 0; ?>
			<?php while(isset($profilearray[$i])) { ?>
								<option value="<?php echo $profilearray[$i]; ?>"<?php if ($filterprofile == $profilearray[$i]) echo ' selected'; ?>><?php echo $profilearray[$i]; ?></option>
			<?php   $i = $i + 1; ?>
			<?php } ?>
							  </select>
							</td>
							<td width="2%"></td>

							<!-- Year Month Filter -->
							<td width="34%">
							  <select id="selyearmonth" name="selyearmonth" class="form-control chosen-select btn-sm" data-placeholder="Select Year/Month">
								<option value=""></option>
			<?php $i = 0; ?>
			<?php while(isset($yearmonthsuffixarray[$i])) { ?>
								<option value="<?php echo $yearmonthsuffixarray[$i]; ?>"<?php if ($filteryearmonth == $yearmonthsuffixarray[$i]) { echo ' selected'; } else if ($i == 0) { echo 'selected'; } ?>><?php echo $yearmonthdescarray[$i]; ?></option>
			<?php   $i = $i + 1; ?>
			<?php } ?>
							  </select>
							</td>

							<td width="13%">
							  <div align="center" style="padding-top:2px;"><input class="btn btn-primary btn-sm" type="submit" id="formfilterdates" name="filterdates" value="Filter Sessions"></div>
							</td>
						  </tr>
						</table>
						<noscript><input type="submit" id="datefilter" name="datefilter" class="input-sm"></noscript>
					  </form><br />
					  <!-- Session Select Drop-Down List -->
					  <form method="post" class="form-horizontal" role="form" action="url.php">
						<select id="seshidtag" name="seshidtag" class="form-control chosen-select" onchange="this.form.submit()" data-placeholder="Select Session..." style="width:100%;">
						  <option value=""></option>
			<?php foreach ($seshdates as $dateid => $datestr) { ?>
						  <option value="<?php echo $dateid; ?>"<?php if ($dateid == $session_id) echo ' selected'; ?>><?php echo $datestr; echo $seshprofile[$dateid]; if ($show_session_length) {echo $seshsizes[$dateid];} ?><?php if ($dateid == $session_id) echo ' (Current Session)'; ?></option>
			<?php } ?>
						</select>
			<?php   if ( $filteryearmonth <> "" ) { ?>
						<input type="hidden" name="selyearmonth" id="selyearmonth" value="<?php echo $filteryearmonth; ?>" />
			<?php   } ?>
						<noscript><input type="submit" id="seshidtag" name="seshidtag" class="input-sm"></noscript>
					  </form>
			<?php if(isset($session_id) && !empty($session_id)){ ?>
					  <div class="btn-group btn-group-justified">
						<table style="width:100%">
						  <tr>
							<td>
							  <form method="post" class="form-horizontal" role="form" action="merge_sessions.php?mergesession=<?php echo $session_id; ?>" id="formmerge">
								<div align="center" style="padding-top:6px;"><input class="btn btn-primary btn-sm" type="submit" id="formmerge" name="merge" value="Merge..." title="Merge this session (<?php echo $seshdates[$session_id]; ?>) with the other sessions." /></div>
							  </form>
							</td>
							<td>
							  <form method="post" class="form-horizontal" role="form" action="session.php?deletesession=<?php echo $session_id; ?>" id="formdelete">
								<div align="center" style="padding-top:6px;"><input class="btn btn-primary btn-sm" type="submit" id="formdelete" name="delete" value="Delete" title="Delete this session or selected range (<?php echo $seshdates[$session_id]; ?>)." /></div>
							  </form>
							</td>
							<script type="text/javascript">
							  $('#formdelete').submit(function() {
								var c = confirm("Click OK to delete session or selected range (<?php echo $seshdates[$session_id]; ?>).");
								return c; //you can just return c because it will be true or false
							  });
							</script>
						  </tr>
						</table>
					  </div>
			<?php } ?>
					</div><br />

			<!-- Variable Select Block -->
			<?php if ($setZoomManually === 0) { ?>
					<h5>Select Variables to Compare</h5>
					<div class="row center-block" style="padding-top:3px;">
						<form method="post" role="form" action="url.php?makechart=y&seshid=<?php echo $session_id; ?>" id="formplotdata">
						  <select data-placeholder="Choose OBD2 data..." multiple class="chosen-select" size="<?php echo $numcols; ?>" style="width:100%;" id="plot_data" onsubmit="onSubmitIt" name="plotdata[]">
							<option value=""></option>
			<?php   foreach ($coldata as $xcol) { ?>
							<option value="<?php echo $xcol['colname']; ?>" <?php $i = 1; while ( isset(${'var' . $i}) ) { if ( (${'var' . $i} == $xcol['colname'] ) OR ( $xcol['colfavorite'] == 1 ) ) { echo " selected"; } $i = $i + 1; } ?>><?php echo $xcol['colcomment']; ?></option>
			<?php   } ?>
						</select>
			<?php   if ( $filteryearmonth <> "" ) { ?>
						<input type="hidden" name="selyearmonth" id="selyearmonth" value="<?php echo $filteryearmonth; ?>" />
			<?php   } ?>
						<div align="center" style="padding-top:6px;"><input class="btn btn-primary btn-sm" type="submit" id="formplotdata" name="plotdata[]" value="Plot!"></div>
					  </form>
					</div>
			<?php } else { ?>

			<!-- Plot Block -->
					<h5>Plot</h5>
					<div align="center" class="badge bg-warning" style="">
						Select a session first!
					  </div><br />
			<?php } ?>

			<!-- Chart Block -->
					<h5>Chart</h5>
					<div class="row center-block" style="padding-bottom:5px;">
			<?php if ($setZoomManually === 0) { ?>
					  <!-- 2015.07.22 - edit by surfrock66 - Don't display anything if no variables are set (default) -->
			<?php   if ( $var1 == "" ) { ?>
					  <div align="center" class="badge bg-warning" style="">
						No Variables Selected to Plot!
					  </div>
			<?php   } else { ?>
					  <div class="demo-container">
						<div id="placeholder" class="demo-placeholder" style="height:300px;"></div>
					  </div>
			<?php   } ?>
			<?php } else { ?>
					  <div align="center" class="badge bg-warning" style="">
						Select a session first!
					  </div>
			<?php } ?>
					</div><br />

			<!-- Data Summary Block -->
					<h5>Data Summary</h5>
					<div class="row center-block">
			<?php if ($setZoomManually === 0) { ?>
					  <!-- 2015.07.22 - edit by surfrock66 - Don't display anything if no variables are set (default) -->
			<?php   if ( $var1 <> "" ) { ?>
					  <div class="table-responsive">
						<table class="table">
						  <thead>
							<tr>
							  <th>Name</th>
							  <th>Min/Max</th>
							  <th>25th Pcnt</th>
							  <th>75th Pcnt</th>
							  <th>Mean</th>
							  <th>Sparkline</th>
							</tr>
						  </thead>
						  <!-- 2015.08.05 - Edit by surfrock66 - Code to plot unlimited variables -->
						  <tbody>
			<?php     $i=1; ?>
			<?php     while ( isset(${'var' . $i }) ) { ?>
							<tr>
							  <th><?php echo substr(${'v' . $i . '_label'}, 1, -1); ?></td>
							  <td><?php echo ${'min' . $i}.'/'.${'max' . $i}; ?></td>
							  <td><?php echo ${'pcnt25data' . $i}; ?></td>
							  <td><?php echo ${'pcnt75data' . $i}; ?></td>
							  <td><?php echo ${'avg' . $i}; ?></td>
							  <td><span class="line"><?php echo ${'sparkdata' . $i}; ?></span></td>
							</tr>
			<?php       $i = $i + 1; ?>
			<?php     } ?>
						  </tbody>
						</table>
					  </div>
			<?php   } else { ?>
					  <div align="center" class="badge bg-warning" style="">
						No Variables Selected to Plot!
					  </div>
			<?php   } ?>
			<?php } else { ?>
					  <div align="center" class="badge bg-warning" style="">
						Select a session first!
					  </div>
			<?php } ?>
					</div><br />

			<!-- Export Data Block -->
					<h5>Export Data</h5>
					<?php if ($plotvariables) { ?><small><input id="graphonly" type="checkbox" /> (Graph data only)</small><?php } ?>
					<div class="row center-block" style="padding-bottom:18px;">
			<?php if ($setZoomManually === 0) { ?>
					  <div id="export" class="btn-group">
						<a class="btn btn-secondary" role="button" href="<?php echo './export.php?sid='.$session_id.'&filetype=csv'; ?>" onClick="javascript:graphonly(this, <?php echo"'$plotvariables'"; ?>);">CSV</a>&nbsp;
						<a class="btn btn-secondary" role="button" href="<?php echo './export.php?sid='.$session_id.'&filetype=json'; ?>" onClick="javascript:graphonly(this, <?php echo"'$plotvariables'"; ?>);">JSON</a>
					  </div>
			<?php } else { ?>
					  <div align="center" class="badge bg-warning" style="">
						Select a session first!
					  </div>
			<?php } ?>
					</div>
					<div class="row center-block" style="padding-bottom:18px;text-align:center;">
					  <a href="./pid_edit.php" title="Edit PIDs">Edit PIDs</a><br />
					  <a href="https://github.com/Esprit1st/torque" target="_blank" title="View Source On Github">View Source On Github</a>
					  <p style="font-size:10px;margin-top:20px;" >
						Render Start: <?php echo $loadstart; ?>; Render End: <?php $loadend = date("h:i:s A", microtime(true)); echo $loadend; ?><br />
						Load Time: <?php $loadmicroend = explode(' ', microtime()); $loadmicroend = $loadmicroend[1] + $loadmicroend[0]; echo $loadmicroend-$loadmicrostart; ?> seconds<br />
						Session ID: <?php echo $session_id; ?>
					  </p>
					</div>
				</div>
			</div>

			
			
			
			
			
		</div>
	</div>
	<script language="javascript" type="text/javascript" src="static/js/torquehelpers2.js"></script>
  </body>
</html>
<?php //echo "<!-- End session.php at ".date("H:i:s", microtime(true))." -->\r\n"; ?>

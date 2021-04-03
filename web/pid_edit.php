<?php

require_once ("./db.php");
require_once ("./auth_user.php");

// Create array of column name/comments for chart data selector form
// 2015.08.21 - edit by surfrock66 - Rather than pull from the column comments,
//   oull from a new database created which manages variables. Include
//   a column flagging whether a variable is populated or not.
$keyqry = mysqli_query($con, "SELECT id,description,units,type,min,max,populated,favorite FROM ".$db_name.".".$db_keys_table." ORDER BY description") or die(mysqli_error($con));
$i = 0;
while ($x = mysqli_fetch_array($keyqry)) {
	if ((substr($x[0], 0, 1) == "k") ) {
		$keydata[$i] = array("id"=>$x[0], "description"=>$x[1], "units"=>$x[2], "type"=>$x[3], "min"=>$x[4], "max"=>$x[5], "populated"=>$x[6], "favorite"=>$x[7]);
		$i = $i + 1;
	}
}
mysqli_free_result($keyqry);
mysqli_close($con);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EV Charge Cost - Open Torque</title>
    <meta name="description" content="Open Torque Viewer">
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
    <script language="javascript" type="text/javascript" src="static/js/torquehelpers.js"></script>
    <script>
      $(function(){
        var message_status = $("#status");

        $("td[contenteditable=true]").blur(function(){
          var field_pid = $(this).attr("id");
          var value = $(this).text();
          $.post('pid_commit.php' , field_pid + "=" + value, function(data) {
            if(data != '') {
              message_status.show();
              message_status.text(data);
              setTimeout(function(){message_status.hide()},3000);
            }
          });
        });
	
        $("input:checkbox").click(function(){
          var field_pid = $(this).attr("id");
          var value = $(this).is(":checked");
          $.post('pid_commit.php' , field_pid + "=" + value, function(data) {
            if(data != '') {
              message_status.show();
              message_status.text(data);
              setTimeout(function(){message_status.hide()},3000);
            }
          });
        });
	
        $("select").change(function(){
          var field_pid = $(this).attr("id");
          var value = $(this).val();
          $.post('pid_commit.php' , field_pid + "=" + value, function(data) {
            if(data != '') {
              message_status.show();
              message_status.text(data);
              setTimeout(function(){message_status.hide()},3000);
            }
          });
        });
      });
    </script>
  </head>
  <body>
	<div class="container-xxl">
	  <header class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom">
		<a href="session.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-dark text-decoration-none">
		  <span class="fs-4">EV-Charge-Cost Torque Viewer</span>
		</a>

		<ul class="nav nav-pills">
		  <li class="nav-item"><a href="session.php" class="nav-link active">Home</a></li>
		  <?php    if ( $_SESSION['torque_user'] ) { ?>
			<li class="nav-item"><a href="signup.php" class="nav-link"><?php echo $_SESSION['torque_user'] ?></a></li>
			<li class="nav-item"><a href="session.php?logout=true" class="nav-link">Logout</a></li>
		  <?php    } ?>
		</ul>
	  </header>
	</div>
    <table class="table small" style="width:98%;margin:0px auto;margin-top:50px;">
      <thead>
        <th>ID</th>
        <th>Description</th>
        <th>Units</th>
        <th>Variable Type</th>
        <th>Min Value</th>
        <th>Max Value</th>
        <th>Visible?</th>
        <th>Favorite?</th>
      </thead>
      <tbody>
<?php $i = 1; ?>
<?php foreach ($keydata as $keycol) { ?>
        <tr<?php if ($i & 1) echo " class=\"odd\"";?>>
          <td id="id:<?php echo $keycol['id']; ?>"><?php echo $keycol['id']; ?></td>
          <td id="description:<?php echo $keycol['id']; ?>" contenteditable="true"><?php echo $keycol['description']; ?></td>
          <td id="units:<?php echo $keycol['id']; ?>" contenteditable="true"><?php echo $keycol['units']; ?></td>
          <td>
            <select id="type:<?php echo $keycol['id']; ?>">
              <!--<option value="boolean"<?php //if ($keycol['type'] == "boolean") echo ' selected'; ?>>boolean</option>-->
              <option value="double"<?php if ($keycol['type'] == "double") echo ' selected'; ?>>double</option>
              <option value="float"<?php if ($keycol['type'] == "float") echo ' selected'; ?>>float</option>
              <option value="varchar(255)"<?php if ($keycol['type'] == "varchar(255)") echo ' selected'; ?>>varchar(255)</option>
            </select>
          </td>
          <td id="min:<?php echo $keycol['id']; ?>" contenteditable="true"><?php echo $keycol['min']; ?></td>
          <td id="max:<?php echo $keycol['id']; ?>" contenteditable="true"><?php echo $keycol['max']; ?></td>
          <td><input type="checkbox" id="populated:<?php echo $keycol['id']; ?>"<?php if ( $keycol['populated'] ) echo " CHECKED"; ?>/></td>
          <td><input type="checkbox" id="favorite:<?php echo $keycol['id']; ?>"<?php if ( $keycol['favorite'] ) echo " CHECKED"; ?>/></td>
        </tr>
<?php   $i = $i + 1; ?>
<?php } ?>
      </tbody>
    </table>
    <div id="status" style="position:fixed; top:50%; left:50%; margin-left:-100px; padding:10px; background:#88C4FF; color:#000; font-weight:bold; font-size:12px; text-align:center; display:none; width:200px;"></div>
  </body>
</html>


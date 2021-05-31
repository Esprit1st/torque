<?php

if ($_SESSION['torque_logged_in']) {
	//** load key ids and names
	$sql = mysqli_query($con, "SELECT id, description FROM `$db_keys_table` WHERE user=" . quote_value($_SESSION["torque_userid"]) . " AND populated=1 ORDER BY `description` ASC") or die(mysqli_error($con));
	while ($row = mysqli_fetch_array($sql)) {
		$keyiddesc[$row["id"]] = $row["description"];
	}
	//** load config
	$parts = explode("|", $_SESSION["torque_config"]."|A&&&");
	$i=0;
	foreach ($parts as $val) {
		//** if config is an Alert
		if ($val[0] == "A") {
			$alertconfig = explode("&", $val);
?>
					<h4 class="mt-5">Send Alert if</h4>
					<?php if (!empty($alertconfig[1])) { ?>
					<div class="form-check">
					  <input class="form-check-input" type="checkbox" value="" id="deletealert<?php echo $i ?>" onclick="emptyalert(<?php echo $i ?>)" />
					  <label class="form-check-label" for="flexCheckDefault">Delete this alert</label>
					</div>
					<?php } ?>
					<div class="mb-3 form-group"><select class="form-select" id="alertfield<?php echo $i ?>" name="alertfield<?php echo $i ?>">
						<option>Select field</option>
						<?php printoptions($keyiddesc, $alertconfig[1], ""); ?>
						</select>
					</div>
					<div class="mb-3 form-group"><select class="form-select" id="alertoperator<?php echo $i ?>" name="alertoperator<?php echo $i ?>">
						<option>Select compare operator</option>
						<option value="1"<?php if ($alertconfig[2]==1) echo " selected"; ?>>gets bigger than</option>
						<option value="2"<?php if ($alertconfig[2]==2) echo " selected"; ?>>gets smaller than</option>
						<option value="3"<?php if ($alertconfig[2]==3) echo " selected"; ?>>is farther than</option>
						<option value="4"<?php if ($alertconfig[2]==4) echo " selected"; ?>>is closer than</option>
						</select>
					</div>
					<div class="mb-3 form-group"><input class="form-control<?php if ($error["alertto".$i]) echo " is-invalid"; ?>" id="alertto<?php echo $i ?>" type="text" name="alertto<?php echo $i ?>" value="<?php echo $alertconfig[3]; ?>" placeholder="(number)" /></div>
					<div class="mb-3 form-group"><select class="form-select" <?php if ($alertconfig[2]<3) echo "style=\"display:none;\" "; ?>id="alertfieldto<?php echo $i ?>" name="alertfieldto<?php echo $i ?>">
						<option>Select field</option>
						<?php printoptions($keyiddesc, $alertconfig[4], "from/to "); ?>
						</select>
					</div>

<?php			
			$i++;
		}
	}
	
?>

<script language="javascript">
for (i=0; i<<?php echo $i ?>; i++) {
	$('#alertoperator' + i).on('change', function() {
		for (i=0; i<<?php echo $i ?>; i++) {
			if ($("#alertoperator" + i).val() < 3) {
				$("#alertfieldto" + i).hide();
			}
			else if ($("#alertoperator" + i).val() < 5) {
				$("#alertfieldto" + i).show();
			}
		}
	});
}

function emptyalert(i) {
	$("#alertfield" + i)[0].selectedIndex = 0;
	$("#alertoperator" + i)[0].selectedIndex = 0;
	$("#alertto" + i).val("");
	$("#alertfieldto" + i).hide();
}
</script>

<?php
}

function printoptions($array, $select, $add) {
	foreach ($array as $key => $val) {
		echo "<option value=\"$key\"";
		if ($key == $select) echo " selected";
		echo ">" . $add . $val . "</option>";
	}
}
?>
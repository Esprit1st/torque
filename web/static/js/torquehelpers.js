function onSubmitIt() {
    var fields = $("li.search-choice").serializeArray();
    if (fields.length <= 1)
    {
        return false;
    }
    else
    {
        $('#formplotdata').submit();
    }
}

$(document).ready(function(){
    var previousPoint = null;
    $("#placeholder").bind("plothover", function (event, pos, item) {
        if (item) placemarker(item.dataIndex);
        var a_p = "";
        var d = new Date(parseInt(pos.x.toFixed(0)));
        var curr_hour = d.getHours();
        var curr_min = d.getMinutes() + "";
        if (curr_min.length == 1) {
           curr_min = "0" + curr_min;
           }
        var curr_sec = d.getSeconds() + "";
        if (curr_sec.length == 1) {
            curr_sec = "0" + curr_sec;
        }
        var formattedTime = curr_hour + ":" + curr_min + ":" + curr_sec + " " + a_p;
        $(".x").text(formattedTime);
        $("#y1").text(pos.y1.toFixed(2));
        //$("#y2").text(pos.y2.toFixed(2));

        if ($("#enableTooltip:checked").length > 0) {
            if (item) {
                if (previousPoint != item.dataIndex) {
                    previousPoint = item.dataIndex;

                    $("#tooltip").remove();
                    var x = item.datapoint[0].toFixed(2),
                        y = item.datapoint[1].toFixed(2);

                    showTooltip(item.pageX, item.pageY,
                                item.series.label + " of " + x + " = " + y);
                }
            }
            else {
                $("#tooltip").remove();
                previousPoint = null;
            }
        }
    });
});

$(document).ready(function(){
  // Activate Chosen on the selection drop down
  $("select#seshidtag").chosen({width: "100%"});
  $("select#plot_data").chosen({width: "100%"});
  // Center the selected element
  $("div#seshidtag_chosen a.chosen-single span").attr('align', 'center');
  // Limit number of multi selects to 2
  $("select#plot_data").chosen({max_selected_options: 2, no_results_text: "Oops, nothing found!"});
  $("select#plot_data").chosen({placeholder_text_multiple: "Choose OBD2 data.."});
  // When the selection drop down is open, force all elements to align left with padding
  $('select#seshidtag').on('chosen:showing_dropdown', function() { $('li.active-result').attr('align', 'left');});
  $('select#seshidtag').on('chosen:showing_dropdown', function() { $('li.active-result').css('padding-left', '20px');});
  $('select#plot_data').on('chosen:showing_dropdown', function() { $('li.active-result').attr('align', 'left');});
  $('select#plot_data').on('chosen:showing_dropdown', function() { $('li.active-result').css('padding-left', '20px');});
});

$(document).on('click', '.panel-heading span.clickable', function(e){
    var $this = $(this);
  if(!$this.hasClass('panel-collapsed')) {
    $this.parents('.panel').find('.panel-body').slideUp();
    $this.addClass('panel-collapsed');
    $this.find('i').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
  } else {
    $this.parents('.panel').find('.panel-body').slideDown();
    $this.removeClass('panel-collapsed');
    $this.find('i').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
  }
});

$(document).ready(function(){
  $(".line").peity("line")
});

function placemarker(marker) {
	markericon.getSource().getFeatures()[0].getGeometry().setCoordinates(coordinates[marker]);
}

function graphonly(e, vars) {
	if ($('#graphonly').is(':checked')) {
		event.preventDefault();
		this.document.location.href = e + "&plotvars=" + vars;
		//alert( e + "&plotvars=" + vars );
	}
}

// form validation
var showErrorSuccess = function(element, status) {
  if (status === false) {
    element.parent().addClass('is-invalid').removeClass('is-valid');
	element.addClass('is-invalid').removeClass('is-valid');
    return false;
  }
  element.parent().removeClass('is-invalid').addClass('is-valid');
  element.removeClass('is-invalid').addClass('is-valid');
};

// form validation
var validate = function() {
  //validate name
  var name = $('#username').val(),
    nameReg = /^[a-zA-Z0-9_-]{4,15}$/
  if (!nameReg.test(name)) {
    return showErrorSuccess($('#username'), false);
  }
  showErrorSuccess($('#username'));

  //validate password
  var password = $('#password').val(),
    passwordReg = /^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,32}$/
  if (password.length>0) {
	  if (!passwordReg.test(password)) {
		return showErrorSuccess($('#password'), false);
	  }
  }
  showErrorSuccess($('#password'));

  //password & pass2 match
  var pass2 = $('#pass2').val();
  if (password != pass2) {
    return showErrorSuccess($('#pass2'), false);
  }
  showErrorSuccess($('#pass2'));

  //validate email
  var email = $('#email').val(),
    emailReg = /^[^@:; \t\r\n]+@[^@:; \t\r\n]+\.[^@:; \t\r\n]+$/
  if (!emailReg.test(email) || email == '') {
    return showErrorSuccess($('#email'), false);
  }
  showErrorSuccess($('#email'));
  
  //validate torque eml
  var teml = $('#torque_eml').val(),
    torqueemlReg = /^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/
  if (!torqueemlReg.test(teml)) {
    if (teml != '') return showErrorSuccess($('#torque_eml'), false);
  }
  showErrorSuccess($('#torque_eml'));
};

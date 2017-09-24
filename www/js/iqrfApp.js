$(".btn-packet").click(function () {
	$("#frm-iqrfAppSendRawForm-packet").val($(this).data("packet"));
});
$(".btn-port").click(function () {
	$("#frm-configIqrfForm-IqrfInterface").val($(this).data("port"));
});
$('#frm-iqrfAppSendRawForm-timeoutEnabled').click(function () {
	if ($(this).is(':checked')) {
		$("#frm-iqrfAppSendRawForm-timeout").prop('disabled', false);
	} else {
		$("#frm-iqrfAppSendRawForm-timeout").prop('disabled', true);
	}
});

$(".btn-packet").click(function () {
	$("#frm-iqrfAppSendRawForm-packet").val($(this).data("packet"));
});
$(".btn-port").click(function () {
	$("#frm-configIqrfForm-IqrfInterface").val($(this).data("port"));
});
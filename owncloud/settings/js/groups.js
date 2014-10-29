$(document).ready(function () {
	$('table').on('click', 'td.publicKey>a', function (event) {
		var key = $(this);
		var row = $(this).parent().parent();
		var gname = $(row).attr('data-uid');
		$.post(
			OC.filePath('settings', 'ajax', 'getpkey.php'),
			{
				groupname: gname
			},
			function (result) {
				if (result.status != 'success') {
					OC.Notification.show(t('admin', result.data.message));
				} else {
					$(key).replaceWith( "<textarea>"+result.data.publicKey+"</textarea>");
				}
			}
		);
	});
});

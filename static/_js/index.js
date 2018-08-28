$(document).ready(function () {
	$('table.projects tr').hover(function () {
		$(this).find('td.price input').show();
	}, function () {
		$(this).find('td.price input').hide();
	});
});
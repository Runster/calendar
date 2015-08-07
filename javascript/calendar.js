var currentMonth = 0;

function getMonth(counter) {
	if (isNaN(counter)) {
		counter = 0;
	}
	currentMonth = (currentMonth + counter);
	$.get('calendar/api/getMonth.php?month=' + currentMonth, function(data, status) {
		$('#treadar-calendar').html(data)
	});
}

$(document).ready(getMonth);

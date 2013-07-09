// <!-- <![CDATA[

// Set the initial date.
var ds_i_date = new Date();
ds_c_month = ds_i_date.getMonth() + 1;
ds_c_year = ds_i_date.getFullYear();

// Get Element By Id
function ds_getel(id) {
	return document.getElementById(id);
}

// Get the left and the top of the element.
function ds_getleft(el) {
	var tmp = el.offsetLeft;
	el = el.offsetParent
	while(el) {
		tmp += el.offsetLeft;
		el = el.offsetParent;
	}
	return tmp;
}
function ds_gettop(el) {
	var tmp = el.offsetTop;
	el = el.offsetParent
	while(el) {
		tmp += el.offsetTop;
		el = el.offsetParent;
	}
	return tmp;
}

setTimeout(
	function(){
		// Output Element
		ds_oe = ds_getel('ds_calclass');		
		// Container
		ds_ce = ds_getel('ds_conclass');
	}, 100
);

// Output Buffering
var ds_ob = ''; 
function ds_ob_clean() {
	ds_ob = '';
}
function ds_ob_flush() {
	ds_oe.innerHTML = ds_ob;
	ds_ob_clean();
}
function ds_echo(t) {
	ds_ob += t;
}

var ds_element; // Text Element...

var ds_monthnames = [
'Janvier', 'F&eacutevrier', 'Mars', 'Avril', 'Mai', 'Juin',
'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'D&eacute;cembre'
]; // You can translate it for your language.

var ds_daynames = [
'Dim', 'Lun', 'Mar', 'Me', 'Jeu', 'Ven', 'Sam'
]; // You can translate it for your language.

// Calendar template
function ds_template_main_above(t) {
	return '<table cellpadding="3" cellspacing="1" class="ds_tbl">'
		 + '<tr>'
		 + '<td class="ds_head" style="cursor: pointer" onclick="ds_py();">&lt;&lt;</td>'
		 + '<td class="ds_head" style="cursor: pointer" onclick="ds_pm();">&lt;</td>'
		 + '<td class="ds_head" style="cursor: pointer" onclick="ds_hi();" colspan="3">[Fermer]</td>'
		 + '<td class="ds_head" style="cursor: pointer" onclick="ds_nm();">&gt;</td>'
		 + '<td class="ds_head" style="cursor: pointer" onclick="ds_ny();">&gt;&gt;</td>'
		 + '</tr>'
		 + '<tr>'
		 + '<td colspan="7" class="ds_head">' + t + '</td>'
		 + '</tr>'
		 + '<tr>';
}

function ds_template_day_row(t) {
	return '<td class="ds_subhead">' + t + '</td>';
	// Define width in CSS, XHTML 1.0 Strict doesn't have width property for it.
}

function ds_template_new_week() {
	return '</tr><tr>';
}

function ds_template_blank_cell(colspan) {
	return '<td colspan="' + colspan + '"></td>'
}

function ds_template_day(d, m, y) {
	return '<td class="ds_cell" onclick="ds_onclick(' + d + ',' + m + ',' + y + ')">' + d + '</td>';
	// Define width the day row.
}

function ds_template_main_below() {
	return '</tr>' + '</table>';
}

// This one draws calendar...
function ds_draw_calendar(m, y) {
	// First clean the output buffer.
	ds_ob_clean();
	// Here we go, do the header
	ds_echo (ds_template_main_above(ds_monthnames[m - 1] + ' ' + y));
	for (i = 0; i < 7; i ++) {
		ds_echo (ds_template_day_row(ds_daynames[i]));
	}
	// Make a date object.
	var ds_dc_date = new Date();
	ds_dc_date.setMonth(m - 1);
	ds_dc_date.setFullYear(y);
	ds_dc_date.setDate(1);
	if (m == 1 || m == 3 || m == 5 || m == 7 || m == 8 || m == 10 || m == 12) {
		days = 31;
	}
	else if (m == 4 || m == 6 || m == 9 || m == 11) {
		days = 30;
	}
	else {
		days = (y % 4 == 0) ? 29 : 28;
	}
	var first_day = ds_dc_date.getDay();
	var first_loop = 1;
	// Start the first week
	ds_echo (ds_template_new_week());
	// If sunday is not the first day of the month, make a blank cell...
	if (first_day != 0) {
		ds_echo (ds_template_blank_cell(first_day));
	}
	var j = first_day;
	for (i = 0; i < days; i ++) {
		// Today is sunday, make a new week.
		// If this sunday is the first day of the month,
		// we've made a new row for you already.
		if (j == 0 && !first_loop) {
			// New week!!
			ds_echo (ds_template_new_week());
		}
		
		ds_echo (ds_template_day(i + 1, m, y)); // Make a row of that day!
		first_loop = 0; // This is not first loop anymore...
		
		// What is the next day?
		j ++;
		j %= 7;
	}
	
	ds_echo (ds_template_main_below()); // Do the footer
	ds_ob_flush();                      // And let's display..
	ds_ce.scrollIntoView();             // Scroll it into view.
}

// A function to show the calendar.
// When user click on the date, it will set the content of t.
function ds_sh(t,i) {
	// Set the element to set...
	ds_element = t;
	var saisie = (t.value).split('/');
	var date = new Date(eval(saisie[2]),eval(saisie[1])-1,eval(saisie[0]));	
	//caption
	if(i == 0)
		document.getElementById('ds_conclass').caption.innerHTML ='D&eacutebut des mesures';
	else
		document.getElementById('ds_conclass').caption.innerHTML ='Fin des mesures';

	// Make a new date, and set the current month and year.
	//var ds_sh_date = new Date();
	var ds_sh_date = date;	
	ds_c_month = ds_sh_date.getMonth() + 1;
	ds_c_year = ds_sh_date.getFullYear();
	// Draw the calendar
	ds_draw_calendar(ds_c_month, ds_c_year);
	// To change the position properly, we must show it first.
	ds_ce.style.display = '';
	widthDate = t.clientWidth; 
	widthCalendar = ds_ce.clientWidth;
	// Move the calendar container!
	the_left = ds_getleft(t);
	the_left -= (widthCalendar-widthDate)/2;
	the_top = ds_gettop(t) + t.offsetHeight;
	ds_ce.style.left = the_left + 'px';
	ds_ce.style.top = the_top + 'px';
	// Scroll it into view.
	ds_ce.scrollIntoView();
}

// Hide the calendar.
function ds_hi() {ds_ce.style.display = 'none';}

// Moves to the next month...
function ds_nm() {
	// Increase the current month.
	ds_c_month ++;
	// We have passed December, let's go to the next year.
	// Increase the current year, and set the current month to January.
	if (ds_c_month > 12) {
		ds_c_month = 1; 
		ds_c_year++;
	}
	// Redraw the calendar.
	ds_draw_calendar(ds_c_month, ds_c_year);
}

// Moves to the previous month...
function ds_pm() {
	ds_c_month = ds_c_month - 1; // Can't use dash-dash here, it will make the page invalid.
	// We have passed January, let's go back to the previous year.
	// Decrease the current year, and set the current month to December.
	if (ds_c_month < 1) {
		ds_c_month = 12; 
		ds_c_year = ds_c_year - 1; // Can't use dash-dash here, it will make the page invalid.
	}
	// Redraw the calendar.
	ds_draw_calendar(ds_c_month, ds_c_year);
}

// Moves to the next year...
function ds_ny() {
	ds_c_year++; // Increase the current year.
	ds_draw_calendar(ds_c_month, ds_c_year); // Redraw the calendar.
}

// Moves to the previous year...
function ds_py() {
	// Decrease the current year.
	ds_c_year = ds_c_year - 1;               // Can't use dash-dash here, it will make the page invalid.
	ds_draw_calendar(ds_c_month, ds_c_year); // Redraw the calendar.
}

// Format the date to output.
function ds_format_date(d, m, y) {
	m2 = '00' + m; // 2 digits month.
	m2 = m2.substr(m2.length - 2);
	d2 = '00' + d; // 2 digits day.
	d2 = d2.substr(d2.length - 2);
	return d2 + '/' + m2 + '/' + y;
}

// When the user clicks the day.
function ds_onclick(d, m, y) {
	ds_hi(); // Hide the calendar.
	
	if (typeof(ds_element.value) != 'undefined') {
		// Set the value of it, if we can.
		ds_element.value = ds_format_date(d, m, y);
	}
	else if (typeof(ds_element.innerHTML) != 'undefined') {
		// Maybe we want to set the HTML in it.
		ds_element.innerHTML = ds_format_date(d, m, y);
	}
	else {
		// I don't know how should we display it, just alert it to user.
		alert (ds_format_date(d, m, y));
	}
}
// ]]> -->
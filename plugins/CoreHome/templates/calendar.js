
Date.prototype.getWeek = function() {
var onejan = new Date(this.getFullYear(),0,1);
return Math.ceil((((this - onejan) / 86400000) + onejan.getDay())/7);
}

var splitDate = piwik.currentDateString.split("-");

var currentYear = splitDate[0];
var currentMonth = splitDate[1] - 1;
var currentDay = splitDate[2];

var currentDate = new Date(currentYear, currentMonth, currentDay);
var todayDate = new Date;
var todayMonth = todayDate.getMonth();
var todayYear = todayDate.getFullYear();
var todayDay = todayDate.getDate();

function isDateSelected( date )
{
	var valid = false;

	var dateMonth = date.getMonth();
	var dateYear = date.getFullYear();
	var dateDay = date.getDate();
	var style = '';
	
	if( date.toLocaleDateString() == todayDate.toLocaleDateString())
	{
		style = style + 'dateToday ';
	}

	// we dont color dates in the future
	if( dateMonth == todayMonth
		&& dateYear == todayYear
		&& dateDay >= todayDay
	)
	{
		return [true, style];		
	}
	
	// we dont color dates before the minimum date
	if( dateYear < piwik.minDateYear
		|| ( dateYear == piwik.minDateYear
				&& 
					(
						(dateMonth == piwik.minDateMonth - 1
						&& dateDay < piwik.minDateDay)
					||  (dateMonth < piwik.minDateMonth - 1)
				)
			)
	)
	{
		return [true, style];		
	}
	
	// we color all day of the month for the same year for the month period
	if(piwik.period == "month"
		&& dateMonth == currentMonth
		&& dateYear == currentYear
	)
	{
		valid = true;
	}
	// we color all day of the year for the year period
	else if(piwik.period == "year"
			&& dateYear == currentYear
	)
	{
		valid = true;
	}
	else if(piwik.period == "week"
			&& date.getWeek() == currentDate.getWeek()
			&& dateYear == currentYear
	)
	{
		valid = true;
	}
	else if( piwik.period == "day"  
				&& dateDay == currentDay
				&& dateMonth == currentMonth
				&& dateYear == currentYear
		)
	{
		valid = true;
	}
	
	if(valid)
	{
		return [true, style+'dateUsedStats'];
	}
	return [true, style];
}


function updateDate()
{
	var date = formatDate(popUpCal.getDateFor($('#calendar')[0]));

	// available in global scope
	var currentUrl = window.location.href;
	if((startStrDate = currentUrl.indexOf("date")) >= 0)
	{
		// look for the & after the date
		var endStrDate = currentUrl.indexOf("&", startStrDate);
		if(endStrDate == -1)
		{
			endStrDate = currentUrl.length;
		}

		var dateToReplace = currentUrl.substring( 
							startStrDate + 4+1, 
							endStrDate
						);
		regDateToReplace = new RegExp(dateToReplace, 'ig');
		currentUrl = currentUrl.replace( regDateToReplace, date );		
	}
	else
	{
		currentUrl = currentUrl + '&date=' + date;
	}
	
	window.location.href = currentUrl;
}

function formatDate(date) 
{
	var day = date.getDate();
	var month = date.getMonth() + 1;
	return date.getFullYear() + '-'
		+ (month < 10 ? '0' : '') + month + '-'
		+ (day < 10 ? '0' : '') + day ;
}

$(document).ready(function(){
	$("#calendar").calendar({
			onSelect: updateDate,
			showOtherMonths: true,
			dateFormat: 'DMY-',
			firstDay: 1,
			minDate: new Date(piwik.minDateYear, piwik.minDateMonth - 1, piwik.minDateDay),
			maxDate: new Date(),
			changeFirstDay: false,
			prevText: "",
			nextText: "",
			currentText: "",
			customDate: isDateSelected,
			dayNames: [
				_pk_translate('CoreHome_DaySu'),
				_pk_translate('CoreHome_DayMo'),
				_pk_translate('CoreHome_DayTu'),
				_pk_translate('CoreHome_DayWe'),
				_pk_translate('CoreHome_DayTh'),
				_pk_translate('CoreHome_DayFr'),
				_pk_translate('CoreHome_DaySa')],
			monthNames: [
				_pk_translate('CoreHome_MonthJanuary'),
				_pk_translate('CoreHome_MonthFebruary'),
				_pk_translate('CoreHome_MonthMarch'),
				_pk_translate('CoreHome_MonthApril'),
				_pk_translate('CoreHome_MonthMay'),
				_pk_translate('CoreHome_MonthJune'),
				_pk_translate('CoreHome_MonthJuly'),
				_pk_translate('CoreHome_MonthAugust'),
				_pk_translate('CoreHome_MonthSeptember'),
				_pk_translate('CoreHome_MonthOctober'),
				_pk_translate('CoreHome_MonthNovember'),
				_pk_translate('CoreHome_MonthDecemeber')]
			},
			currentDate);
	}
);

<?php if (! defined('ABSPATH')) exit; // Exit if accessed directly
global $NTS_TIME_WEEKDAYS_SHORT;
$NTS_TIME_WEEKDAYS_SHORT = array( 
/* translators: short Sunday */
	HCM::__('Sun'),
/* translators: short Monday */
	HCM::__('Mon'),
/* translators: short Tuesday */
	HCM::__('Tue'),
/* translators: short Wednesday */
	HCM::__('Wed'),
/* translators: short Thursday */
	HCM::__('Thu'),
/* translators: short Friday */
	HCM::__('Fri'),
/* translators: short Saturday */
	HCM::__('Sat')
	);

global $NTS_TIME_WEEKDAYS_LONG;
if( defined('WPINC') ){
	$NTS_TIME_WEEKDAYS_LONG = array( 
		__('Sunday'),
		__('Monday'),
		__('Tuesday'),
		__('Wednesday'),
		__('Thursday'),
		__('Friday'),
		__('Saturday')
		);
}
else {
	$NTS_TIME_WEEKDAYS_LONG = array( 
		HCM::__('Sunday'),
		HCM::__('Monday'),
		HCM::__('Tuesday'),
		HCM::__('Wednesday'),
		HCM::__('Thursday'),
		HCM::__('Friday'),
		HCM::__('Saturday')
		);
}

global $NTS_TIME_MONTH_NAMES;
$NTS_TIME_MONTH_NAMES = array(
/* translators: short January */
	HCM::__('Jan'),
/* translators: short February */
	HCM::__('Feb'),
/* translators: short March */
	HCM::__('Mar'),
/* translators: short April */
	HCM::__('Apr'),
/* translators: short May */
	HCM::__('May'),
/* translators: short June */
	HCM::__('Jun'),
/* translators: short July */
	HCM::__('Jul'),
/* translators: short August */
	HCM::__('Aug'),
/* translators: short September */
	HCM::__('Sep'),
/* translators: short October */
	HCM::__('Oct'),
/* translators: short November */
	HCM::__('Nov'),
/* translators: short December */
	HCM::__('Dec')
	);

global $NTS_TIME_MONTH_NAMES_REPLACE;
$NTS_TIME_MONTH_NAMES_REPLACE = array( 
/* translators: short January */
	'Jan'	=> HCM::__('Jan'),
/* translators: short February */
	'Feb'	=> HCM::__('Feb'),
/* translators: short March */
	'Mar'	=> HCM::__('Mar'),
/* translators: short April */
	'Apr'	=> HCM::__('Apr'),
/* translators: short May */
	'May'	=> HCM::__('May'),
/* translators: short June */
	'Jun'	=> HCM::__('Jun'),
/* translators: short July */
	'Jul'	=> HCM::__('Jul'),
/* translators: short August */
	'Aug'	=> HCM::__('Aug'),
/* translators: short September */
	'Sep'	=> HCM::__('Sep'),
/* translators: short October */
	'Oct'	=> HCM::__('Oct'),
/* translators: short November */
	'Nov'	=> HCM::__('Nov'),
/* translators: short December */
	'Dec'	=> HCM::__('Dec')
	);

/* new object oriented style */
class Hc_time extends DateTime {
	public $timeFormat = 'g:ia';
	public $dateFormat = 'j M Y';
	public $weekStartsOn = 0;

	var $weekdays = array();
	var $weekdaysShort = array();
	var $monthNames = array();
	var $timezone = '';
	protected $disable_weekdays = array();

	function __construct( $time = 0, $tz = '' )
	{
//static $initCount;
//$initCount++;
//echo "<h2>init $initCount</h2>";
		if( strlen($time) == 0 )
			$ts = 0;
		if( ! $time )
			$time = time();
		if( is_array($time) )
			$time = $time[0];

		parent::__construct();
		if( $time > 0 ){
			$this->setTimestamp( $time );
		}
		else {
			$this->setNow();
		}

		if( $tz ){
			$this->setTimezone( $tz );
		}
	}

	public function parseTextTimePeriods( $text )
	{
// echo "getting: '$text'<br>";
		$return = NULL;

		$text = trim($text);
		if( ! strlen($text) ){
			return $return;
		}

		if( strpos($text, ',') !== FALSE ){
			$return = array();
			$texts = explode(',', $text);
			reset( $texts );
			foreach( $texts as $text2 ){
				$sub_return = $this->parseTextTimePeriods( $text2 );
				if( $sub_return ){
					$return[] = $sub_return;
				}
			}
			return $return;
		}

		if( strpos($text, '-') !== FALSE ){
			$texts = explode('-', $text);

			$return = array();
			reset( $texts );
			foreach( $texts as $text2 ){
				$sub_return = $this->parseTextTimePeriods( $text2 );
				if( $sub_return === NULL ){
					$return = NULL;
					return $return;
				}
				else {
					$return[] = $sub_return;
				}
			}
			if( $return ){
				$return = join('-', $return);
			}
			else {
				$return = NULL;
			}
			return $return;
		}

		$parsed_ts = strtotime( $text );
// echo "parsed '$parsed_ts' from '$text'<br>";

		if( ! $parsed_ts ){
			return $return;
		}

		$start_day_ts = $this->setStartDay();
		$return = $parsed_ts - $start_day_ts;
		if( $return < 0 ){
			$return = NULL;
		}
// echo "returting '$return'<br>";
		return $return;
	}

	public function daysBetween( $another_date )
	{
		$this_ts = $this->getTimestamp();
		$this->setDateDb( $another_date )->setEndDay();
		$another_ts = $this->getTimestamp();
		$datediff = ($this_ts > $another_ts) ? ($this_ts - $another_ts) : ($another_ts - $this_ts);
		$return = ceil($datediff / (60 * 60 * 24));
		return $return;
	}

	public function set_disable_weekdays( $disable_weekdays )
	{
		if( $disable_weekdays !== NULL ){
			if( ! is_array($disable_weekdays) ){
				$disable_weekdays = array($disable_weekdays);
			}
		}
		$this->disable_weekdays = $disable_weekdays;
		return $this;
	}
	public function disable_weekdays()
	{
		return $this->disable_weekdays;
	}

	// checks the measure and modifies to the start of a measure unit
	// for example, today is 12 Sep, if modify is "+1 month", it jumps to 1 Oct, not 12 Oct
	public function cute_modify( $modify, $updown )
	{
		list( $qty, $measure ) = explode(' ', $modify);
		// strip s
		if( substr($measure, -1) == 's' ){
			$measure = substr($measure, 0, -1);
		}

		$this->modify( $modify );

		switch( $measure ){
			case 'year':
				if( $updown == '-' ){
					$this->setStartYear();
				}
				else {
					$this->modify('-1 ' . $measure);
					$this->setEndYear();
				}
				break;

			case 'month':
				// 14 sep: +2 month: 14 Nov -> 1 Oct, -2 month: 14 Aug -> 1 Sep
				if( $updown == '-' ){
					$this->setStartMonth();
				}
				else {
					$this->modify('-1 ' . $measure);
					$this->setEndMonth();
				}
				break;

			case 'week':
				if( $updown == '-' ){
					$this->setStartWeek();
				}
				else {
					$this->modify('-1 ' . $measure);
					$this->setEndWeek();
				}
				break;

			case 'day':
				if( $updown == '-' ){
					$this->setStartDay();
				}
				else {
					$this->modify('-1 ' . $measure);
					$this->setEndDay();
				}
				break;

			case 'hour':
				if( $updown == '-' ){
					$this->setStartHour();
				}
				else {
					$this->modify('-1 ' . $measure);
					$this->setEndHour();
				}
				break;
		}

		return $this;
	}

	public function modify( $modify )
	{
		// echo "MODIFY = '$modify'<br>";
		parent::modify( $modify );
		return $this;
	}

	public function weekStartsOn()
	{
		return $this->weekStartsOn;
	}

	public function isWeekStart()
	{
		$return = FALSE;
		$weekDay = $this->getWeekday();
		if( $weekDay == $this->weekStartsOn() ){
			$return = TRUE;
		}
		return $return;
	}

	public function getDatesRange( $date, $range )
	{
		$save_ts = $this->getTimestamp();

		if( ! $date ){
			$date = $this->setNow()->formatDate_Db();
		}
		$disable_weekdays = $this->disable_weekdays();

		$t = clone $this;
		switch( $range ){
			case 'custom':
				if( strpos($date, '_') !== FALSE ){
					list( $start_date, $end_date ) = explode('_', $date);
				}
				else {
					$start_date = $end_date = $date;
				}
				break;

			case 'now':
			case 'day':
				$start_date = $date;
				// $end_date = 0;
				$end_date = $date;
				break;

			case 'all':
				$start_date = $end_date = 0;
				break;

			case 'upcoming':
				$start_date = $date;
				$end_date = NULL;
				break;

			case 'week':
			case 'month':
				$this->setDateDb( $date );
				list( $start_date, $end_date ) = $this->getDates( $range, TRUE );
				break;

			default:
				if( strpos($date, '_') !== FALSE ){
					list( $start_date, $end_date ) = explode('_', $date);
				}
				else {
					$start_date = $date;
					$t->setDateDb( $start_date );
					$t->modify( '+' . $range );
					$end_date = $t->formatDate_Db();
				}
				break;
		}

		$this->setTimestamp( $save_ts );
		$return = array( $start_date, $end_date );
		return $return;
	}

	function getDates( $range, $start_end = FALSE )
	{
		$save_ts = $this->getTimestamp();

		$disable_weekdays = $this->disable_weekdays();
		$start_date = $end_date = 0;

		switch( $range ){
			case 'day':
				$start_date = $end_date = $this->formatDate_Db();
				break;

			case 'week':
				$this->setStartWeek();
				$start_date = $this->formatDate_Db();
				$this->setEndWeek();
				$end_date = $this->formatDate_Db();
				break;

			case 'month':
				$this->setStartMonth();
				$start_date = $this->formatDate_Db();
				$this->setEndMonth();
				$end_date = $this->formatDate_Db();
				break;
		}

		$return = array();

	// start and end only
		if( $start_end ){
			if( $disable_weekdays && (count($disable_weekdays) < 7)){
				$this->setDateDb( $start_date );
				$this_weekday = $this->getWeekDay();
				while( in_array($this_weekday, $disable_weekdays) ){
					$this->modify('+1 day');
					$this_weekday = $this->getWeekDay();
					$start_date = $this->formatDate_Db();
				}

				$this->setDateDb( $end_date );
				$this_weekday = $this->getWeekDay();
				while( in_array($this_weekday, $disable_weekdays) ){
					$this->modify('-1 day');
					$this_weekday = $this->getWeekDay();
					$end_date = $this->formatDate_Db();
				}
			}

			$return[] = $start_date;
			$return[] = $end_date;
		}
	// all
		else {
			if( $start_date && $end_date ){
				$this->setDateDb( $start_date );
				$rex_date = $start_date;
				while( $rex_date <= $end_date ){
					if( $disable_weekdays ){
						$this_weekday = $this->getWeekDay();
						if( ! in_array($this_weekday, $disable_weekdays) ){
							$return[] = $rex_date;
						}
					}
					else {
						$return[] = $rex_date;
					}

					$this->modify('+1 day');
					$rex_date = $this->formatDate_Db();
				}
			}
		}

		$this->setTimestamp( $save_ts );
		return $return;
	}

	public function formatTimeRange_Old( $ts1, $ts2 )
	{
		$return = array();
		$this->setTimestamp( $ts1 );
		$date1 = $this->formatDate_Db();
		$return[] = $this->formatDateFull() . ' ' . $this->formatTime();

		if( $ts2 > $ts1 ){
			$this->setTimestamp( $ts2 );
			$date2 = $this->formatDate_Db();
			if( $date2 == $date1 ){
				$return[] = $this->formatTime();
			}
			else {
				$return[] = $this->formatDateFull() . ' ' . $this->formatTime();
			}
		}

		$return = join( ' - ', $return );
		return $return;
	}

	public function getFormatTimeRange( $ts1, $ts2, $with_weekday = FALSE )
	{
		$return = array();

		$this->setTimestamp( $ts1 );
		$date1 = $this->formatDate_Db();
		$start_day1 = $this->setStartDay();
		$time1 = $ts1 - $start_day1;

		$this->setTimestamp( $ts2 );
		$start_day2 = $this->setStartDay();
		$time2 = $ts2 - $start_day2;
		if( ! $time2 ){
			$this->modify('-1 second');
		}
		$date2 = $this->formatDate_Db();

		list( $start_date_view, $end_date_view ) = $this->_formatDateRange( $date1, $date2, $with_weekday );
		$return['date'] = array();
		$return['date'][] = $start_date_view;
		if( $end_date_view ){
			$return['date'][] = $end_date_view;
		}

		if( $time1 OR $time2 ){
			$time1_view = $this->formatTimeOfDay($time1);
			$time2_view = $this->formatTimeOfDay($time2);

			$return['time'] = array($time1_view, $time2_view);
		}
		return $return;
	}

	public function formatTimeRange( $ts1, $ts2, $with_weekday = FALSE )
	{
		$return = NULL;

		$format = $this->getFormatTimeRange( $ts1, $ts2, $with_weekday );
		if( ! isset($format['date']) ){
			$format['date'] = array();
		}
		if( ! isset($format['time']) ){
			$format['time'] = array();
		}

		if( (count($format['date']) == 0) && (count($format['time']) == 0) ){
		}
		elseif( (count($format['date']) == 0) && (count($format['time']) == 1) ){
			$return = $format['time'][0];
		}
		elseif( (count($format['date']) == 0) && (count($format['time']) == 2) ){
			$return = $format['time'][0] . ' - ' . $format['time'][1];
		}
		elseif( (count($format['date']) == 1) && (count($format['time']) == 0) ){
			$return = $format['date'][0];
		}
		elseif( (count($format['date']) == 1) && (count($format['time']) == 1) ){
			$return = $format['date'][0] . ' ' . $format['time'][0];
		}
		elseif( (count($format['date']) == 1) && (count($format['time']) == 2) ){
			$return = $format['date'][0] . ' ' . $format['time'][0] . ' - ' . $format['time'][1];
		}
		elseif( (count($format['date']) == 2) && (count($format['time']) == 0) ){
			$return = $format['date'][0] . ' - ' . $format['date'][1];
		}
		elseif( (count($format['date']) == 2) && (count($format['time']) == 1) ){
			$return = $format['date'][0] . ' - ' . $format['date'][1] . ' ' . $format['time'][0];
		}
		elseif( (count($format['date']) == 2) && (count($format['time']) == 2) ){
			$return = $format['date'][0] . ' ' . $format['time'][0] . ' - ' . $format['date'][1] . ' ' . $format['time'][1];
		}

		return $return;
	}

	public function formatDateRange( $date1, $date2, $with_weekday = FALSE )
	{
		list( $start_date_view, $end_date_view ) = $this->_formatDateRange( $date1, $date2, $with_weekday );

		if( $end_date_view ){
			$return = $start_date_view . ' - ' . $end_date_view;
		}
		else {
			$return = $start_date_view;
		}
		return $return;
	}

	protected function _formatDateRange( $date1, $date2, $with_weekday = FALSE )
	{
		$return = array();
		$skip = array();

		if( $date1 == $date2 ){
			$this->setDateDb( $date1 );
			$view_date1 = $this->formatDate();
			if( $with_weekday ){
				$view_date1 = $this->formatWeekdayShort() . ', ' . $view_date1;
			}
			$return[] = $view_date1;
			$return[] = NULL;
			return $return;
		}

		$this->setDateDb( $date1 );
		$year1 = $this->getYear();
		$month1 = $this->getMonth();

		$this->setDateDb( $date2 );
		$year2 = $this->getYear();
		$month2 = $this->getMonth();

		if( $year2 == $year1 )
			$skip['year'] = TRUE;
		if( $month2 == $month1 )
			$skip['month'] = TRUE;

		if( $skip ){
			$date_format = $this->dateFormat;
			$date_format_short = $date_format;

			$tags = array('m', 'n', 'M');
			foreach( $tags as $t ){
				$pos_m_original = strpos($date_format_short, $t);
				if( $pos_m_original !== FALSE )
					break;
			}

			if( isset($skip['year']) ){
				$pos_y = strpos($date_format_short, 'Y');
				if( $pos_y == 0 ){
					$date_format_short = substr_replace( $date_format_short, '', $pos_y, 2 );
				}
				else {
					$date_format_short = substr_replace( $date_format_short, '', $pos_y - 1, 2 );
				}
			}

			if( isset($skip['month']) ){
				$tags = array('m', 'n', 'M');
				foreach( $tags as $t ){
					$pos_m = strpos($date_format_short, $t);
					if( $pos_m !== FALSE )
						break;
				}

				// month going first, do not replace
				if( $pos_m_original == 0 ){
					// $date_format_short = substr_replace( $date_format_short, '', $pos_m, 2 );
				}
				else {
					// month going first, do not replace
					if( $pos_m == 0 ){
						$date_format_short = substr_replace( $date_format_short, '', $pos_m, 2 );
					}
					else {
						$date_format_short = substr_replace( $date_format_short, '', $pos_m - 1, 2 );
					}
				}
			}

			if( $pos_y == 0 ){ // skip year in the second part
				$date_format1 = $date_format;
				$date_format2 = $date_format_short;
			}
			else {
				$date_format1 = $date_format_short;
				$date_format2 = $date_format;
			}

			$this->setDateDb( $date1 );

			$view_date1 = $this->formatDate( $date_format1 );
			if( $with_weekday ){
				$view_date1 = $this->formatWeekdayShort() . ', ' . $view_date1;
			}
			$return[] = $view_date1;

			$this->setDateDb( $date2 );
			$view_date2 = $this->formatDate( $date_format2 );
			if( $with_weekday ){
				$view_date2 = $this->formatWeekdayShort() . ', ' . $view_date2;
			}
			$return[] = $view_date2;
		}
		else {
			$this->setDateDb( $date1 );
			$view_date1 = $this->formatDate();
			if( $with_weekday ){
				$view_date1 = $this->formatWeekdayShort() . ', ' . $view_date1;
			}
			$return[] = $view_date1;

			$this->setDateDb( $date2 );
			$view_date2 = $this->formatDate();
			$view_date2 = $this->formatDate( $date_format2 );
			if( $with_weekday ){
				$view_date2 = $this->formatWeekdayShort() . ', ' . $view_date2;
			}
			$return[] = $view_date2;
		}

		// $return = join( ' - ', $return );
		return $return;
	}

	function formatToDatepicker( $dateFormat = '' )
    {
		if( ! $dateFormat )
			$dateFormat = $this->dateFormat;

		$pattern = array(
			//day
			'd',	//day of the month
			'j',	//3 letter name of the day
			'l',	//full name of the day
			'z',	//day of the year

			//month
			'F',	//Month name full
			'M',	//Month name short
			'n',	//numeric month no leading zeros
			'm',	//numeric month leading zeros

			//year
			'Y', //full numeric year
			'y'	//numeric year: 2 digit
			);

		$replace = array(
			'dd','d','DD','o',
			'MM','M','m','mm',
			'yyyy','y'
		);
		foreach($pattern as &$p){
			$p = '/'.$p.'/';
		}
		return preg_replace( $pattern, $replace, $dateFormat );
	}

	function sortWeekdays( $wds ) // sort weekdays according to weekStartsOn
	{
		$return = array();
		$later = array();

		sort( $wds );
		reset( $wds );
		foreach( $wds as $wd ){
			if( $wd < $this->weekStartsOn )
				$later[] = $wd;
			else
				$return[] = $wd;
		}
		$return = array_merge( $return, $later );
		return $return;
	}

	function formatWeekdays( $wds = array() )
	{
		$wds = $this->sortWeekdays( $wds );

		$weekdays_order = array();
		$weekdays_order_index = array();
		$order = 0;
		for( $ii = 0; $ii <= 6; $ii++ ){
			$wi = $this->weekStartsOn + $ii;
			if( $wi >= 7 ){
				$wi = $wi - 7;
			}
			$weekdays_order_index[ $wi ] = $order;
			$weekdays_order[ $order ] = $wi;
			$order++;
		}

		$weekdays = array();
		$wdi = 0;
		reset( $wds );
		foreach( $wds as $wd ){
			if( ! isset($weekdays[$wdi]) ){
				$weekdays[$wdi] = $wd;
			}
			elseif( is_array($weekdays[$wdi]) ){
				$my_index = $weekdays_order_index[$wd];
				$previous_wd = isset($weekdays_order[($my_index - 1)]) ? $weekdays_order[($my_index - 1)] : -1;
				if( $weekdays[$wdi][1] == $previous_wd ){
					$weekdays[$wdi][1] = $wd;
				}
				else {
					$wdi++;
					$weekdays[$wdi] = $wd;
				}
			}
			else {
				$my_index = $weekdays_order_index[$wd];
				$previous_wd = isset($weekdays_order[($my_index - 1)]) ? $weekdays_order[($my_index - 1)] : -1;
				if( $weekdays[$wdi] == $previous_wd ){
					$weekdays[$wdi] = array($weekdays[$wdi], $wd);
				}
				else {
					$wdi++;
					$weekdays[$wdi] = $wd;
				}
			}
		}

	/* build view */
		$weekday_view = array();
		reset( $weekdays );
		foreach( $weekdays as $wd ){
			if( is_array($wd) ){
				$weekday_view[] = $this->formatWeekdayShort($wd[0]) . ' - ' . $this->formatWeekdayShort($wd[1]);
			}
			else {
				$weekday_view[] = $this->formatWeekdayShort($wd);
			}
		}

		return $weekday_view;
	}

	function setNow(){
		$this->setTimestamp( time() );
		return $this;
		}

	function differ( $other )
	{
		if( ! is_object($other) ){
			$other_date = $other;
			$other = clone $this;
			$other->setDateDb( $other_date );
		}
		else {
			$other_date = $other->formatDate_Db();
		}

		$this_date = $this->formatDate_Db();
		if( $this_date == $other_date ){
			$delta = 0;
		}
		elseif( $this_date > $other_date ){
			$delta = $this->getTimestamp() - $other->getTimestamp();
		}
		else {
			$delta = $other->getTimestamp() - $this->getTimestamp();
		}

		$return = 0;
		if( $delta ){
			$return = floor( $delta / (24 * 60 * 60) );
		}
		return $return;
	}

	function getDatesOfMonth(){
		$return = array();

		$this->setEndMonth();
		$end_month = $this->formatDate_Db();

		$this->setStartMonth();
		$rex_date = $this->formatDate_Db();
		while( $rex_date <= $end_month ){
			$return[] = $rex_date;
			$this->modify( '+1 day' );
			$rex_date = $this->formatDate_Db();
			}
		return $return;
		}
		
	static function expandPeriodString( $what, $multiply = 1 ){
		$string = '';
		switch( $what ){
			case 'd':
				$string = '+' . 1 * $multiply . ' days';
				break;
			case '2d':
				$string = '+' . 2 * $multiply . ' days';
				break;
			case 'w':
				$string = '+' . 1 * $multiply . ' weeks';
				break;
			case '2w':
				$string = '+' . 2 * $multiply . ' weeks';
				break;
			case '3w':
				$string = '+' . 3 * $multiply . ' weeks';
				break;
			case '6w':
				$string = '+' . 6 * $multiply . ' weeks';
				break;
			case 'm':
				$string = '+' . 1 * $multiply . ' months';
				break;
			}
		return $string;
		}

	function setTimezone( $tz ){
		if( is_array($tz) )
			$tz = $tz[0];

//		if( preg_match('/^-?[\d\.]$/', $tz) ){
//			$currentTz = ($tz >= 0) ? '+' . $tz : $tz;
//			$tz = "Etc/GMT$currentTz";
//			echo "<br><br>Setting timezone as Etc/GMT$currentTz<br><br>";
//			}
		if( ! $tz )
			$tz = date_default_timezone_get();

		$this->timezone = $tz;
		$tz = new DateTimeZone($tz);
		parent::setTimezone( $tz );
		}

	function getLastDayOfMonth(){
		$thisYear = $this->getYear(); 
		$thisMonth = $this->getMonth();

		$this->setDateTime( $thisYear, ($thisMonth + 1), 0, 0, 0, 0 );
		$return = $this->format( 'j' );
		return $return;
		}

	function getTimestamp(){
		if( function_exists('date_timestamp_get') ){
			return parent::getTimestamp();
			}
		else {
			$return = $this->format('U');
			return $return;
			}
		}

	function setTimestamp( $ts )
	{
		if( function_exists('date_timestamp_set') ){
			parent::setTimestamp( $ts );
		}
		else {
			$strTime = '@' . $ts;
			parent::__construct( $strTime );
			$this->setTimezone( $this->timezone );
		}
		return $this;
	}

	static function splitDate( $string )
	{
		$year = substr( $string, 0, 4 );
		$month = substr( $string, 4, 2 );
		$day = substr( $string, 6, 4 );
		$return = array( $year, $month, $day );
		return $return;
	}

	static function splitDateTime2( $string )
	{
		$year = substr( $string, 0, 4 );
		$month = substr( $string, 4, 2 );
		$day = substr( $string, 6, 2 );
		$hour = substr( $string, 8, 2 );
		$minute = substr( $string, 10, 2 );

		$return = array( $year, $month, $day, $hour, $minute );
		return $return;
	}

	function timestampFromDbDate( $date ){
		list( $year, $month, $day ) = Hc_time::splitDate( $date );
		$this->setDateTime( $year, $month, $day, 0, 0, 0 );
		$return = $this->getTimestamp();
		return $return;
		}

	function getParts(){
		$return = array( $this->format('Y'), $this->format('m'), $this->format('d'), $this->format('H'), $this->format('i') );
		return $return;
		}

	function getYear(){
		$return = $this->format('Y');
		return $return;
		}

	function getMonth(){
		$return = $this->format('m');
		return $return;
		}

	public function getMonthNames()
	{
		$return = array();
		foreach( range(1, 12) as $m ){
			$return[ $m ] = $this->getMonthName($m);
		}
		return $return;
	}

	public function getMonthName( $thisMonth = NULL )
	{
		global $NTS_TIME_MONTH_NAMES;
		if( $thisMonth === NULL ){
			$thisMonth = (int) $this->getMonth();
		}
		$return = $NTS_TIME_MONTH_NAMES[ $thisMonth - 1 ];
		return $return;
	}

	public function getHour()
	{
		$return = $this->format('G');
		return $return;
	}

	public function getMinute()
	{
		$return = $this->format('i');
		return $return;
	}

	public function getDay()
	{
		$return = $this->format('d');
		return $return;
	}

	function getDayShort()
	{
		$return = $this->format('j');
		return $return;
	}

	public function setStartHour()
	{
		$thisYear = $this->getYear(); 
		$thisMonth = $this->getMonth();
		$thisDay = $this->getDay();
		$thisHour = $this->getHour();

		$this->setDateTime( $thisYear, $thisMonth, $thisDay, $thisHour, 0, 0 );
		$return = $this->getTimestamp();
		return $return;
	}

	public function setEndHour()
	{
		$thisYear = $this->getYear(); 
		$thisMonth = $this->getMonth();
		$thisDay = $this->getDay();
		$thisHour = $this->getHour();

		$this->setDateTime( $thisYear, $thisMonth, $thisDay, ($thisHour + 1), 0, 0 );
		$return = $this->getTimestamp();
		return $return;
	}

	public function getStartDay()
	{
		$thisYear = $this->getYear(); 
		$thisMonth = $this->getMonth();
		$thisDay = $this->getDay();

		$this->setDateTime( $thisYear, $thisMonth, $thisDay, 0, 0, 0 );
		$return = $this->getTimestamp();
		return $return;
	}

	public function setStartDay()
	{
		$thisYear = $this->getYear(); 
		$thisMonth = $this->getMonth();
		$thisDay = $this->getDay();

		$this->setDateTime( $thisYear, $thisMonth, $thisDay, 0, 0, 0 );
		$return = $this->getTimestamp();
		return $return;
	}

	public function setEndDay()
	{
		$thisYear = $this->getYear(); 
		$thisMonth = $this->getMonth();
		$thisDay = $this->getDay();

		$this->setDateTime( $thisYear, $thisMonth, ($thisDay + 1), 0, 0, 0 );
		$return = $this->getTimestamp();
		return $return;
	}

	public function setNextDay()
	{
		$this->setStartDay();
		$this->modify( '+1 day' );
	}

	public function getEndDay()
	{
		$thisYear = $this->getYear(); 
		$thisMonth = $this->getMonth();
		$thisDay = $this->getDay();

		$this->setDateTime( $thisYear, $thisMonth, ($thisDay + 1), 0, 0, 0 );
		$return = $this->getTimestamp();
		return $return;
	}

	public function setStartWeek()
	{
		$this->setStartDay();
		$weekDay = $this->getWeekday();

		while( $weekDay != $this->weekStartsOn ){
			$this->modify( '-1 day' );
			$weekDay = $this->getWeekday();
			}
		return $this;
	}

	function setEndWeek(){
		$this->setStartDay();
		$this->modify( '+1 day' );
		$weekDay = $this->getWeekday();

		while( $weekDay != $this->weekStartsOn ){
			$this->modify( '+1 day' );
			$weekDay = $this->getWeekday();
			}
		$this->modify( '-1 day' );
		return $this;
		}

	function setStartMonth()
	{
		$thisYear = $this->getYear(); 
		$thisMonth = $this->getMonth();
		$this->setDateTime( $thisYear, $thisMonth, 1, 0, 0, 0 );
		return $this;
	}

	function setEndMonth()
	{
		$thisYear = $this->getYear(); 
		$thisMonth = $this->getMonth();
		$this->setDateTime( $thisYear, ($thisMonth + 1), 1, 0, 0, -1 );
		return $this;
	}

	function setStartYear()
	{
		$thisYear = $this->getYear(); 
		$this->setDateTime( $thisYear, 1, 1, 0, 0, 0 );
		return $this;
	}

	function timezoneShift(){
		$return = 60 * 60 * $this->timezone;
		return $return;
		}

	function setDateTime( $year, $month, $day, $hour, $minute, $second )
	{
		$this->setDate( $year, $month, $day );
		$this->setTime( $hour, $minute, $second );
		return $this;
	}

	public function setDateDb( $date )
	{
		list( $year, $month, $day ) = Hc_time::splitDate( $date );
		$this->setDateTime( $year, $month, $day, 0, 0, 0 );
		return $this;
	}

	function formatPeriodOfDay( $start, $end )
	{
		if( 
			( $start == 0 ) &&
			( ( $end == 0 ) OR ( $end == 24*60*60 ) )
			){
/* translators: duration, during all day */
			$return = HCM::__('All Day');
		}
		else {
			$return = $this->formatTimeOfDay($start) . ' - ' .  $this->formatTimeOfDay($end);
		}
		return $return;
	}

	function formatTimeOfDay( $ts ){
		$this->setDateDb('20130315');
		if( $ts ){
			$this->modify( '+' . $ts . ' seconds' );
		}
		return $this->formatTime();
		}

	public function durationToSeconds( $duration )
	{
		$this->setDateDb('20160803');
		$start_ts = $this->getTimestamp();
		$this->modify('+ ' . $duration);
		$end_ts = $this->getTimestamp();
		$return = $end_ts - $start_ts;
		return $return;
	}

	public function timeFormat()
	{
		return $this->timeFormat;
	}

	function formatTime( $duration = 0, $displayTimezone = 0 )
	{
		$return = $this->format( $this->timeFormat );
		if( $duration ){
			$this->modify( '+' . $duration . ' seconds' );
			$return .= ' - ' . $this->format( $this->timeFormat );
		}

		if( $displayTimezone ){
			$return .= ' [' . Hc_time::timezoneTitle($this->timezone) . ']';
		}
		return $return;
	}

	function formatDate( $format = '' ){
		global $NTS_TIME_MONTH_NAMES_REPLACE;
		if( ! $format )
			$format = $this->dateFormat;

		$return = $this->format( $format );
	// replace months 
		$return = str_replace( array_keys($NTS_TIME_MONTH_NAMES_REPLACE), array_values($NTS_TIME_MONTH_NAMES_REPLACE), $return );
		return $return;
		}

	static function formatDateParam( $year, $month, $day ){
		$return = sprintf("%04d%02d%02d", $year, $month, $day);
		return $return;
		}

	function formatDateDb()
	{
		return $this->formatDate_Db();
	}

	public function setDateTimeDb2( $dt2 )
	{
		list( $year, $month, $day, $hour, $minute ) = Hc_time::splitDateTime2( $dt2 );
		$this->setDateTime( $year, $month, $day, $hour, $minute, 0 );
		return $this;
	}

	public function formatDateTimeDb2()
	{
		$date = $this->formatDateDb();
		$time = $this->formatTimeDb2();
		$return = $date . $time;
		return $return;
	}

	public function formatDateDb2()
	{
		return $this->formatDate_Db();
	}

	public function getSecondsOfDay()
	{
		$h = $this->getHour();
		$m = $this->getMinute();

		$return = $h * 60 * 60 + $m * 60;
		return $return;
	}

	public function formatTimeDb2()
	{
		$h = $this->getHour();
		$m = $this->getMinute();

		$h = str_pad( $h, 2, 0, STR_PAD_LEFT );
		$m = str_pad( $m, 2, 0, STR_PAD_LEFT );

		$return = $h . $m;
		return $return;
	}

	function formatDate_Db(){
		$dateFormat = 'Ymd';
		$return = $this->format( $dateFormat );
		return $return;
		}

	function formatTime_Db(){
		$dateFormat = 'Hi';
		$return = $this->format( $dateFormat );
		return $return;
		}

	function getWeekday(){
		$return = $this->format('w');
		return $return;
		}

	public function formatWeekdayShort( $wd = -1 )
	{
		global $NTS_TIME_WEEKDAYS_SHORT;
		if( $wd == -1 )
			$wd = $this->format('w');
		$return = $NTS_TIME_WEEKDAYS_SHORT[ $wd ];
		return $return;
	}

	function formatWeekday( $wd = -1 )
	{
		global $NTS_TIME_WEEKDAYS_LONG;
		if( $wd == -1 )
			$wd = $this->format('w');
		$return = $NTS_TIME_WEEKDAYS_LONG[ $wd ];
		return $return;
	}

	function formatFull( $duration = 0, $displayTimezone = 0 )
	{
		$return = $this->formatWeekdayShort() . ', ' . $this->formatDate() . ' ' . $this->formatTime($duration, $displayTimezone);
		return $return;
	}

	public function formatDateFullShort()
	{
		$return = $this->formatWeekdayShort() . ', ' . $this->formatDate();
		return $return;
	}

	public function formatDateFull()
	{
		$return = $this->formatWeekday() . ', ' . $this->formatDate();
		return $return;
	}

	static function timezoneTitle( $tz ){
		if( is_array($tz) )
			$tz = $tz[0];
		$tzobj = new DateTimeZone( $tz );
		$dtobj = new DateTime();
		$dtobj->setTimezone( $tzobj );
		$offset = $tzobj->getOffset($dtobj);

		$offsetString = 'GMT';
		$offsetString .= ($offset >= 0) ? '+' : '';
		$offsetString = $offsetString . ( $offset/(60 * 60) );

		$return = $tz . ' (' . $offsetString . ')';
		return $return;
		}

	static function getTimezones(){
		$skipStarts = array('Brazil/', 'Canada/', 'Chile/', 'Etc/', 'Mexico/', 'US/');
		$return = array();
		$timezones = timezone_identifiers_list();
		reset( $timezones );
		foreach( $timezones as $tz ){
			if( strpos($tz, "/") === false )
				continue;
			$skipIt = false;
			reset( $skipStarts );
			foreach( $skipStarts as $skip ){
				if( substr($tz, 0, strlen($skip)) == $skip ){
					$skipIt = true;
					break;
					}
				}
			if( $skipIt )
				continue;

			$tzTitle = Hc_time::timezoneTitle( $tz );
			$return[] = array( $tz, $tzTitle );
			}
		return $return;
		}

	static function formatPeriodExtraShort( $ts, $limit = 'day' )
	{
		if( $limit == 'day' )
			$day = (int) ($ts/(24 * 60 * 60));
		else
			$day = 0;
		$hour = (int) ( ($ts - (24 * 60 * 60)*$day)/(60 * 60));
		$minute = (int) ( $ts - (24 * 60 * 60)*$day - (60 * 60)*$hour ) / 60;

		switch( $limit ){
			case 'day':
				$return = $day + ($hour/24) + ($minute/(24*60));
				$return = sprintf('%.2f', $return );
				$return = $return + 0;

/* translators: short duration format in days, for example 4 d */
				$return = sprintf( HCM::__('%s d'), $return );
				break;

			case 'hour':
				$return = $day * 24 + $hour + ($minute/60);
				$return = sprintf('%.2f', $return );
				$return = $return + 0;

/* translators: short duration format in hours, for example 2 hr */
				$return = sprintf( HCM::__('%s hr'), $return );
				break;

			case 'min':
				$return = $day * 24 *60 + $hour * 60 + $minute;
				$return = sprintf('%.2f', $return );
				$return = $return + 0;

/* translators: short duration format in minutes, for example 25 min */
				$return = sprintf( HCM::__('%s min'), $return );
				break;
		}

		return $return;
	}

	static function formatPeriodNumbers( $ts, $limit = 'day' )
	{
		if( $limit == 'day' )
			$day = (int) ($ts/(24 * 60 * 60));
		else
			$day = 0;
		$hour = (int) ( ($ts - (24 * 60 * 60)*$day)/(60 * 60));
		$minute = (int) ( $ts - (24 * 60 * 60)*$day - (60 * 60)*$hour ) / 60;

		$formatArray = array();
		if( $day > 0 ){
			$formatArray[] = $day;
		}
		$formatArray[] = sprintf( "%02d", $hour );
		$formatArray[] = sprintf( "%02d", $minute );

		$verbose = join( ':', $formatArray );
		return $verbose;
	}

	public function formatDuration( $ts2 )
	{
		$ts1 = $this->getTimestamp();
		$diff = ( $ts2 > $ts1 ) ? ($ts2 - $ts1) : ($ts1 - $ts2);
		$return = Hc_time::formatPeriod( $diff );
		return $return;
	}

	static function formatPeriod( $ts, $limitMeasure = '', $downLimitMeasure = '' ){
//		$conf =& ntsConf::getInstance();
//		$limitMeasure = $conf->get('limitTimeMeasure');
//		$limitMeasure = '';

		switch( $limitMeasure ){
			case 'minute':
				$week = 0;
				$day = 0;
				$hour = 0;
				$minute = (int) ( $ts ) / 60;
				break;
			case 'hour':
				$week = 0;
				$day = 0;
				$hour = (int) ( ($ts)/(60 * 60));
				$minute = (int) ( $ts - (60 * 60)*$hour ) / 60;
				break;
			case 'day':
				$week = 0;
				$day = (int) ($ts/(24 * 60 * 60));
				$hour = (int) ( ($ts - (24 * 60 * 60)*$day)/(60 * 60));
				$minute = (int) ( $ts - (24 * 60 * 60)*$day - (60 * 60)*$hour ) / 60;
				break;
			default:
				$week = (int) ($ts/(7* 24 * 60 * 60));
				$day = (int) ( ( $ts - (7 *24 * 60 * 60)*$week ) / (24 * 60 * 60) );
				$hour = (int) ( ( $ts - (7 *24 * 60 * 60)*$week - (24 * 60 * 60)*$day ) / (60 * 60) );
				$minute = (int) ( $ts - (7 *24 * 60 * 60)*$week - (24 * 60 * 60)*$day - (60 * 60)*$hour ) / 60;
				break;
		}

		switch( $downLimitMeasure ){
			case 'hour':
				$minute = 0;
				break;
			case 'day':
				$hour = 0;
				$minute = 0;
				break;
		}

		$formatArray = array();
		if( $week > 0 ){
			$formatArray[] = sprintf( HCM::_n('%d Week', '%d Weeks', $week), $week );
		}
		if( $day > 0 ){
			$formatArray[] = sprintf( HCM::_n('%d Day', '%d Days', $day), $day );
		}
		if( $hour > 0 ){
			$formatArray[] = sprintf( HCM::_n('%d Hour', '%d Hours', $hour), $hour );
		}
		if( $minute > 0 ){
			$formatArray[] = sprintf( HCM::_n('%d Minute', '%d Minutes', $minute), $minute );
		}

		$verbose = join( ' ', $formatArray );
		return $verbose;
	}

	static function formatPeriodShort( $ts, $limitMeasure = '' ){
//		$conf =& ntsConf::getInstance();
//		$limitMeasure = $conf->get('limitTimeMeasure');
//		$limitMeasure = '';

		switch( $limitMeasure ){
			case 'minute':
				$week = 0;
				$day = 0;
				$hour = 0;
				$minute = (int) ( $ts ) / 60;
				break;
			case 'hour':
				$week = 0;
				$day = 0;
				$hour = (int) ( ($ts)/(60 * 60));
				$minute = (int) ( $ts - (60 * 60)*$hour ) / 60;
				break;
			case 'day':
				$week = 0;
				$day = (int) ($ts/(24 * 60 * 60));
				$hour = (int) ( ($ts - (24 * 60 * 60)*$day)/(60 * 60));
				$minute = (int) ( $ts - (24 * 60 * 60)*$day - (60 * 60)*$hour ) / 60;
				break;
			default:
				$week = (int) ($ts/(7* 24 * 60 * 60));
				$day = (int) ( ( $ts - (7 *24 * 60 * 60)*$week ) / (24 * 60 * 60) );
				$hour = (int) ( ( $ts - (7 *24 * 60 * 60)*$week - (24 * 60 * 60)*$day ) / (60 * 60) );
				$minute = (int) ( $ts - (7 *24 * 60 * 60)*$week - (24 * 60 * 60)*$day - (60 * 60)*$hour ) / 60;
				break;
		}

		$formatArray = array();
		if( $week > 0 ){
/* translators: short duration format in weeks, for example 4 w */
			$formatArray[] = sprintf( HCM::__('%d w'), $week );
		}
		if( $day > 0 ){
/* translators: short duration format in days, for example 4 d */
			$formatArray[] = sprintf( HCM::__('%d d'), $day );
		}
		if( $hour > 0 ){
/* translators: short duration format in hours, for example 2 hr */
			$formatArray[] = sprintf( HCM::__('%d hr'), $hour );
		}
		if( $minute > 0 ){
/* translators: short duration format in minutes, for example 25 min */
			$formatArray[] = sprintf( HCM::__('%d min'), $minute );
		}

		$verbose = join( ' ', $formatArray );
		return $verbose;
		}

	function getWeekOfMonth()
	{
		$return = 0;
		$keepDate = $this->formatDate_Db();
		$thisMonth = $this->getMonth();
		$testMonth = $thisMonth;
		while( $testMonth == $thisMonth )
		{
			$return++;
			$this->modify( '-1 week' );
			$testMonth = $this->getMonth();
		}
		$this->setDateDb( $keepDate );
		return $return;
	}

	function formatWeekOfMonth()
	{
		$week = $this->getWeekOfMonth();
		$text = array(
			1	=> HCM::__('1st'),
			2	=> HCM::__('2nd'),
			3	=> HCM::__('3rd'),
			4	=> HCM::__('4th'),
			5	=> HCM::__('5th'),
			);
		return $text[$week];
	}

	function getWeekOfMonthFromEnd()
	{
		$return = 0;
		$keepDate = $this->formatDate_Db();
		$thisMonth = $this->getMonth();
		$testMonth = $thisMonth;
		while( $testMonth == $thisMonth )
		{
			$return++;
			$this->modify( '+1 week' );
			$testMonth = $this->getMonth();
		}
		$this->setDateDb( $keepDate );
		return $return;
	}

	function formatWeekOfMonthFromEnd()
	{
		$week = $this->getWeekOfMonthFromEnd();
		$text = array(
			1	=> HCM::__('1st'),
			2	=> HCM::__('2nd'),
			3	=> HCM::__('3rd'),
			4	=> HCM::__('4th'),
			5	=> HCM::__('5th'),
			);
		$add = HCM::__('From End');
		return $text[$week] . ' ' . $add;
	}

	function getMonthMatrix( $endDate = '', $exact = FALSE ){
		$matrix = array();
		$currentMonthDay = 0;
		$startDate = $this->formatDate_Db();
// echo "END DATE = $endDate<br>";

		if( $endDate )
			$this->setDateDb( $endDate );
		else
			$this->setEndMonth();

		if( ! $exact ){
			$this->setEndWeek();
			$endDate = $this->formatDate_Db();
		}
// echo "END DATE = $endDate<br>";

		if( ! $exact ){
			$this->setDateDb( $startDate );
			$this->setStartWeek();
			$rexDate = $this->formatDate_Db();
		}
		else {
			$rexDate = $startDate;
		}

		$this->setDateDb( $startDate );
		$this->setStartWeek();
		$rexDate = $this->formatDate_Db();

// echo "START DATE = $startDate, END DATE = $endDate, REX DATE = $rexDate<br>";

		$this->setDateDb( $rexDate );
		while( $rexDate <= $endDate ){
			$week = array();
			for( $weekDay = 0; $weekDay <= 6; $weekDay++ ){
				$thisWeekday = $this->getWeekday();
				$setDate = $rexDate;

				if( $exact ){
					if( 
						( $rexDate > $endDate ) OR
						( $rexDate < $startDate )
						){
						$setDate = NULL;
						}
				}

				// $week[ $thisWeekday ] = $setDate;
				$week[] = $setDate;
				$this->modify('+1 day');
				$rexDate = $this->formatDate_Db();

				// if( $exact && ($rexDate >= $endDate) ){
					// break;
				// }
			}
			$matrix[] = $week;
		}
		return $matrix;
	}

	public function getWeekdays()
	{
		$return = array();

		$wkds = array( 0, 1, 2, 3, 4, 5, 6 );
		$wkds = $this->sortWeekdays( $wkds );

		reset( $wkds );
		foreach( $wkds as $wkd ){
			// $return[ $wkd ] = $this->formatWeekdayShort($wkd);
			$return[] = $this->formatWeekdayShort($wkd);
		}
		return $return;
	}

	public function helper_get_dates( $date_from, $date_to )
	{
		$return_dates = array();
		$return_dates_details = array();

		$this->setDateDb( $date_from );

		$rex_date = $date_from;
		while( $rex_date <= $date_to ){
			$day_start = $this->getStartDay();
			$weekday = $this->getWeekday();
			$weekday_formatted = $this->formatWeekdayShort();
			$day_formatted = $this->formatDate();
			$day_short = $this->getDay();

			$date_key = (int) $rex_date;
			$return_dates[] = $date_key;

			$return_dates_details[ $date_key ] = array(
				// 'date'		=> $date_key,
				'formatted'	=> $day_formatted,
				'start'		=> (int) $day_start,
				'short'		=> (int) $day_short,
				'weekday'	=> (int) $weekday,
				'weekday_formatted'	=> $weekday_formatted,
				);

			$this->modify('+1 day');
			$rex_date = $this->formatDateDb();
		}

		$return = array( $return_dates, $return_dates_details );
		return $return;
	}
}

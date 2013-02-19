<?php
    function createYears($start_year, $end_year, $id='year_select', $selected=null)
    {
        $years = range($start_year, $end_year);
        $selected = is_null($selected) ? date('Y') : $selected;
				return createDropdown($years, $id, $selected);
    }

    function createMonths($id='month_select', $selected=null)
    {
        $months = array(
                1=>'January', 2=>'February', 3=>'March', 4=>'April', 5=>'May', 6=>'June',
                7=>'July', 8=>'August', 9=>'September', 10=>'October', 11=>'November', 12=>'December');
				$selected = is_null($selected) ? date('m') : $selected;
				return createDropdown($months, $id, $selected);
    }


    function createDays($id='day_select', $selected=null)
    {
        $days = range(1, 31);
        $selected = is_null($selected) ? date('d') : $selected;
				return createDropdown($days, $id, $selected);
    }

    function createHours($id='hours_select', $selected=null)
    {
        $hours = range(1, 12);
        $selected = is_null($selected) ? date('h') : $selected;
				return createDropdown($hours, $id, $selected);
    }

    function createMinutes($id='minute_select', $selected=null)
    {
        $minutes = array('00', 15, 30, 45);
			  $selected = in_array($selected, $minutes) ? $selected : 0;
				return createDropdown($minutes, $id, $selected);
    }

    function createAmPm($id='select_ampm', $selected=null)
    {
        $ampm = array('AM', 'PM');
        $selected = is_null($selected) ? date('A') : strtoupper($selected);
				return createDropdown($ampm, $id, $selected);
    }

    function createDropdown($array, $id, $default=null) {
			$result = "<select name=\"$id\" id=\"$id\">\n";
			if (isAssoc($array)) {
        foreach ( $array as $key => $value ) {
					$result .= "<option value=\"$key\"".
						($key == $default ? ' selected="true"' : '').
						">$value</option>\n";
				}
			}
			else {
				foreach ( $array as $value ) {
							$result .= "<option value=\"$value\"".($value == $default ? ' selected="true"' : '').">$value</option>\n";
				}
			}
      $result .= '</select>';
			return $result;
    }
	# vim:filetype=html:ts=2:sw=2
?>

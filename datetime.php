<?php
    /**
    *
    * @Create dropdown of years
    *
    * @param int $start_year
    *
    * @param int $end_year
    *
    * @param string $id The name and id of the select object
    *
    * @param int $selected
    *
    * @return string
    *
    */
    function createYears($start_year, $end_year, $id='year_select', $selected=null)
    {

        /*** the current year ***/
        $selected = is_null($selected) ? date('Y') : $selected;

        /*** range of years ***/
        $r = range($start_year, $end_year);

        /*** create the select ***/
        $select = '<select name="'.$id.'" id="'.$id.'">';
        foreach( $r as $year )
        {
            $select .= "<option value=\"$year\"";
            $select .= ($year==$selected) ? ' selected="selected"' : '';
            $select .= ">$year</option>\n";
        }
        $select .= '</select>';
        return $select;
    }

    /*
    *
    * @Create dropdown list of months
    *
    * @param string $id The name and id of the select object
    *
    * @param int $selected
    *
    * @return string
    *
    */
    function createMonths($id='month_select', $selected=null)
    {
        /*** array of months ***/
        $months = array(
                1=>'January',
                2=>'February',
                3=>'March',
                4=>'April',
                5=>'May',
                6=>'June',
                7=>'July',
                8=>'August',
                9=>'September',
                10=>'October',
                11=>'November',
                12=>'December');

        /*** current month ***/
        $selected = is_null($selected) ? date('m') : $selected;

        $select = '<select name="'.$id.'" id="'.$id.'">'."\n";
        foreach($months as $key=>$mon)
        {
            $select .= "<option value=\"$key\"";
            $select .= ($key==$selected) ? ' selected="selected"' : '';
            $select .= ">$mon</option>\n";
        }
        $select .= '</select>';
        return $select;
    }


    /**
    *
    * @Create dropdown list of days
    *
    * @param string $id The name and id of the select object
    *
    * @param int $selected
    *
    * @return string
    *
    */
    function createDays($id='day_select', $selected=null)
    {
        /*** range of days ***/
        $r = range(1, 31);

        /*** current day ***/
        $selected = is_null($selected) ? date('d') : $selected;

        $select = "<select name=\"$id\" id=\"$id\">\n";
        foreach ($r as $day)
        {
            $select .= "<option value=\"$day\"";
            $select .= ($day==$selected) ? ' selected="selected"' : '';
            $select .= ">$day</option>\n";
        }
        $select .= '</select>';
        return $select;
    }


    /**
    *
    * @create dropdown list of hours
    *
    * @param string $id The name and id of the select object
    *
    * @param int $selected
    *
    * @return string
    *
    */
    function createHours($id='hours_select', $selected=null)
    {
        /*** range of hours ***/
        $r = range(1, 12);

        /*** current hour ***/
        $selected = is_null($selected) ? date('h') : $selected;

        $select = "<select name=\"$id\" id=\"$id\">\n";
        foreach ($r as $hour)
        {
            $select .= "<option value=\"$hour\"";
            $select .= ($hour==$selected) ? ' selected="selected"' : '';
            $select .= ">$hour</option>\n";
        }
        $select .= '</select>';
        return $select;
    }

    /**
    *
    * @create dropdown list of minutes
    *
    * @param string $id The name and id of the select object
    *
    * @param int $selected
    *
    * @return string
    *
    */
    function createMinutes($id='minute_select', $selected=null)
    {
        /*** array of mins ***/
        $minutes = array(0, 15, 30, 45);
#        $minutes = array(0, 10, 20, 30, 40, 50);

    $selected = in_array($selected, $minutes) ? $selected : 0;

        $select = "<select name=\"$id\" id=\"$id\">\n";
        foreach($minutes as $min)
        {
            $select .= "<option value=\"$min\"";
            $select .= ($min==$selected) ? ' selected="selected"' : '';
            $select .= ">".str_pad($min, 2, '0')."</option>\n";
        }
        $select .= '</select>';
        return $select;
    }

    /**
    *
    * @create a dropdown list of AM or PM
    *
    * @param string $id The name and id of the select object
    *
    * @param string $selected
    *
    * @return string
    *
    */
    function createAmPm($id='select_ampm', $selected=null)
    {
        $r = array('AM', 'PM');

    /*** set the select minute ***/
        $selected = is_null($selected) ? date('A') : strtoupper($selected);

        $select = "<select name=\"$id\" id=\"$id\">\n";
        foreach($r as $ampm)
        {
            $select .= "<option value=\"$ampm\"";
            $select .= ($ampm==$selected) ? ' selected="selected"' : '';
            $select .= ">$ampm</option>\n";
        }
        $select .= '</select>';
        return $select;
    }
?>

<?php

if (!function_exists('escape_like')) {
    /**
     * @param $string
     * @return mixed
     */
    function escape_like($string)
    {
        $search = array('%', '_');
        $replace = array('\%', '\_');
        return str_replace($search, $replace, $string);
    }

}

if (!function_exists('convertMinutesToHoursMins')) {

    function convertMinutesToHoursMins($time)
    {
        if ($time < 1) {
            $time_result['hours'] = 0;
            $time_result['minutes'] = 0;
        }

        $hours = (int)floor($time / 60);
        $minutes = ($time % 60);

        $time_result['hours'] = $hours;
        $time_result['minutes'] = $minutes;

        return $time_result;
    }
}


if (!function_exists('get_month_locale')) {

    function get_month_locale($index)
    {
        $arr = ['Ιαν', 'Φεβ', 'Μαρ', 'Απρ', 'Μαϊ', 'Ιουν', 'Ιουλ', 'Αυγ', 'Σεπ', 'Οκτ', 'Νοε', 'Δεκ'];

        return $arr[$index - 1];
    }
}


if (!function_exists('format_text_for_language_file')) {

    function format_text_for_language_file($key, $value, $spaces)
    {

        if (strpos($value, "'") !== false) {
            $value = str_replace("\'", "'", $value);
            $value = str_replace("'", "\'", $value);
        }

        $results = str_repeat(" ", $spaces) . "'" . $key . "'=>";
        $results .= "'" . $value . "'," . PHP_EOL;

        return $results;
    }
}


if (!function_exists('replace_body_parameters')) {
    function replace_body_parameters($string, $array)
    {
        foreach ($array as $key => $value) {
            if (strpos($string, ':' . $key) !== false) {
                $string = str_replace(':' . $key, $value, $string);
            }
        }
        return $string;
    }
}







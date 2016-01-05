<?php

function is_comma_separated_string($string){
    return preg_match("/^[0-9,]+$/i", $string);
}
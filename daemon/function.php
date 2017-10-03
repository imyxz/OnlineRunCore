<?php
/**
 * User: imyxz
 * Date: 2017-09-30
 * Time: 17:31
 * Github: https://github.com/imyxz/
 */
function classToJson($obj)
{
    return json_encode(get_object_vars($obj));
}
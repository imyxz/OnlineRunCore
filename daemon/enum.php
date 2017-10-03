<?php
abstract class enum{
    private $int2str=array();
    private $str2int=array();
    public function __construct()
    {
        $vars=get_class_vars(get_class($this));

        $vars=array_diff_key($vars,get_class_vars("enum"));
        foreach($vars as $key=>&$one)
        {
            $this->int2str[$one]=$key;

        }
        $this->str2int=$vars;
    }
    public function getName($int)
    {
        if(!array_key_exists ($int,$this->int2str))
            return "TESTING";
        return $this->int2str[$int];
    }
    public function getInt($str)
    {
        if(!array_key_exists ($str,$this->str2int))
            return 15;
        return $this->str2int[$str];
    }
}
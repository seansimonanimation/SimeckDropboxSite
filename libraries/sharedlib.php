<?php
function SummonActivityButton($isActive){
    if($isActive){
        return '<span style="color:green; font-weight:bold">✅</span>';
    } else {
        return '<span style="color:red; font-weight:bold">❌</span>';
    }
}
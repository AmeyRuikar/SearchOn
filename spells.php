<?php
ini_set("memory_limit", "-1");
include 'SpellCorrector.php';



echo SpellCorrector::correct('calfrnia');
//it will output *october*
?>

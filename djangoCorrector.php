<?php

  function correction($word){

    //if(isset($_GET['word'])){

        $urlJSON = "http://127.0.0.1:8000/polls/?word=".$word;
        $JSON = file_get_contents($urlJSON);
        return $JSON;

    //}



  }




 ?>

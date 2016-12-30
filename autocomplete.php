<?php


    if(isset($_GET['input'])){

        $results = array();

        $queryObjs = explode(" ", $_GET['input']);

        $encodedQuery = strtolower($queryObjs[count($queryObjs)-1]);


        $appendQuery = "";
        for($i = 0; $i< count($queryObjs)-1; $i++){
            $appendQuery = $appendQuery." ".strtolower($queryObjs[$i]);
        }
        $appendQuery = trim($appendQuery);

        $urlJSON = "http://localhost:8983/solr/CNNUSA/suggest?indent=on&q=".$encodedQuery."&wt=json";


        $JSON = file_get_contents($urlJSON);
        $data = json_decode($JSON, true);
        //echo $data;

        $suggestions = $data['suggest']['suggest'];
        //echo $suggestions;

        foreach ($suggestions as  $value) {
          # code...
          foreach($value['suggestions'] as $obj) {

              //echo $obj;
              $val = "";
              $sym = "";
              $val = $obj['term'];
              $sym = $obj['term'];

              if(!strcmp($val, $encodedQuery)){
                continue;
              }

              if(strpos($val, ".") || strpos($val, "_") || strpos($val, ":")){
                continue;
              }

              //$val = $stockEntry['Symbol']." - ".$stockEntry['Name']." ( ".$stockEntry['Exchange']." )";
              //$sym = $stockEntry['Symbol'];

              array_push($results, array('label'=> $appendQuery." ".$val, 'value'=>$appendQuery." ".$sym));

          }


        }



      echo json_encode($results);
    }

?>

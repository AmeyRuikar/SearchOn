<?php
ini_set("memory_limit", "-1");
include 'SpellCorrector.php';
include 'djangoCorrector.php';

// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');

$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$searchAlgo = isset($_REQUEST['searchAlgo']) ? $_REQUEST['searchAlgo'] : false;
$correctSpellings = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : 0;
$results = false;

if ($query)
{
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('Apache/Solr/Service.php');

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/CNNUSA', false, $searchAlgo);

  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }

  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  try
  {
    $queryElements = explode(" ", $query);
    $newQuery = "";
    $flag = 0;

    foreach ($queryElements as $value) {
      # code...
      #echo "<p>".$value."</p>";
      #add step here for phpspell
      #$pspell_link = pspell_new("en");

      /*

      if (pspell_check($pspell_link, "wild")) {
          echo "This is a valid spelling";
      } else {
          echo "Sorry, wrong spelling";
      }
      */

      #$retVal2 = SpellCorrector::correct($value);
      $retVal = correction($value);



      if(strcmp(strtolower($retVal), strtolower($value)) == 0){
          #echo "<p>".$value."->".$retVal;
          $newQuery = $newQuery." ".$value;
      }
      else{
        #echo "<p>".$value."->".$retVal;
        #echo "here";
        $newQuery = $newQuery." ".$retVal;
        $flag = 1;
      }

        /*
      if((strcmp(strtolower($retVal), strtolower($value))) != 0 && (strcmp(strtolower($retVal2), strtolower($value)) == 0)){
        $newQuery = $newQuery." ".$value;
      }
      elseif ((strcmp(strtolower($retVal), strtolower($value))) != 0 && (strcmp(strtolower($retVal2), strtolower($value)) != 0)){
        $newQuery = $newQuery." ".$retVal;
        $flag = 1;
      }
      elseif((strcmp(strtolower($retVal), strtolower($value))) == 0 && (strcmp(strtolower($retVal2), strtolower($value)) != 0)){
        $newQuery = $newQuery." ".$value;
      }
      else{
        $newQuery = $newQuery." ".$value;
      }
      */

    }


    #echo "<p>".$newQuery."</p>";
    if($correctSpellings != 1)
    {
      $results = $solr->search(trim(strtolower($newQuery)), 0, $limit);
    }
    else{
      $results = $solr->search(trim(strtolower($query)), 0, $limit);
      $flag = 0;
    }

  }
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}

?>
<html>
  <head>
    <title>PHP Solr Client Example</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
    <link href=" http://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css" rel="stylesheet">





  </head>
  <body>


    <form  accept-charset="utf-8" method="get">
      <input id="searchAlgo" type="radio" name="searchAlgo" value="DefaultSolr"
      <?php if ($query){
            if(!strcmp($searchAlgo, "DefaultSolr")){
              echo "checked";
            }
       }

      ?> /> Default Solr
      <input id="searchAlgo" type="radio" name="searchAlgo" value="PageRank" <?php
      if ($query){
            if(!strcmp($searchAlgo, "PageRank")){
              echo "checked";
            }
       }

       ?> /> Page Rank
      <br/>
      <label for="q">Search:</label>
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
      <input type="submit"/>
    </form>



<?php

if($query){

  if($flag == 1){
    echo "<p><b>Showing results for: <span STYLE='color:blue; font-size: 14pt'><i><u>".trim($newQuery)."</u></i></span></b></p>";
    $encodedQ = str_replace(" ", "+", $query);
    echo "<p><b>Search instead for: <i><u><a href='micro.php?mode=1&q=".$encodedQ."'>".trim($query)."</a></u></i></b></p>";
  }

}


// display results
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
<?php

  // time to create the Map
  //$csv = array_map('str_getcsv', file('/Users/ameyruikar/Downloads/USATodayCNNData/map.csv'));

   $csvFile = file('/Users/ameyruikar/Downloads/USATodayCNNData/map2.csv');
   $csv = array();
   foreach ($csvFile as $line) {
       $key = explode("," ,$line)[0];
       $value = explode("," ,$line)[1];
       $csv[$key] = $value;
   }


  // iterate result documents
  $resultsList = array();
  foreach ($results->response->docs as $doc)
  {
?>
      <li>
        <table style="max-width: 850px; border: 0px solid black; text-align: left">
<?php
    // iterate document fields / values

    $interestingFields = array("id", "og_site_name", "title", "content_encoding", "content_type_hint", "pubdate");

    $docFields = array();
    $snippetArray = array();
    foreach ($doc as $field => $value)
    {

      $docFields[$field] = $value;

      if(strcmp($field, "id") == 0){

        $doc = new DOMDocument();
        $file = $value;
        $doc->loadHTMLFile($file);


        //$xpath = new DOMXPath($doc);
        //$textnodes = $xpath->query('//text()');
        $termsToFind = explode(" ", $newQuery);

        $countSnippets = 2;


        foreach($doc->getElementsByTagName('p') as $paragraph) {
            // do something with $paragraph->textContent

            foreach ($termsToFind as $term) {
              # code...
              if(stripos($paragraph->textContent, $term) !== false){

                ?>
                <?php
                array_push($snippetArray, $paragraph->textContent);

                //echo "<p>".$onetag->nodeValue."</p>";
                $countSnippets = $countSnippets - 1;
              }
              if($countSnippets == 0){
                break;
              }

            }

            if($countSnippets == 0){
              break;
            }

        }







        /*
        $countSnippets = 3;

        $termsToFind = explode(" ", $newQuery);
        foreach ($ps as $onetag) {
          foreach ($termsToFind as $term) {
            # code...
            if(strpos($onetag->nodeValue, $term)){


              //echo "<p>".$onetag->nodeValue."</p>";
              $countSnippets = $countSnippets - 1;
            }
          }

          if($countSnippets == 0){
            break;
          }
       }
       */
        $hashKey = explode("/", $value)[6];
        $docFields["actualLink"] = $csv[$hashKey];


        /*
        <tr>
          <th><?php echo "link: " ?></th>
          <td><a target='_blank' href='<?php echo $csv[$hashKey]; ?>'><?php echo $csv[$hashKey]; ?></a></td>
        </tr>
          this was bellow the ?> for link
        */
?>

<?php

        array_push($resultsList,  $hashKey);
      }

      if(strcmp($field, "description") == 0){

          $termsToFind = explode(" ", $newQuery);

          foreach ($termsToFind as $term) {
            # code...
            #echo stripos($value, $term);
            if(stripos($value, $term) !== false){


              array_push($snippetArray, $value);

              //echo "<p>".$onetag->nodeValue."</p>";

            }

          }

      }

      if(0){
?>

          <tr>
            <th><?php echo htmlspecialchars($field, ENT_NOQUOTES, 'utf-8'); ?></th>
            <td><?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8'); ?></td>
          </tr>
<?php
      }
    }


    if(count($snippetArray) !=0 ){
      $docFields["snippetSet"] = array_unique($snippetArray);
    }
    else{
      $newa = array();
      array_push($newa, $docFields["title"]);
      $docFields["snippetSet"] = $newa;
    }

?>
      <tr>

        <td><a target='_blank' href='<?php echo $docFields["actualLink"]; ?>'><?php echo "<span STYLE='font-size: 18pt'>".$docFields["title"]."</span>"; ?></a></td>
      </tr>


        <tr>

          <td><?php echo "<span STYLE='color:green; font-size: 12pt'>".$docFields["actualLink"]."</span>"; ?></td>
        </tr>


        <tr>

          <td><?php echo "<span STYLE='color:grey; font-size: 14pt'>"."...".implode("...", $docFields["snippetSet"])."..."."</span>"; ?></td>
        </tr>


        </table>
      </li>
<?php
  }

  foreach ($resultsList as $value) {
    # code...
    #echo "<p>".$value."</p>";
  }

?>
    </ol>
<?php
}
?>

  <script>

  $('#q').autocomplete({

      source: function(request, response){

           //$("#sQuote").prop("type","button");
           //$('#error_no').html("");
          //entryFrom = 0;
           //console.log("0-0");
          $.ajax({
              url: "autocomplete.php",
              data: {
                  input: request.term
              },
              success: function(data){
                  console.log(data);
                  var d = jQuery.parseJSON(data);

                  response(d);
              }
          });
      },
      minLength: 1,
      select: function(event, uri){
          //entryFrom = 2;
      },
      open: function(){
          $( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
      },
      close: function(){
          $( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
      }

  });

  </script>
  </body>
</html>

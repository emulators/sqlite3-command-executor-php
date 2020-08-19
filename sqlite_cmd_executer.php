
<!DOCTYPE HTML>  
<html>
<head>
<style>
tr:first-child > td {
  border-bottom: 1px #000000 solid;
}
</style>
</head>
<body>  

<?php
// define variables and set to empty values
$nameErr = $emailErr = $genderErr = $websiteErr = "";
$name = $email = $gender = $comment = $website = "";
$real_command = "";
$errorMessage = "";
$db_file_name = "bendo_duty.db";
$errMsg2 = "";

class SQLiteDB extends SQLite3
{
  function __construct($db_file_path)
  {
    global $db_file_name;
    $this->open($db_file_path);
  }
}


// Process SQLite command
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Get database file
  if( $_POST["hidd_db_file_name"] ){
    $db_file_name = $_POST["hidd_db_file_name"];
  }

  if ( empty($_POST["comment"]) || empty($db_file_name) ) {
    $comment = "";
  } else {
    $real_command = $_POST["comment"];
    $comment      = test_input($_POST["comment"]);
    $db           = new SQLiteDB($db_file_name);
    
    // Process SQL command here
    if (!$db){
      $errorMessage = $db->lastErrorMsg();
    }
    else{
      // No error on open DB, next check command...
      $first_cmd = mb_substr($real_command, 0, 9);
      if (  /* isset($_POST['btn_exec']) && */
     /*  (stristr($first_cmd, "update") != FALSE ||
          stristr($first_cmd, "insert") != FALSE ||
          stristr($first_cmd, "delete") != FALSE) */
         !stristr($first_cmd, "select") 
      )
      {
        $ret = $db->exec($real_command);
        if (!$ret){ $errorMessage = $db->lastErrorMsg(); }
        if (!$errorMessage){
          $errorMessage = "No error during SQLcmd execution, returned value=$ret";
        }
      }
      else if ( /*isset($_POST['btn_query']) &&*/ stristr($first_cmd, "select") ){
        $ret = $db->query($real_command);
        if ($ret == FALSE){ $errorMessage = $db->lastErrorMsg(); }
        if (!$errorMessage){
        }
      }
      else{
        $errorMessage = 'must press "query" when select, "exec" when others';
      }
    }
  }
  $comment = "";
}

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
?>

<h2>PHP SQLite Command Executor</h2>
<p>&nbsp;</p>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  
  <input type=hidden name="hidd_db_file_name" id="hidd_db_fn" value="<?php echo $db_file_name; ?>" />
  <span>SQLite DB file: 
  <input type=text value="<?php echo $db_file_name; ?>" onblur="setHiddenFileName()" id="txt_db_file_name" />
  <input type=submit value="Load database" />
  
  </span>
  <p>&nbsp;</p>
  SQL Command:<br>
  <textarea name="comment" rows="7" cols="60"><?php echo $comment;?></textarea>

  <br><br>
  <input type="submit" value="SQL Exec" id="btn_exec" name='btn_exec' onclick="queryButtonPressed('btn_exec')">
  &nbsp;
  <input type="submit" value="SQL Query" id="btn_query" name="btn_query" onclick="queryButtonPressed('btn_query')" />
</form>
<script type='text/javascript'>
function queryButtonPressed(btn_id){
  var thisBtn      = document.getElementById(btn_id);
  thisBtn.disabled = true;
  thisBtn.value    = "Processing";
} // queryButtonPressed()


function setHiddenFileName(){
  var file_name_txt_elem = document.getElementById('txt_db_file_name');
  var file_name_hid_elem = document.getElementById('hidd_db_fn');
  file_name_hid_elem.value = file_name_txt_elem.value;
} // setHiddenFileName()

</script>
<?php

function print_col_names_as_table_row($col_name_array){
  echo "<tr>";
  foreach($col_name_array as $col_name){
    echo "<td>" . $col_name . "</td>";
  }
  echo "</tr>";
}

function print_data_from_select_as_table_row($row_data_array){
  echo "<tr>";
  foreach( $row_data_array as $val ){
    echo "<td>" . htmlentities($val) . "</td>";
  }
  echo "</tr>";
}


  echo "<h2>Result:</h2>";
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  /* SQL command you input */
  echo "<br>--------<br>";
  echo "Your input: <br>";
  echo $real_command;
  echo "<br>--------<br>";

  /* SQL execution response from system */
  if ($errorMessage){
    echo "System response:<br>";
    echo $errorMessage;
    echo "<br>--------<br>";
  }

  /* SQL result */
  if ( !$errorMessage ){
    
    // row count:
    $i = 0;

    echo '<br><table cellspacing="0" cellpadding="2">';
    while($row = $ret->fetchArray(SQLITE3_ASSOC) ){
      
      // show column names
      if ($i == 0){
        $col_name_ary = array_keys($row);
        print_col_names_as_table_row($col_name_ary);
      }

      // show row data
      print_data_from_select_as_table_row($row);
      $i = $i + 1;
    }
    echo "</table><br>";
    echo "number of rows: $i";
  }
  if($db){ 
    $db->close();
  }
}
?>

</body>
</html>


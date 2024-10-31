<?php
  # Connect to the sample database
  $conn = sasql_connect( "HOST=10.55.99.180:2640;DBN=infinity03;UID=gabellini;PWD=rocket2022;ServerName=infinity_gabellini_corsi" );
  if( ! $conn ) {
      echo "<div>Connection failed</div>";
  } else {
      echo "<div>Connected successfully</div>";

      $result=sasql_query($conn,'SELECT * FROM dba.tdo_cli');

      if ($result) {
        while($row=sasql_fetch_assoc($result)) {
          echo '<div>'.json_encode($row).'</div>';
        }
      }

      sasql_close( $conn );
  }

?>
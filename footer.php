<?php 
       		 		echo "</div>"; // kraj main-content
       		 	echo "</div>";  // kraj wrapper diva
       		 echo "</div>";  // kraj main diva
        echo "<div class='footer'>&copy; copyright WEAR 2015</div>";
    echo "<body>";  // kraj body
echo "</html>"; // kraj html

/* Zatvaramo konekciju na bazu */
global $global_database;
// Uništava se thread korisnika da ne ostane nepotrebno u memoriji, moguće je i bez ovoga jer će MySQL DBMS nakon nekog vremena(timeout) automatski odspojiti inaktivne korisnike
$thread_id = $global_database->thread_id;
$global_database -> kill($thread_id);
// Zatvaranje konekcije
$global_database -> close();
/* footer.php */
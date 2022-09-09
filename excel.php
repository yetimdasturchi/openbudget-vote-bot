<?php

include 'functions.php';

header("Content-Type: application/xls");    
header("Content-Disposition: attachment; filename=ovozlar_".date('Y_m_d_H_i_s').".xls");  
header("Pragma: no-cache"); 
header("Expires: 0");



echo '<table border="1">';
echo '<tr><th>Telefon raqam</th><th>Vaqt</th></tr>';
$votes = get_votes();
foreach ($votes as $vote) {
    echo "<tr><td>".format_phone($vote['phone'])."</td><td>".date('Y-m-d | H:i:s', $vote['time'])."</td></tr>";
}
echo '</table>';

exit();
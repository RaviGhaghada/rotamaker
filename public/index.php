<?php
     require_once('../private/initialize.php')
?>
<?php
    echo "hello there!";

    $rota = new RotaAlgorithm("2019-10-07");

    $rota->generateRota();
?>
<?php
    $c_sess = array(
        "authenticated",
        "id"
    );

    foreach ($c_sess as $key => $val) {
        define($val, $val);
    }
?>

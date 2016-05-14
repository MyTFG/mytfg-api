<?php
function eval_bool($val, $return_null = false){
    $boolval = (is_string($val) ?
        filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : (bool) $val);
    return ($boolval === null && !$return_null ? false : $boolval);
}

function permission_merge($array1, $array2) {
    global $res;

    $result = $array1;
    foreach ($array2 as $key => $perm) {
        if (isset($result[$key])) {
            $existing_perm = $result[$key];
            if ($existing_perm->isDerived()) {
                $result[$key] = $perm;
            }
            # ELSE: Keep existing permission
        } else {
            $result[$key] = $perm;
        }
    }
    return $result;
}

?>

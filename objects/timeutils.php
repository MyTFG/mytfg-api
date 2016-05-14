<?php
namespace Mytfg\Objects;

class TimeUtils {
    public static function microSecondsToString($ms) {
        return number_format($ms, 2, ",", "") . "μs";
    }

    public static function milliSecondsToString($ms) {
        if ($ms < 1) {
            return TimeUtils::microSecondsToString(($ms * 1000));
        } else {
            return number_format($ms, 2, ",", "") . "ms";
        }
    }

    public static function secondsToString($s) {
        if ($s < 1) {
            return TimeUtils::milliSecondsToString(($s * 1000));
        }
        return number_format($s, 2, ",", "") . "s";
    }
}

?>

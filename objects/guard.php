<?php
namespace Mytfg\Objects;

class Guard {
    ##
    ## Checks whether current user has specific permissions.
    ##

    // Checks, if the user has the needed permissions and fails if not
    public static function need(...$perms) {
        global $currentuser;
        foreach ($perms as $perm) {
            if (!$currentuser->hasPerm($perm)) {
                global $res;
                $res->log("Missing permission: $perm");
                $res->send(403);
            }
        }
    }

    public static function needOne(...$perms) {
        global $currentuser;
        foreach ($perms as $perm) {
            if ($currentuser->hasPerm($perm)) {
                return true;
            }
        }
        // None of the permissions are present
        global $res;
        $res->log("Missing permission: $perm");
        $res->send(403);
    }

    // Returns true iff the current user has ALL of the given permissions
    public static function ifAll(...$perms) {
        global $currentuser;
        foreach ($perms as $perm) {
            if (!$currentuser->hasPerm($perm)) {
                return false;
            }
        }
        return true;
    }

    // Returns true iff the current user has at least one of the given permissions
    public static function ifOne(...$perms) {
        global $currentuser;
        foreach ($perms as $perm) {
            if ($currentuser->hasPerm($perm)) {
                return true;
            }
        }
        return false;
    }


    // Evaluates special permissions
    public static function special($obj, $perm) {
        $split = explode("/", $perm);
        switch ($split[0]) {
            default:
                return false;
            case "user":
                return Guard::specialUser($obj, $split);
        }
    }

    private static function specialUser($user, $perm) {
        if (sizeof($perm) < 3) {
            return false;
        }

        switch ($perm[1]) { // Indicator
            case "is":
                return $user->getUsername() == $perm[2];
            case "id":
                return $user->getId() == $perm[2];
            default:
                return false;
        }
    }

}
?>

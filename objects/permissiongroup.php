<?php
namespace Mytfg\Objects;

class PermissionGroup {

    public static function resolve($perm) {
        global $res;

        $perm_str = $perm->getPermission();
        $split    = explode("/", $perm_str);
        $function = implode("_", $split);
        $result   = array();
        if (method_exists(__CLASS__, $function)) {
            $list = PermissionGroup::$function();
            foreach ($list as $item) {
                $permission = Permission::createDerived($perm, $item);
                $result = permission_merge($permission->getDerived(), $result);
            }
        }
        $result[$perm->getPermission()] = $perm;
        return $result;
    }


    # Defined permission-groups
    private static function pg_admin() {
        return array(
            "pg/admin/user",
            "pg/admin/menu"
        );
    }

    private static function pg_teacher() {

    }

    private static function pg_pupil() {

    }

    private static function pg_admin_user() {
        return array(
            "user/create",
            "user/delete",
            "pg/admin/user/permission"
        );
    }

    private static function pg_admin_user_permission() {
        return array(
            "user/permission/grant",
            "user/permission/take"
        );
    }

    private static function pg_admin_menu() {
        return array(
            "menu/get",
            "menu/reset",
            "menu/set"
        );
    }
}

?>

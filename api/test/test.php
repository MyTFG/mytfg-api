<?php
namespace Mytfg\Api\Test;

class Test {
    public function exec() {
        $this->permission();
    }

    private function permission() {
        global $res;
        global $currentuser;

        $permissions = $currentuser->getPermissions();
        foreach ($permissions as $perm) {
            $a = array();
            $res->log($perm->toArray($a));
        }
        $res->send(200);
    }
}

?>

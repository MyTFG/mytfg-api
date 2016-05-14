<?php
namespace Mytfg\Api;

class Module {
    private $module;
    private $function;
    private $file;
    private $valid = false;

    public function __construct($module, $function) {
        global $res;
        if (empty($module) || empty($function)) {            
            # Invalid call
            $res->code(400, "Missing valid module and valid function");
        } else {
            if (is_dir(Module::getPath($module))) {
                $file = Module::getFuncPath($module, $function);
                if (file_exists($file)) {
                    $this->file = $file;
                    $this->valid = true;
                    $this->module = $module;
                    $this->function = $function;
                } else {                
                    # Invalid call
                    $res->code(404, "Function $function not available in module $module");
                }
            } else {      
                # Invalid call
                $res->code(501);      
            }
        }
    }
    
    public function exec() {
        if ($this->valid) {
            $class = "Mytfg\\Api\\" . $this->module . "\\" . $this->function;
            $module = new $class();
            $module->exec();
        } else {
            global $res;
            $res->log("Cannot execute module: No valid module or function");
        }
    }
    
    private static function getPath($module) {
        return __DIR__ . "/" . $module;
    }
    
    private static function getFuncPath($module, $function) {
        return Module::getPath($module) . "/" . $function . ".php";
    }
}

?>
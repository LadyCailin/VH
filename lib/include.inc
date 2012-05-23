<?php
    /**
     * This file includes all the VH files needed. To use VH, you simply need to include this one file. 
     */
    if(!defined("_VIEW_LIB_INCLUDED")){
        function inc($fileFolder){          
            if(is_dir($fileFolder)){                  
                $list = scandir($fileFolder);               
                foreach($list as $entry){
                    if($entry == "." || $entry == ".."){
                        continue;
                    }                
                    inc($fileFolder . "/" . $entry);
                }
            } else {
                include_once($fileFolder);
            }
        }
        define("_VIEW_LIB_INCLUDED", true);
        
        $includeDirs = array("factory", "view", "util", "controller");
        foreach($includeDirs as $dir){
            inc(dirname(__FILE__) . "/" . $dir);
        }
    }
?>

<?php
    if(!Filter::isPOST()) return false;
    $LANG   = $module->lang;
    $apply = $module->apply_import_tlds();

    if($apply)
        echo Utility::jencode(['status' => "successful",'message' => $LANG["success4"]]);
    else
        echo Utility::jencode(['status' => "error",'message' => $LANG["error8"]]);
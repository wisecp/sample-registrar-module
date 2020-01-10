<?php
    if(!defined("CORE_FOLDER")) die();
    $LANG   = $module->lang;
    $CONFIG = $module->config;
    Helper::Load("Money");
?>

<form action="<?php echo Controllers::$init->getData("links")["controller"]; ?>" method="post" id="ExampleRegistrarModuleSettings">
    <input type="hidden" name="operation" value="module_controller">
    <input type="hidden" name="module" value="ExampleRegistrarModule">
    <input type="hidden" name="controller" value="settings">

    <div class="formcon">
        <div class="yuzde30"><?php echo $LANG["fields"]["username"]; ?></div>
        <div class="yuzde70">
            <input type="text" name="username" value="<?php echo $CONFIG["settings"]["username"]; ?>">
            <span class="kinfo"><?php echo $LANG["desc"]["username"]; ?></span>
        </div>
    </div>

    <div class="formcon">
        <div class="yuzde30"><?php echo $LANG["fields"]["password"]; ?></div>
        <div class="yuzde70">
            <input type="password" name="password" value="<?php echo $CONFIG["settings"]["password"] ? "*****" : ""; ?>">
            <span class="kinfo"><?php echo $LANG["desc"]["password"]; ?></span>
        </div>
    </div>

    <div class="formcon">
        <div class="yuzde30"><?php echo $LANG["fields"]["WHiddenAmount"]; ?></div>
        <div class="yuzde70">
            <input type="text" name="whidden-amount" value="<?php echo Money::formatter($CONFIG["settings"]["whidden-amount"],$CONFIG["settings"]["whidden-currency"]); ?>" style="width: 100px;" onkeypress='return event.charCode==46  || event.charCode>= 48 &&event.charCode<= 57'>
            <select name="whidden-currency" style="width: 150px;">
                <?php
                    foreach(Money::getCurrencies($CONFIG["settings"]["whidden-currency"]) AS $currency){
                        ?>
                        <option<?php echo $currency["id"] == $CONFIG["settings"]["whidden-currency"] ? ' selected' : ''; ?> value="<?php echo $currency["id"]; ?>"><?php echo $currency["name"]." (".$currency["code"].")"; ?></option>
                        <?php
                    }
                ?>
            </select>
            <span class="kinfo"><?php echo $LANG["desc"]["WHiddenAmount"]; ?></span>
        </div>
    </div>

    <div class="formcon">
        <div class="yuzde30"><?php echo $LANG["fields"]["test-mode"]; ?></div>
        <div class="yuzde70">
            <input<?php echo $CONFIG["settings"]["test-mode"] ? ' checked' : ''; ?> type="checkbox" name="test-mode" value="1" id="ExampleRegistrarModule_test-mode" class="checkbox-custom">
            <label class="checkbox-custom-label" for="ExampleRegistrarModule_test-mode">
                <span class="kinfo"><?php echo $LANG["desc"]["test-mode"]; ?></span>
            </label>
        </div>
    </div>

    <div class="formcon">
        <div class="yuzde30"><?php echo $LANG["fields"]["adp"]; ?></div>
        <div class="yuzde70">
            <input<?php echo $CONFIG["settings"]["adp"] ? ' checked' : ''; ?> type="checkbox" name="adp" value="1" id="ExampleRegistrarModule_adp" class="checkbox-custom">
            <label class="checkbox-custom-label" for="ExampleRegistrarModule_adp">
                <span class="kinfo"><?php echo $LANG["desc"]["adp"]; ?></span>
            </label>
        </div>
    </div>

    <div class="formcon" id="cost_currency_wrap">
        <div class="yuzde30"><?php echo $LANG["fields"]["cost-currency"]; ?></div>
        <div class="yuzde70">
            <select name="cost-currency" style="width:200px;">
                <?php
                    foreach(Money::getCurrencies($CONFIG["settings"]["cost-currency"]) AS $currency){
                        ?>
                        <option<?php echo $currency["id"] == $CONFIG["settings"]["cost-currency"] ? ' selected' : ''; ?> value="<?php echo $currency["id"]; ?>"><?php echo $currency["name"]." (".$currency["code"].")"; ?></option>
                        <?php
                    }
                ?>
            </select>
        </div>
    </div>


    <div class="formcon">
        <div class="yuzde30"><?php echo __("admin/products/profit-rate-for-registrar-module"); ?></div>
        <div class="yuzde70">
            <input type="text" name="profit-rate" value="<?php echo Config::get("options/domain-profit-rate"); ?>" style="width: 50px;" onkeypress='return event.charCode==44 || event.charCode==46 || event.charCode>= 48 &&event.charCode<= 57'>
        </div>
    </div>


    <div class="clear"></div>
    <br>

    <div style="float:left;" class="guncellebtn yuzde30"><a id="ExampleRegistrarModule_testConnect" href="javascript:void(0);" class="lbtn"><i class="fa fa-plug" aria-hidden="true"></i> <?php echo $LANG["test-button"]; ?></a></div>

    <div style="float:right;" class="guncellebtn yuzde30"><a id="ExampleRegistrarModule_submit" href="javascript:void(0);" class="yesilbtn gonderbtn"><?php echo $LANG["save-button"]; ?></a></div>

    </form>
    <script type="text/javascript">
    $(document).ready(function(){
        $("#ExampleRegistrarModule_testConnect").click(function(){
            $("#ExampleRegistrarModuleSettings input[name=controller]").val("test_connection");
            MioAjaxElement($(this),{
                waiting_text:waiting_text,
                progress_text:progress_text,
                result:"ExampleRegistrarModule_handler",
            });
        });

        $("#ExampleRegistrarModule_submit").click(function(){
            $("#ExampleRegistrarModuleSettings input[name=controller]").val("settings");
            MioAjaxElement($(this),{
                waiting_text:waiting_text,
                progress_text:progress_text,
                result:"ExampleRegistrarModule_handler",
            });
        });
    });

    function ExampleRegistrarModule_handler(result){
        if(result != ''){
            var solve = getJson(result);
            if(solve !== false){
                if(solve.status == "error"){
                    if(solve.for != undefined && solve.for != ''){
                        $("#ExampleRegistrarModuleSettings "+solve.for).focus();
                        $("#ExampleRegistrarModuleSettings "+solve.for).attr("style","border-bottom:2px solid red; color:red;");
                        $("#ExampleRegistrarModuleSettings "+solve.for).change(function(){
                            $(this).removeAttr("style");
                        });
                    }
                    if(solve.message != undefined && solve.message != '')
                        alert_error(solve.message,{timer:5000});
                }else if(solve.status == "successful")
                    alert_success(solve.message,{timer:2500});
            }else
                console.log(result);
        }
    }
    </script>
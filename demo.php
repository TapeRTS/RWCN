<?php

require_once "RWCN.php";

$do = $_GET['do'] ?: $_POST['do'] ?: die("未知操作");

$rts = new RW_CN();
$rts->setUIA("NjE0L3kxYTMvN15fNyxhYSk3K2VlLDExLyswMixgLVljXTJeYl4zMGE3X103MWUsKGApLzNhMTMtKzg0NGJfXTUsLTBi");
if ($do == "sign") {
    echo($rts->sign());
}
if ($do == "pvp") {
    echo "当前战力：".$rts->getPVP_Strength("self");
    echo "TOP战力：[".$rts->getPVP_Strength("top")."]/ID:[".$rts->getPVPTop()."]";
    echo($rts->pvp_auto());
}
if ($do == "delAll") {
    echo($rts->reply_delAll(7449));
}
if ($do == "reply") {
    echo($rts->reply(7449,"自动回复、签到、拓展信息"));
}

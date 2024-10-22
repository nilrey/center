<?php
$subj = date("Y-m-d H:i:s");
$txt = "Test message Line1
Line 2
";
exec("python3 client_local_users.py \"{$subj}\" \"{$txt}\" ");

?>
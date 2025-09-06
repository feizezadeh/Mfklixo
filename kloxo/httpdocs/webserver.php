<?php

include_once "lib/html/displayinclude.php";

try {
    display_main();
} catch (Exception $e) {
    print($e->getMessage());
}

function display_main()
{
    global $gbl, $sgbl, $login, $ghtml;

    initProgram();

    if (!$login->isAdmin()) {
        throw new lxException("only_admin_can_access");
    }

    if ($ghtml->frm_action === 'update') {
        lxshell_return("sh", "/script/changedriver", "web", $ghtml->frm_web_driver);
        $ghtml->print_redirect_back("Web server updated successfully");
    }

    $driverapp = $gbl->getSyncClass(null, 'localhost', 'web');

    $ghtml->print_begin();
    $ghtml->print_head();
    $ghtml->print_body();
    $ghtml->print_header();
    $ghtml->print_navigation();
    $ghtml->print_content_begin();

    $ghtml->print_title("Web Server Configuration");

    $ghtml->print_begin_table();
    $ghtml->print_table_entry("Current Web Server", $driverapp);
    $ghtml->print_end_table();

    $serverweb = new serverweb(null, null, 'pserver-localhost');
    $vlist = $serverweb->updateform('webswitch', null);
    $ghtml->print_update_form($vlist, array('web_driver'));

    $ghtml->print_content_end();
    $ghtml->print_footer();
    $ghtml->print_end();
}

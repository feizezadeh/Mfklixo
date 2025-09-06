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
        $serverweb = new serverweb(null, null, 'pserver-localhost');
        $serverweb->dbactionUpdate($ghtml->frm_subaction);
        $ghtml->print_redirect_back("PHP settings updated successfully");
    }

    $ghtml->print_begin();
    $ghtml->print_head();
    $ghtml->print_body();
    $ghtml->print_header();
    $ghtml->print_navigation();
    $ghtml->print_content_begin();

    $ghtml->print_title("PHP Configuration");

    $serverweb = new serverweb(null, null, 'pserver-localhost');

    $vlist = $serverweb->updateform('php_branch', null);
    $ghtml->print_update_form($vlist, array('php_branch'));

    $vlist = $serverweb->updateform('multiple_php_install', null);
    $ghtml->print_update_form($vlist, array('multiple_php_install'));

    $vlist = $serverweb->updateform('multiple_php_remove', null);
    $ghtml->print_update_form($vlist, array('multiple_php_remove'));

    $vlist = $serverweb->updateform('php_used', null);
    $ghtml->print_update_form($vlist, array('php_used'));

    $ghtml->print_content_end();
    $ghtml->print_footer();
    $ghtml->print_end();
}

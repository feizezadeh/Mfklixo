<?php

class serverweb extends lxdb
{
	static $__desc = array("", "", "webserver_config");
	static $__desc_nname = array("", "", "webserver_config");
	static $__desc_php_type = array("", "", "php_type");
	static $__desc_secondary_php = array("", "", "secondary_php");

	static $__acdesc_update_edit = array("", "", "config");
	static $__acdesc_show = array("", "", "webserver_config");

	static $__desc_apache_optimize = array("", "", "apache_optimize");
	static $__desc_enable_keepalive = array("f", "", "enable_keepalive");

	static $__desc_mysql_convert = array("", "", "mysql_convert");
	static $__desc_mysql_charset = array("", "", "mysql_charset");

	static $__desc_fix_chownchmod = array("", "", "fix_chownchmod");
	static $__desc_fix_chownchmod_user = array("", "", "fix_chownchmod");

	static $__desc_php_branch = array("", "", "php_branch");
	static $__desc_php_used = array("", "", "php_used");

	static $__desc_multiple_php_flag = array("f", "", "multiple_php_enable");

	static $__desc_multiple_php_install = array("", "", "multiple_php_install");
	static $__desc_multiple_php_already_installed = array("", "", "multiple_php_already_installed");
	static $__desc_multiple_php_remove = array("", "", "multiple_php_remove");

	static $__desc_pagespeed_cache = array("", "", "pagespeed_cache");

	static $__desc_enable_php52m_fpm = array("", "", "enable_php52m_fpm");

	function createShowUpdateform()
	{
		global $login;

		if ($this->getParentO()->getClass() === 'pserver') {
			$uflist['edit'] = null;

			$uflist['php_used'] = null;

			$uflist['php_branch'] = null;

			$uflist['multiple_php_activate'] = null;
			$uflist['multiple_php_install'] = null;
			$uflist['multiple_php_remove'] = null;

			$a = getListOnList('set.php');

			foreach ($a as $k => $v) {
				if (strpos($v, 'php52') !== false) {
					$uflist['enable_php52m_fpm'] = null;

					break;
				}
			}

			if (isWebProxyOrApache()) {
				$uflist['php_type'] = null;
				$uflist['apache_optimize'] = null;
			}

			$uflist['mysql_convert'] = null;

			$uflist['fix_chownchmod'] = null;

			$uflist['pagespeed_clear_cache'] = null;
		} else {
			$uflist['fix_chownchmod_user'] = null;
		}

		return $uflist;
	}

	function updateform($subaction, $param)
	{
		switch($subaction) {
            case "webswitch":
                return $this->updateform_webswitch($param);
			case "apache_optimize":
                return $this->updateform_apache_optimize($param);
			case "mysql_convert":
                return $this->updateform_mysql_convert($param);
			case "fix_chownchmod":
                return $this->updateform_fix_chownchmod($param);
			case "fix_chownchmod_user":
                return $this->updateform_fix_chownchmod_user($param);
			case "php_type":
                return $this->updateform_php_type($param);
			case "php_branch":
                return $this->updateform_php_branch($param);
			case "multiple_php_install":
                return $this->updateform_multiple_php_install($param);
			case "multiple_php_remove":
                return $this->updateform_multiple_php_remove($param);
			case "multiple_php_activate":
                return $this->updateform_multiple_php_activate($param);
			case "php_used":
                return $this->updateform_php_used($param);
			case "enable_php52m_fpm":
                return $this->updateform_enable_php52m_fpm($param);
			case "pagespeed_clear_cache":
                return $this->updateform_pagespeed_clear_cache($param);
			default:
				$vlist['__v_button'] = array();
				return $vlist;
		}
	}

    private function updateform_webswitch($param)
    {
        include "file/driver/rhel.inc";
        $this->web_driver = null;
        $vlist['web_driver'] = array('s', $driver['web']);
        $this->setDefaultValue('web_driver', $this->getParentO()->driver_b->pg_web);
        return $vlist;
    }

    private function updateform_apache_optimize($param)
    {
        $this->apache_optimize = null;

        $out = null;
        exec("cat /etc/httpd/conf.d/~lxcenter.conf | grep -i '### selected:'", $out);

        if (count($out) > 0) {
            if (strpos($out[0], 'customize') !== false) {
                $a = array('default', 'low', 'medium', 'high', 'customize');
            } else {
                $a = array('default', 'low', 'medium', 'high');
            }
        } else {
            $a = array('default', 'low', 'medium', 'high');
        }

        $vlist['apache_optimize'] = array('s', $a);

        $b = '';

        if (count($out) > 0) {
            foreach ($a as $k => $v) {
                if (strpos($out[0], $v) !== false) {
                    $b = $v;

                    break;
                }
            }
        }

        if ($b !== '') {
            $this->setDefaultValue('apache_optimize', $b);
        }

        $this->enable_keepalive = null;

        $vlist['enable_keepalive'] = null;

        $out = null;
        exec("cat /etc/httpd/conf.d/~lxcenter.conf | grep -i ^'keepalive on'", $out);

        if (count($out) > 0) {
            $s = 'on';
        } else {
            $s = 'off';
        }

        $this->setDefaultValue('enable_keepalive', $s);

        return $vlist;
    }

    private function updateform_mysql_convert($param)
    {
        $this->mysql_convert = null;
        $this->mysql_charset = null;

        // TODO: "SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'kloxo';"
        // mysql -u[user] -p -D[database] -e "show table status\G"| egrep "(Index|Data)_length" | awk 'BEGIN { rsum = 0 } { rsum += $2 } END { print rsum }'

        if (getRpmBranchInstalled('mysql') === 'MariaDB-server') {
            $vlist['mysql_convert'] = array('s', array('to-myisam', 'to-innodb', 'to-aria', 'to-tokudb'));
        } else {
            $vlist['mysql_convert'] = array('s', array('to-myisam', 'to-innodb'));
        }

        $vlist['mysql_charset'] = array('s', array( 'utf8'));

        return $vlist;
    }

    private function updateform_fix_chownchmod($param)
    {
        $this->fix_chownchmod = null;
        $vlist['fix_chownchmod'] = array('s', array('fix-ownership', 'fix-permissions', 'fix-ALL'));
        return $vlist;
    }

    private function updateform_fix_chownchmod_user($param)
    {
        $this->fix_chownchmod_user = null;
        $vlist['fix_chownchmod_user'] = array('s', array('fix-ownership', 'fix-permissions', 'fix-ALL'));
        return $vlist;
    }

    private function updateform_php_type($param)
    {
        $this->php_type = null;
        $this->secondary_php = null;

        $a = array('suphp_event', 'suphp_worker',
            'php-fpm_event', 'php-fpm_worker',
            'fcgid_event', 'fcgid_worker');

        if (file_exists("/etc/httpd/modules/libphp5.so")) {
            // MR -- remove mod_php on 'php-type' select
            $a = array_merge(array('mod_php_ruid2', 'mod_php_itk','suphp'), $a);
        }

        if (file_exists("../etc/flag/use_apache24.flg")) {
            $a = array_merge($a, array('proxy_fcgi_event', 'proxy_fcgi_worker'));

        }

        $vlist['php_type'] = array('s', $a);

        $d = db_get_value("serverweb", "pserver-". $this->syncserver, "php_type");

        if (!$d) {
            db_set_default("serverweb", "php_type", "php-fpm_event",
                "nname = 'pserver-{$this->syncserver}'");
            $this->setDefaultValue('php_type', 'php-fpm_event');
        } else {
            $this->setDefaultValue('php_type', $d);
        }

        $vlist['secondary_php'] = array('f', array('on', 'off'));

        if (file_exists("/etc/httpd/conf.d/suphp2.conf")) {
            $this->setDefaultValue('secondary_php', 'on');
        }

        if (file_exists("/etc/httpd/conf.d/suphp52.conf")) {
            lxfile_rm("/etc/httpd/conf.d/suphp52.conf");
        }

        return $vlist;
    }

    private function updateform_php_branch($param)
    {
        $this->php_branch = null;

        $a = getListOnList('set.php');
        $vlist['php_branch'] = array('s', $a);

        $this->setDefaultValue('php_branch', getRpmBranchInstalledOnList('php'));

        return $vlist;
    }

    private function updateform_multiple_php_install($param)
    {
        $this->multiple_php_already_installed = null;
        $this->multiple_php_install = null;

        //	$a = rl_exec_get(null, $this->syncserver, "getCleanRpmBranchListOnList", array('php'));
        $a = getCleanRpmBranchListOnList('php');

        //	$g = rl_exec_get(null, $this->syncserver, "getMultiplePhpList");
        $g = getMultiplePhpList();

        $u = array_diff($a, $g);

        $vlist['multiple_php_install'] = array("U", $u);

        return $vlist;
    }

    private function updateform_multiple_php_remove($param)
    {
        $this->multiple_php_remove = null;

        $a = getMultiplePhpList();

        $vlist['multiple_php_remove'] = array("U", $a);

        return $vlist;
    }

    private function updateform_multiple_php_activate($param)
    {
        $h = implode(" ", getMultiplePhpList());

        $vlist['multiple_php_already_installed'] = array("M", $h);

        $vlist['multiple_php_flag'] = array("f", array('on', 'off'));

        $this->multiple_php_flag = null;

        $s = (file_exists("../etc/flag/enablemultiplephp.flg")) ? 'on' : 'off';

        $this->setDefaultValue('multiple_php_flag', $s);

        return $vlist;
    }

    private function updateform_php_used($param)
    {
        $this->php_used = null;

        $d = getMultiplePhpList();
        $g = getInitialPhpFpmConfig();

        $s = '--PHP Branch--';

        if (isset($d)) {
            foreach ($d as $k => $v) {
                if ($v === 'php52m') {
                    unset($d[$k]);
                }
            }

            $d = array_merge(array($s), $d);
        } else {
            $d = array($s);
        }

        if ($g === 'php') {
            $j = $s;
        } else {
            $j = $g;
        }

        $this->setDefaultValue('php_used', $j);

        $vlist['php_used'] = array('s', $d);

        return $vlist;
    }

    private function updateform_enable_php52m_fpm($param)
    {
        $this->enable_php52m_fpm = null;

        $vlist['enable_php52m_fpm'] = array("f", array('on', 'off'));

        if (file_exists("../etc/flag/enable_php52m-fpm.flg")) {
            $this->setDefaultValue('enable_php52m_fpm', 'on');
        }

        return $vlist;
    }

    private function updateform_pagespeed_clear_cache($param)
    {
        $this->pagespeed_cache = null;

        $vlist['pagespeed_cache'] = array('s', array( 'clear'));

        return $vlist;
    }

	static function initThisObjectRule($parent, $class, $name = null)
	{
		return $parent->getClName();
	}
}

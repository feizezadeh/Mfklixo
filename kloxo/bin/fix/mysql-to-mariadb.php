<?php 

// by mustafa@bigraf.com for Kloxo-MR

include_once "lib/html/include.php"; 

$mysqlbranch = getRpmBranchInstalled('mysql');

echo "*** Change MySQL to MariaDB - begin ***\n";

system("apt-get clean all");
system("sh /script/fix-service-list");
echo "\n";

if (strpos($mysqlbranch, "MariaDB") !== false) {
	echo "* Already '{$mysqlbranch}' installed\n";
} elseif (strpos($mysqlbranch, "mariadb") !== false) {
	echo "* Already '{$mysqlbranch}' installed\n";
} else {
	exec("apt-cache search mariadb-server", $out, $ret);
	
//	if ($ret) {
//		echo "- No repo for MariaDB.\n";
//		echo "  Open '/etc/apt/sources.list.d/mratwork.list and uncomment it'\n";
//		exit;
//	} else {
		system("apt-get clean all");

		// MR -- also issue on Centos 5.9 - prevent for update!
		if (php_uname('m') === 'x86_64') {
			system("apt-get remove mysql-server:i386 -y");
		}

		
		$out2 = shell_exec("dpkg -l | grep mysql-server");

		$arr = explode("\n", $out2);

		echo "- Remove MySQL packages\n";
		system("'cp' -f /etc/mysql/my.cnf /etc/my.cnf._bck_");
		
		foreach ($arr as &$o) {
			if (strpos($o, "mysql-server") !== false) { continue; }
			system("dpkg -r {$o}");
		}
		
		// MR -- may trouble if remove for mysqli extension
	//	system("apt-get install mysql-client -y");

		if (!file_exists("/var/lib/mysqltmp")) {
			mkdir("/var/lib/mysqltmp");			
		}

		chown("/var/lib/mysqltmp", "mysql:mysql");

		echo "- Install MariaDB\n";
		system("apt-get install mariadb-server -y");

		system("'cp' -f /etc/my.cnf._bck_ /etc/mysql/my.cnf");

		system("chmod 777 /var/lib/mysqltmp");

		echo "- Restart MariaDB\n";
		system("update-rc.d mysql defaults >/dev/null 2>&1");
		system("service mysql restart");
//	}
}

echo "\n";
//echo " - Note: remove 'skip-innodb' from '/etc/my.cnf' and '/etc/my.cnf.d/server.cnf'.\n";
//echo "   Need reboot!.\n\n";

echo "*** Change MySQL to MariaDB - end ***\n";




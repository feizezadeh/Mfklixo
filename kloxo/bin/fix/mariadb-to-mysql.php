<?php 

// by mustafa@bigraf.com for Kloxo-MR

include_once "lib/html/include.php"; 

$mysqlbranch = getRpmBranchInstalled('mysql');

echo "*** Change MariaDB to MySQL - begin ***\n";

system("apt-get clean all");
system("sh /script/fix-service-list");
echo "\n";

if (strpos($mysqlbranch, "mysql") !== false) {
	echo "* Already '{$mysqlbranch}' installed\n";
} elseif (strpos($mysqlbranch, "mariadb") !== false) {
	echo "* Already '{$mysqlbranch}' installed\n";
} else {

	exec("apt-cache search mariadb-server", $out, $ret);
	
//	if ($ret) {
//		echo "- Repo for MariaDB exists.\n";
//		echo "  Open '/etc/apt/sources.list.d/mratwork.list and comment it'\n";
//		echo "  and then run 'sh /script/cleanup' again\n";
//		exit;
//	} else {
		// MR -- don't use $mysqlbranch because for MariaDB mean MariaDB-server
		$out2 = shell_exec("dpkg -l | grep mariadb-server");

		$arr = explode("\n", $out2);

		echo "- Remove MariaDB packages\n";
		foreach ($arr as &$o) {
			system("dpkg -r {$o}");
		}

		echo "- Install MySQL\n";
		system("apt-get install mysql-server -y");

		if (file_exists("/etc/mysql/my.cnf")) {
			system("'cp' -f /etc/mysql/my.cnf /etc/my.cnf");
		} elseif (file_exists("/etc/my.cnf._bck_")) {
			system("'cp' -f /etc/my.cnf._bck_ /etc/my.cnf");
		}

		echo "- Restart MySQL\n";
		system("chkconfig mysqld on >/dev/null 2>&1");
		system("service mysqld restart");
//	}
}

echo "\n";
echo " - Note: remove 'skip-innodb' from '/etc/my.cnf' and '/etc/my.cnf.d/my.cnf'.\n";
echo "   Need reboot!.\n\n";

echo "*** Change MariaDB to MySQL - end ***\n";


<?php
// 数据库配置
$db_config = array(
	'host' => 'localhost',
	'port' => '3306',
	'dbname' => 'db_chidori',
	'username' => 'root',
	'password' => 'root',
	'charset' => 'utf8mb4',
);

$table_prefix = 'ps_';
$prov_tb = $table_prefix.'province';
$city_tb = $table_prefix.'city';
$area_tb = $table_prefix.'area';

// 从文件读取区域数据
$prov_data = file_get_contents('../dist/provinces.json');
$city_data = file_get_contents('../dist/cities.json');
$area_data = file_get_contents('../dist/areas.json');
$prov_array = json_decode($prov_data, true);
unset($prov_data);
$city_array = json_decode($city_data, true);
unset($city_data);
$area_array = json_decode($area_data, true);
unset($area_data);

// 初始化数据库连接
try {
	$dbh = new PDO("mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['dbname']};charset={$db_config['charset']}",
		$db_config['username'], $db_config['password']);
} catch (Exception $e) {
	echo '数据库链接失败：', $e->getMessage();
	exit();
}


// 表格创建SQL
$table_create_sql = <<<SQL
CREATE TABLE IF NOT EXISTS `{$prov_tb}` (
`code` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`name` varchar(32) NOT NULL,
PRIMARY KEY(`code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='省份表';

CREATE TABLE IF NOT EXISTS `{$city_tb}` (
`code` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`name` varchar(32) NOT NULL,
`parent_code` int(11) UNSIGNED NOT NULL,
PRIMARY KEY(`code`), KEY(`parent_code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='城市表';

CREATE TABLE IF NOT EXISTS `{$area_tb}` (
`code` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`name` varchar(32) NOT NULL,
`parent_code` int(11) UNSIGNED NOT NULL,
PRIMARY KEY(`code`), KEY(`parent_code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='地区表';
SQL;
// 执行表格创建
$dbh->exec($table_create_sql);

// 省份数据添加
$prov_data_insert_sql = "INSERT INTO `{$prov_tb}` (`code`, `name`) VALUES ";
// 城市数据添加
$city_data_insert_sql = "INSERT INTO `{$city_tb}` (`code`, `name`, `parent_code`) VALUES ";
// 地区数据添加
$area_data_insert_sql = "INSERT INTO `{$area_tb}` (`code`, `name`, `parent_code`) VALUES ";

// 省份数据删除
$prov_delete_sql = "DELECT FROM `{$prov_tb}`";
// 城市数据删除
$city_delete_sql = "DELECT FROM `{$city_tb}`";
// 地区数据删除
$area_delete_sql = "DELECT FROM `{$area_tb}`";

// 省市区数据组装
$values = array();
foreach ($prov_array as $prov) {
	$values[] = "({$prov['code']}, '{$prov['name']}')";
}
$prov_data_insert_sql .= implode(',', $values).';';

$values = array();
foreach ($city_array as $city) {
	$values[] = "({$city['code']}, '{$city['name']}', '{$city['parent_code']}')";
}
$city_data_insert_sql .= implode(',', $values).';';

$values = array();
foreach ($area_array as $area) {
	$values[] = "({$area['code']}, '{$area['name']}', '{$area['parent_code']}')";
}
$area_data_insert_sql .= implode(',', $values).';';


try {
	$dbh->beginTransaction();
	$dbh->exec($prov_delete_sql);
	$dbh->exec($city_delete_sql);
	$dbh->exec($area_delete_sql);
	$dbh->exec($prov_data_insert_sql);
	$dbh->exec($city_data_insert_sql);
	$dbh->exec($area_data_insert_sql);
	$dbh->commit();
} catch (Exception $e) {
	$dbh->rollBack();
	echo '导入数据失败：', $e->getMessage();
}

$dbh = null;

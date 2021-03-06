<?php
// Debug mode (use 0, 1 or 2):
$debug = 0;

// Run full install if config doesn't exist
if (!file_exists('../config.php')) {
	header('Location: ./index.php');
	exit;
}

// Configuration
require_once('../config.php');

// Startup
require_once(DIR_SYSTEM . 'startup.php');

// Get Path & Url
$errors = array();
$baseurl=(isset($_SERVER['HTTPS']) ? 'https' :'http'). '://' . $_SERVER['HTTP_HOST'] . str_replace('/install','',dirname($_SERVER['REQUEST_URI']));
chdir('..');
$basepath=getcwd();
chdir(dirname(__FILE__));

if (!$link = @mysql_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD)) {
	$errors[] = 'Could not connect to the database server using the username and password provided.';
} else {
	if (!@mysql_select_db(DB_DATABASE, $link)) {
		$errors[] = 'The database could selected, check you have permissions, and check it exists on the server.';
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Installation</title>
	<link rel="stylesheet" type="text/css" href="style.css">
	</head>

	<body>
		<h1>ocStore 0.x.x Upgrade Script (BETA)</h1>
		<div id="container">
<?php
	if (empty($errors)) {
		// Run upgrade script

		@include_once(DIR_CONFIG . 'config_tuning.php');
		$output  = '<?php' . "\n";
		$output .= '// TUNING' . "\n";
		$output .= "\n";
		$output .= '// Время жизни кук в браузере посетителя. Значение в днях (по умолчанию = 183 дня)' . "\n";
		$output .= 'define(\'CONF_COOKIES_LIFETIME\', ' . (defined('CONF_COOKIES_LIFETIME') ? CONF_COOKIES_LIFETIME : '183') . ');' . "\n";
		$output .= "\n";
		$output .= '// Каталог для сессионных файлов. Возможные значения:' . "\n";
		$output .= '//  \'\' (по умолчанию) - файлы будут сохраняться в каталоге, указанном в php.ini' . "\n";
		$output .= '//  \'path:/путь/к_каталогу\' - файлы будут сохраняться по указанному пути' . "\n";
		$output .= 'define(\'CONF_SESSION_DIR\', \'' . (defined('CONF_SESSION_DIR') ? CONF_SESSION_DIR : '') . '\');' . "\n";
		$output .= "\n";
		$output .= '// Время жизни сессионных файлов. Значение указывается в минутах.' . "\n";
		$output .= '// Если указано 0 (по умолчанию), то значение берётся из файла php.ini' . "\n";
		$output .= 'define(\'CONF_SESSION_LIFETIME\', ' . (defined('CONF_SESSION_LIFETIME') ? CONF_SESSION_LIFETIME : 0) . ');' . "\n";
		$output .= '?>';
		
		if ($file = @fopen(DIR_CONFIG . 'config_tuning.php', 'w')) {
			fwrite($file, $output);
			fclose($file);
		} else {
			$errors[] = '<font color=red>Can\'t write to \'config_tuning.php\' in ' . DIR_CONFIG . ', check permissions to dir;</font>';
		}

		$file='upgrade.sql';
		if (!file_exists($file)) {
			$errors[] = 'Upgrade SQL file '.$file.' could not be found.';
		} else {
			mysql_query('set character set utf8', $link);
			if ($sql=file($file)) {
				$query = '';
				$num_line = 0;
				foreach($sql as $line) {
					$num_line++;

					// Hacks for compatibility (needs to be improved)
					$line = str_replace("oc_", DB_PREFIX, $line);
					$line = str_replace(" order ", " `order` ", $line);
					$line = str_replace(" ssl ", " `ssl` ", $line);
					$line = str_replace("NOT NULL DEFAULT ''", "NOT NULL", $line);
					$line = str_replace("NOT NULL DEFAULT NULL", "NOT NULL", $line);
					$line = str_replace("NOT NULL DEFAULT 0 COMMENT '' auto_increment", "NOT NULL COMMENT '' auto_increment", $line);

					switch ($debug) {
						case 0: break;
						case 1: if (preg_match('/^#|--/', $line) || !preg_match('/\S/', $line)) break;
						case 2:
						default: echo '<font color="brown">Read line '.$num_line.':</font> {' . $line . '}<br>';
					}

					if ((substr(trim($line), 0, 2) == '--') || (substr(trim($line), 0, 1) == '#')) {
						if ($debug > 1) echo "^^ comment <b>(continue)</b><br><br>";
						continue;
					}

					if (preg_match('/^ALTER TABLE `?(.+?)`? ADD PRIMARY KEY/', $line, $matches)) {
						if (mysql_num_rows(@mysql_query(sprintf("SHOW KEYS FROM `%s` WHERE Key_name = 'PRIMARY'",$matches[1]), $link)) > 0) {
							if ($debug) echo "^^ 'PRIMARY' key already exists <b>(continue)</b><br><br>";
							continue;
						}
					}
					if (preg_match('/^ALTER TABLE `?(\w+?)`? ADD (?:INDEX|KEY) (.+)/', $line, $matches)) {
						$index = 'PRIMARY';
						if (preg_match('/^\s*`?(\w+?)`?\s+\(/', $matches[2], $i)) { $index = $i[1]; }
						if (mysql_num_rows(@mysql_query(sprintf("SHOW INDEX FROM `%s` WHERE Key_name = '%s'", $matches[1],$index), $link)) > 0) {
							if ($debug) echo "^^ '$index' key already exists <b>(continue)</b><br><br>";
							continue;
						}
					}
					if (preg_match('/^ALTER TABLE `?(\w+?)`? ADD(?! INDEX| KEY)(?: COLUMN)? `?(.+?)`? /', $line, $matches)) {
						if (mysql_num_rows(@mysql_query(sprintf("SHOW COLUMNS FROM `%s` LIKE '%s'", $matches[1],$matches[2]), $link)) > 0) {
							if ($debug) echo "^^ '$matches[2]' column already exists <b>(continue)</b><br><br>";
							continue;
						}
					}

					if (preg_match('/^ALTER TABLE `?(\w+?)`? DROP PRIMARY KEY;/', $line, $matches)) {
						if (mysql_num_rows(@mysql_query(sprintf("SHOW KEYS FROM `%s` WHERE Key_name = 'PRIMARY'", $matches[1]), $link)) <= 0) {
							if ($debug) echo "^^ 'PRIMARY' key already dropped <b>(continue)</b><br><br>";
							continue;
						}
					}
					if (preg_match('/^ALTER TABLE `?(\w+?)`? DROP (?:INDEX|KEY) `?(\w+?)`?;/', $line, $matches)) {
						if (mysql_num_rows(@mysql_query(sprintf("SHOW KEYS FROM `%s` WHERE Key_name = '%s'", $matches[1],$matches[2]), $link)) <= 0) {
							if ($debug) echo "^^ '$matches[2]' key already dropped <b>(continue)</b><br><br>";
							continue;
						}
					}
					if (preg_match('/^ALTER TABLE `?(\w+?)`? DROP(?: COLUMN)? `?(\w+?)`?;/', $line, $matches)) {
							if (mysql_num_rows(@mysql_query(sprintf("SHOW COLUMNS FROM `%s` LIKE '%s'", $matches[1],$matches[2]), $link)) <= 0) {
								if ($debug) echo "^^ '$matches[2]' column already dropped <b>(continue)</b><br><br>";
								continue;
							}
					}

					if (preg_match('/^ALTER TABLE `?(.+?)`? MODIFY(?: COLUMN)? `?(\w+?)`? /', $line, $matches)) {
						if (mysql_num_rows(@mysql_query(sprintf("SHOW COLUMNS FROM `%s` LIKE '%s'", $matches[1],$matches[2]), $link)) <= 0) {
							if ($debug) echo "^^ '$matches[2]' column NOT exists <b>(continue)</b><br><br>";
							continue;
						}
					}

					if (!empty($line)) {
						$query .= $line;
						if (preg_match('/;\s*$/', $line)) {
							if ($debug) echo 'Try To Execute: {<font color="blue">' . $query . '</font>}<br>';
							if (mysql_query($query, $link) === false) {
								if ($debug) echo '<b>&lt;--- ERROR</b><br>';
								$errors[] = 'Could not execute this query ('.mysql_errno($link).'='.mysql_error($link).'):<br><b>-&gt; '.$query.'</b>';
							}
							$query = '';
							if ($debug) echo '<br>';
						}
					}
				}
			}
		}
	}

	// Check if there are any products associated with a store (pre-1.4.1)
	$info = mysql_fetch_assoc(mysql_query("SELECT * FROM " . DB_PREFIX . "product_to_store", $link));

	// If not, then add them all to the default
	if (!$info) {
		$resource = mysql_query("SELECT product_id FROM " . DB_PREFIX . "product", $link);
		$data = array();
		$i = 0;
		while ($result = mysql_fetch_assoc($resource)) {
			$data[$i] = $result;

			$i++;
		}

		foreach ($data as $product) {
		    mysql_query("INSERT INTO " . DB_PREFIX . "product_to_store (product_id, store_id) VALUES ('".$product['product_id']."', '0')", $link);
		}
	}

	// Check if there are any categories associated with a store (pre-1.4.1)
	$info = mysql_fetch_assoc(mysql_query("SELECT * FROM " . DB_PREFIX . "information_to_store", $link));

	// If not, then add them all to the default
	if (!$info) {
		$resource = mysql_query("SELECT information_id FROM " . DB_PREFIX . "information", $link);
		$data = array();
		$i = 0;
		while ($result = mysql_fetch_assoc($resource)) {
			$data[$i] = $result;

			$i++;
		}

		foreach ($data as $information) {
		    mysql_query("INSERT INTO " . DB_PREFIX . "information_to_store (information_id, store_id) VALUES ('".$information['information_id']."', '0')", $link);
		}
	}

	// Check if there are any categories associated with a store (pre-1.4.1)
	$info = mysql_fetch_assoc(mysql_query("SELECT * FROM " . DB_PREFIX . "category_to_store", $link));

	// If not, then add them all to the default
	if (!$info) {
		$resource = mysql_query("SELECT category_id FROM " . DB_PREFIX . "category", $link);
		$data = array();
		$i = 0;
		while ($result = mysql_fetch_assoc($resource)) {
			$data[$i] = $result;

			$i++;
		}

		foreach ($data as $category) {
		    mysql_query("INSERT INTO " . DB_PREFIX . "category_to_store (category_id, store_id) VALUES ('".$category['category_id']."', '0')", $link);
		}
	}

	// Check if there are any categories associated with a store (pre-1.4.1)
	$info = mysql_fetch_assoc(mysql_query("SELECT * FROM " . DB_PREFIX . "manufacturer_to_store", $link));

	// If not, then add them all to the default
	if (!$info) {
		$resource = mysql_query("SELECT manufacturer_id FROM " . DB_PREFIX . "manufacturer", $link);
		$data = array();
		$i = 0;
		while ($result = mysql_fetch_assoc($resource)) {
			$data[$i] = $result;

			$i++;
		}

		foreach ($data as $manufacturer) {
		    mysql_query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store (manufacturer_id, store_id) VALUES ('".$manufacturer['manufacturer_id']."', '0')", $link);
		}
	}

	if (!empty($errors)) { //has to be a separate if
		?>
		<p>The following <?php echo count($errors); ?> errors occured:</p>
		<?php foreach ($errors as $error) {?>
		<div class="warning"><?php echo $error;?></div><br />
		<?php } ?>
		<p>The above errors occurred because the script could not properly determine the existing state of those db elements. Your store may not need those changes. Please post any errors on the forums to ensure that they can be addressed in future versions!</p>
		</div>
<?php } else { ?>
		<h2>SUCCESS!!! Click <a href="<?php echo $baseurl; ?>">here</a> to goto your store</h2>
<?php } ?>
		<div class="center"><a href="http:/myopencart.ru/">myOpenCart.ru</a></div>
	</body>
</html>
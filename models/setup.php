<?php
	class setup_model extends model {
		private $required_php_extensions = array("libxml", "mysqli", "xsl");

		/* Determine next step
		 */
		public function step_to_take() {
			$missing = $this->missing_php_extensions();
			if (count($missing) > 0) {
				return "php_extensions";
			}

			if ($this->db->connected == false) {
				$db = new MySQLi_connection(DB_HOSTNAME, DB_DATABASE, DB_USERNAME, DB_PASSWORD);
			} else { 
				$db = $this->db;
			}

			if ($db->connected == false) {
				/* No database connection
				 */
				if ((DB_HOSTNAME == "localhost") && (DB_DATABASE == "monitor") && (DB_USERNAME == "monitor") && (DB_PASSWORD == "monitor")) {
					return "db_settings";
				} else if (strpos(DB_PASSWORD, "'") !== false) {
					$this->output->add_system_message("A single quote is not allowed in the password!");
					return "db_settings";
				}

				return "create_db";
			}

			$result = $db->execute("show tables like %s", "settings");
			if (count($result) == 0) {
				return "import_sql";
			}

			if ($this->settings->database_version < $this->latest_database_version()) {
				return "update_db";
			}

			return "done";
		}

		/* Missing PHP extensions
		 */
		public function missing_php_extensions() {
			static $missing = null;

			if ($missing !== null) {
				return $missing;
			}

			$missing = array();
			foreach ($this->required_php_extensions as $extension) {
				if (extension_loaded($extension) == false) {
					array_push($missing, $extension);
				}
			}

			return $missing;
		}

		/* Remove datase related error messages
		 */
		public function remove_database_errors() {
			$errors = explode("\n", rtrim(ob_get_contents()));
			ob_clean();

			foreach ($errors as $error) {
				if (strtolower(substr($error, 0, 14)) != "mysqli_connect") {
					print $error;
				}
			}
		}

		/* Create the MySQL database
		 */
		public function create_database($username, $password) {
			$db = new MySQLi_connection(DB_HOSTNAME, "mysql", $username, $password);

			if ($db->connected == false) {
				$this->output->add_message("Error connecting to database.");
				return false;
			}

			$db->query("begin");

			/* Create database
			 */
			$query = "create database if not exists %S character set utf8";
			if ($db->query($query, DB_DATABASE) == false) {
				$db->query("rollback");
				$this->output->add_message("Error creating database.");
				return false;
			}

			/* Create user
			 */
			$query = "select count(*) as count from user where User=%s";
			if (($users = $db->execute($query, DB_USERNAME)) === false) {
				$db->query("rollback");
				$this->output->add_message("Error checking for user.");
				return false;
			}

			if ($users[0]["count"] == 0) {
				$query = "create user %s@%s identified by %s";
				if ($db->query($query, DB_USERNAME, DB_HOSTNAME, DB_PASSWORD) == false) {
					$db->query("rollback");
					$this->output->add_message("Error creating user.");
					return false;
				}
			} else {
				$login_test = new MySQLi_connection(DB_HOSTNAME, DB_DATABASE, DB_USERNAME, DB_PASSWORD);
				if ($login_test->connected == false) {
					$db->query("rollback");
					$this->output->add_message("Invalid credentials in settings/website.conf.");
					return false;
				}
			}

			/* Set access rights
			 */
			$rights = array(
				"select", "insert", "update", "delete",
				"create", "drop", "alter", "index", "lock tables",
				"create view", "show view");

			$query = "grant ".implode(", ", $rights)." on %S.* to %s@%s";
			if ($db->query($query, DB_DATABASE, DB_USERNAME, DB_HOSTNAME) == false) {
				$db->query("rollback");
				$this->output->add_message("Error setting access rights.");
				return false;
			}

			/* Commit changes
			 */
			$db->query("commit");
			$db->query("flush privileges");
			unset($db);

			return true;
		}

		/* Import SQL script from file
		 */
		public function import_sql() {
			system("mysql -h '".DB_HOSTNAME."' -u '".DB_USERNAME."' --password='".DB_PASSWORD."' '".DB_DATABASE."' < ../database/mysql.sql", $result);
			if ($result != 0) {
				$this->output->add_message("Error while importing database tables.");
				return false;
			}

			$this->db->query("update users set status=%d", USER_STATUS_CHANGEPWD);
			$this->settings->secret_website_code = random_string();

			return true;
		}

		/* Collect latest database version from update_database() function
		 */
		private function latest_database_version() {
			$old_db = $this->db;
			$old_settings = $this->settings;
			$this->db = new dummy_object();
			$this->settings = new dummy_object();
			$this->settings->database_version = 0;

			$this->update_database();
			$version = $this->settings->database_version;

			unset($this->db);
			unset($this->settings);
			$this->db = $old_db;
			$this->settings = $old_settings;

			return $version;
		}

		/* Add setting when missing
		 */
		private function ensure_setting($key, $type, $value) {
			if ($this->db->entry("settings", $key, "key") != false) {
				return true;
			}

			$entry = array(
				"key"   => $key,
				"type"  => $type,
				"value" => $value);
			return $this->db->insert("settings", $entry) !== false;
		}

		/* Update database
		 */
		public function update_database() {
			if ($this->settings->database_version < 101) {
				$this->settings->database_version = 101;
			}

			if ($this->settings->database_version < 102) {
				$this->ensure_setting("hiawatha_cache_enabled", "boolean", "false");
				$this->ensure_setting("hiawatha_cache_default_time", "integer", "3600");
				$this->ensure_setting("session_timeout", "integer", "3600");
				$this->ensure_setting("session_persistent", "boolean", "false");

				$this->settings->database_version = 102;
			}

			if ($this->settings->database_version < 103) {
				$tables = array("cgi_statistics", "host_statistics", "server_statistics");
				foreach ($tables as $table) {
					$this->db->query("alter table %S add %S date not null after %S", $table, "date", "id");
					$this->db->query("alter table %S add %S tinyint unsigned not null after %S", $table, "hour", "date");
					$this->db->query("update %S set %S=date(%S), %S=%d", $table, "date", "timestamp_begin", "hour", 0);
					$this->db->query("alter table %S drop %S", $table, "timestamp_begin");
					$this->db->query("alter table %S drop %S", $table, "timestamp_end");
				}

				$this->db->query("alter table %S add index(%S)", "cgi_statistics", "date");
				$this->db->query("alter table %S add index(%S)", "cgi_statistics", "hour");
				$this->db->query("alter table %S add index(%S)", "host_statistics", "date");
				$this->db->query("alter table %S add index(%S)", "host_statistics", "hour");
				$this->db->query("alter table %S add index(%S)", "server_statistics", "date");
				$this->db->query("alter table %S add index(%S)", "server_statistics", "hour");

				$this->settings->dashboard_threshold_change = 150;
				$this->settings->dashboard_threshold_value = 5;
				$this->settings->dashboard_page_refresh = 1;
				$this->settings->report_alert_high = 300;
				$this->settings->report_alert_medium = 150;
				$this->settings->report_history_days = 15;
				$this->settings->report_skip_normal = false;
				$this->settings->report_use_median = true;

				$this->settings->database_version = 103;
			}

			if ($this->settings->database_version < 104) {
				$this->settings->database_version = 104;
			}
		}
	}

	class dummy_object {
		private $cache = array();

		public function __set($key, $value) {
			$this->cache[$key] = $value;
		}

		public function __get($key) {
			return $this->cache[$key];
		}

		public function __call($func, $args) {
			return false;
		}
	}
?>

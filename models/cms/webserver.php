<?php
	class cms_webserver_model extends model {
		public function count_webservers() {
			$query = "select count(*) as count from webservers";

			if (($result = $this->db->execute($query)) == false) {
				return false;
			}

			return $result[0]["count"];
		}

		public function get_webservers($offset, $limit) {
			$query = "select * from webservers order by name limit %d,%d";

			return $this->db->execute($query, $offset, $limit);
		}

		public function get_webserver($webserver_id) {
			if (($webserver = $this->db->entry("webservers", $webserver_id)) === false) {
				return false;
			}

			$query = "select user_id from webserver_user where webserver_id=%d";
			if (($users = $this->db->execute($query, $webserver_id)) === false) {
				return false;
			}

			$webserver["users"] = array();
			foreach ($users as $user) {
				array_push($webserver["users"], (int)$user["user_id"]);
			}

			return $webserver;
		}

		public function get_users() {
			$query = "select id,fullname from users order by fullname";

			return $this->db->execute($query);
		}

		public function save_oke($webserver) {
			$result = true;

			if (trim($webserver["name"]) == "") {
				$this->output->add_message("Enter the webserver name.");
				$result = false;
			}

			if (trim($webserver["ip_address"]) == "") {
				$this->output->add_message("Enter the IP address.");
				$result = false;
			}

			if (((int)$webserver["port"] < 1) || ((int)$webserver["port"] > 65535)) {
				$this->output->add_message("Enter a valid port number.");
				$result = false;
			}

			return $result;
		}

		private function assign_users_to_webserver($webserver) {
			if ($this->db->query("delete from webserver_user where webserver_id=%d", $webserver["id"]) === false) {
				return false;
			}

			if (is_array($webserver["users"]) == false) {
				return true;
			}

			foreach ($webserver["users"] as $user_id) {
				if ($this->db->query("insert into webserver_user values (%d, %d)", $webserver["id"], $user_id) === false) {
					return false;
				}
			}

			return true;
		}

		public function create_webserver($webserver) {
			$keys = array("id", "name", "ip_address", "port", "tls", "active", "errors", "version");

			$webserver["id"] = null;
			$webserver["tls"] = is_true($webserver["tls"]) ? YES : NO;
			$webserver["active"] = is_true($webserver["active"]) ? YES : NO;
			$webserver["errors"] = 0;
			$webserver["version"] = null;

			if ($this->db->query("begin") === false) {
				return false;
			}

			if ($this->db->insert("webservers", $webserver, $keys) === false) {
				$this->db->query("rollback");
				return false;
			}
			$webserver["id"] = $this->db->last_insert_id;

			if ($this->assign_users_to_webserver($webserver) == false) {
				$this->db->query("rollback");
				return false;
			}

			return $this->db->query("commit") !== false;
		}

		public function update_webserver($webserver) {
			$keys = array("name", "ip_address", "port", "tls", "active");

			$webserver["tls"] = is_true($webserver["tls"]) ? YES : NO;
			$webserver["active"] = is_true($webserver["active"]) ? YES : NO;

			if ($this->db->query("begin") === false) {
				return false;
			}

			if ($this->db->update("webservers", $webserver["id"], $webserver, $keys) === false) {
				$this->db->query("rollback");
				return false;
			}

			if ($this->assign_users_to_webserver($webserver) == false) {
				$this->db->query("rollback");
				return false;
			}

			return $this->db->query("commit") !== false;
		}

		public function delete_oke($webserver) {
			$result = true;

			return $result;
		}

		public function delete_webserver($webserver_id) {
			$queries = array(
				array("delete from webserver_user where webserver_id=%d", $webserver_id),
				array("delete from events where webserver_id=%d", $webserver_id),
				array("delete from cgi_statistics where webserver_id=%d", $webserver_id),
				array("delete from host_statistics where webserver_id=%d", $webserver_id),
				array("delete from server_statistics where webserver_id=%d", $webserver_id),
				array("delete from webservers where id=%d", $webserver_id));

			return $this->db->transaction($queries);
		}
	}
?>

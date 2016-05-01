<?php
	class dashboard_controller extends controller {
		private $alerts = array(
				array("Failed logins",      "host",   "failed_logins"),
				array("Exploit attempts",   "host",   "exploit_attempts"),
				array("CGI errors",         "cgi",    "cgi_errors"),
				array("400 Bad request",    "server", "result_bad_request"),
				array("Client bans",        "host",   "bans"),
				array("404 Not Found",      "host",   "result_not_found"),
				array("500 Internal Error", "host",   "result_internal_error"),
				array("403 Forbidden",      "host",   "result_forbidden"));

		private function show_alert($index) {
			if (valid_input($index, VALIDATE_NUMBERS, VALIDATE_NONEMPTY) == false) {
				return;
			} else if ($index >= count($this->alerts)) {
				return;
			}

			list($title, $type, $column) = $this->alerts[(int)$index];

			$cache = new cache($this->db, "dashboard_".$this->user->username);
			if (($list = $cache->$column) === NULL) {
				$function = "get_".$type."_statistics";
				$list = $this->model->$function($column);
				$cache->store($column, $list, ($this->settings->dashboard_page_refresh * 60) - 1);
			}
			
			if ($list == false) {
				return;
			}

			$this->output->open_tag("list", array("title" => $title));
			foreach ($list as $name => $item) {
				$this->output->add_tag("item", $name, array(
					"count"  => $item["today"],
					"change" => $item["change"]));
			}
			$this->output->close_tag();
		}

		public function execute() {
			if ($this->page->ajax_request) {
				if ($this->page->pathinfo[1] == null) {
					$this->output->add_tag("max_alert_count", count($this->alerts));
					$this->output->add_tag("page_refresh", $this->settings->dashboard_page_refresh);
				} else {
					$this->show_alert($this->page->pathinfo[1]);
				}
				return;
			}

			if (isset($_SESSION["latest_hiawatha_version"]) == false) {
				$hiawatha_website = new HTTPS("www.hiawatha-webserver.org");
				if (($result = $hiawatha_website->GET("/latest")) !== false) {
					$_SESSION["latest_hiawatha_version"] = $result["body"];
				}
			}

			if (isset($_SESSION["latest_mbedtls_version"]) == false) {
				$mbedtls_website = new HTTPS("tls.mbed.org");
				if (($result = $mbedtls_website->GET("/download/latest-stable-version")) !== false) {
					$_SESSION["latest_mbedtls_version"] = $result["body"];
				}
			}

			/* Webserver
			 */
			if (($webservers = $this->model->get_webservers()) === false) {
				return;
			}

			$webservers_offline = false;

			foreach ($webservers as $webserver) {
				$webserver["address"] = ($webserver["tls"] == 0 ? "http" : "https") . "://".$webserver["ip_address"];
				if ((($webserver["tls"] == 0) && ($webserver["port"] != 80)) ||
				    (($webserver["tls"] == 1) && ($webserver["port"] != 443))) {
					$webserver["address"] .= ":".$webserver["port"];
				}
				$webserver["address"] .= "/";

				if ($webserver["active"]) {
					if ($webserver["errors"] == 0) {
						$webserver["status"] = "online";
					} else {
						$webserver["status"] = "offline";
						$webservers_offline = true;
					}
				}

				$webserver["tls"] = show_boolean($webserver["tls"]);
				$webserver["active"] = show_boolean($webserver["active"]);

				if ($webserver["version"] != "") {
					$parts = explode(",", $webserver["version"]);
					list(, $version) = explode("v", $parts[0], 2);
					$comparison = version_compare($version, $_SESSION["latest_hiawatha_version"], ">=");
					$webserver["uptodate"] = show_boolean($comparison);

					foreach ($parts as $part) {
						if (in_array(substr(ltrim($part), 0, 3), array("TLS", "SSL"))) {
							$version = trim(substr($part, 4), " (v)");
							if (version_compare($version, $_SESSION["latest_mbedtls_version"], "<")) {
								$webserver["uptodate"] .= " (mbed TLS out of date)";
							}
						}
					}
				}

				$this->output->record($webserver, "webserver");
			}

			if ($webservers_offline) {
				$this->output->add_system_message("Warning, one or more webservers are unavailable!");
			}

			/* Alerts
			 */
			$this->output->add_javascript("jquery/jquery-ui.js");
			$this->output->add_javascript("dashboard.js");
			$this->output->add_css("jquery/jquery-ui.css");

			$this->output->add_tag("threshold_change", $this->settings->dashboard_threshold_change);
			$this->output->add_tag("threshold_value", $this->settings->dashboard_threshold_value);
			$this->output->add_tag("page_refresh", $this->settings->dashboard_page_refresh);
		}
	}
?>

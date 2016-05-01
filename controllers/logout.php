<?php
	class logout_controller extends controller {
		public function execute() {
			if ($this->user->logged_in) {
				header("Status: 401");

				if (isset($_SESSION["user_switch"]) == false) {
					$this->user->logout();
					$url = $this->settings->start_page;
				} else {
					$this->user->log_action("switched back to self");
					$_SESSION["user_id"] = $_SESSION["user_switch"];
					unset($_SESSION["user_switch"]);
					unset($_SESSION["filter"]);
					$url = "cms/switch";
				}

				$this->output->add_tag("result", "You are now logged out.", array("url" => $url));
			} else {
				$this->output->add_tag("result", "You are not logged in.", array("url" => $this->settings->start_page));
			}
		}
	}
?>

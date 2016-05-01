<?php
	class setup_controller extends controller {
		public function execute() {
			if ($_SERVER["HTTP_SCHEME"] != "https") {
				$this->output->add_system_warning("Be aware! Your connection is not secured by SSL/TLS.");
			}

			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if ($_POST["submit_button"] == "Create database") {
					$this->model->create_database($_POST["username"], $_POST["password"]);
				} else if ($_POST["submit_button"] == "Import SQL") {
					$this->model->import_sql();
				} else if ($_POST["submit_button"] == "Update database") {
					$this->model->update_database();
				}
			}

			$step = $this->model->step_to_take();
			$this->output->open_tag($step);
			switch ($step) {
				case "php_extensions":
					$missing = $this->model->missing_php_extensions();
					foreach ($this->model->missing_php_extensions() as $extension) {
						$this->output->add_tag("extension", $extension);
					}
					break;
				case "db_settings":
					$this->model->remove_database_errors();
					break;
				case "create_db":
					$this->model->remove_database_errors();
					$username = isset($_POST["username"]) ? $_POST["username"] : "root";
					$this->output->add_tag("username", $username);
					$this->output->run_javascript("document.getElementById('password').focus()");
					break;
				case "import_sql":
					ob_clean();
					break;
				case "update_db":
					ob_clean();
					break;
				case "done":
					break;
			}
			$this->output->close_tag();
		}
	}
?>

<?php
	require("../libraries/user_agent.php");

	class browser_with_os_model extends model {
		public function get_information($filter_hostname, $filter_webserver) {
			$query = "select count(*) as count, concat(b.browser, %s, o.os) as info ".
					 "from requests r, user_agents u, user_agent_browser b, user_agent_os o ".
					 "where r.user_agent_id=u.id and u.browser_id=b.id and u.os_id=o.id";

			$filter_args = array();
			if ($filter_hostname != 0) {
				$query .= " and r.hostname_id=%d";
				array_push($filter_args, $filter_hostname);
			}
			if ($filter_webserver != 0) {
				$query .= " and r.webserver_id=%d";
				array_push($filter_args, $filter_webserver);
			}

			$query .= " group by info order by count desc";

			return $this->db->execute($query, " with ", $filter_args);
		}

		private function sort_browser_list($a1, $a2) {
			return $a1["count"] > $a2["count"] ? -1 : 1;
		}

		public function remove_browser_version($info) {
			global $browser_list;

			$browsers = array_unique($browser_list);

			/* Remove version information
			 */
			foreach ($info as $i => $item) {
				list($part_browser, $part_os) = explode(" with ", $item["info"]);

				foreach ($browsers as $browser) {
					if (strpos($part_browser, $browser) !== false) {
						$info[$i]["info"] = sprintf("%s with %s", $browser, $part_os);
					}
				}
			}

			/* Combine browser records
			 */
			$result = array();
			foreach ($info as $item_old) {
				foreach ($result as &$item_new) {
					if ($item_old["info"] == $item_new["info"]) {
						$item_new["count"] += $item_old["count"];
						continue 2;
					}
					unset($item_new);
				}
				array_push($result, $item_old);
			}

			/* Sort browser list
			 */
			uasort($result, array($this, "sort_browser_list"));

			return $result;
		}
	}
?>
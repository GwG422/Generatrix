<?php

	//
	// The Generatrix Controller for utils
	//

	require_once(DISK_ROOT . 'framework/library/controller.php');
	require_once(DISK_ROOT . 'framework/library/view.php');

	class generatrixController extends Controller {

		private $server = 'http://localhost/Generatrix-Packages';

		private function isCli() {
			$cli_array = $this->getGeneratrix()->getCliArray();
			return (isset($cli_array[0])) ? true : false;
		}

		public function base() { }

		public function help() { }

		public function addPage() {
			if(!$this->isCli())
				return;

			// Get the name of the required page
			$cli_array = $this->getGeneratrix()->getCliArray();
			if(isset($cli_array[2])) {

				$page_name = $cli_array[2];

				$not_allowed = array('generatrix');

				// Check if a controller or view of the page exists
				if(
					!in_array($page_name, $not_allowed) &&
					!file_exists(path('/app/controllers/' . $page_name . 'Controller.php')) &&
					!file_exists(path('/app/views/' . $page_name . 'View.php'))
				) {

					// Write the data
					$file = new File();
					$file->write(
						'/app/controllers/' . $page_name . 'Controller.php',
						str_replace('___PAGE_NAME___', $page_name, $file->read('/framework/data/addpage_new_controller.data'))
					);
					$file->write(
						'/app/views/' . $page_name . 'View.php',
						str_replace('___PAGE_NAME___', $page_name, $file->read('/framework/data/addpage_new_view.data'))
					);
					display('SUCCESSFULLY Added a controller and a view');
				} else {
					display_error('FAILED : A file with the view or controller by the same name already exists');
				}
			} else {
				display_error('FAILED : Please specify the page name as "php index.php generatrix addPage the-page-name"');
			}
		}

		public function exportDbStructure() {
			if (!$this->isCLI())
				return;

			exec('mysqldump ' . DATABASE_NAME . ' -u' . DATABASE_USER . ' -p' . DATABASE_PASS . ' -d > ' . path('/app/model/structures/' . DATABASE_NAME . '_' . time() . '.sql'), $output);
			$dump = implode("\n", $output);
			display($dump);
		}

		public function importDbStructure() {
			if (!$this->isCLI())
				return;

			$cli_array = $this->getGeneratrix()->getCliArray();
			$cli_2 = isset($cli_array[2]) ? $cli_array[2] : false;

			$structures = array();
			if ($handle = opendir(path("/app/model/structures/"))) {
				while(false !== ($entry = readdir($handle))) {
					if ($entry != "." && $entry != "..") {
						if (pathinfo(path("/app/model/structures/" . $entry), PATHINFO_EXTENSION) === "sql") {
							$structures[filemtime(path("/app/model/structures/" . $entry))] = $entry;
						}
					}
				}

				// Sort the structures
				ksort($structures);
			} else {
				display("Error: Couldn't open directory for reading");
				display("Make sure the app/model/structures directory exists & you have sufficient permissions");
			}

			if ($cli_2) {
				switch($cli_2) {
					case "view":
						if (count($structures) > 1) {
							display("Last modified		Filename");
							foreach($structures as $time => $filename) {
								display(date("d/m/Y G:i:s", $time) . "	" . $filename);
							}
						} else {
							display("No database structures found!");
						}
						break;
					default:
						// Import the specified sql file
						display("Importing the strucutre: " . $cli_2);
						$this->importDb("/" . $cli_2);
						break;
				}
			} else {
				// Import the latest structure
				$latest_structure = array_pop($structures);
				display("Importing the latest structure: " . $latest_structure);
				$this->importDb("/app/model/structures/" . $latest_structure);
			}
		}

		public function prepareModel() {
			if(!$this->isCli())
				return;

			$db = $this->getDb();

			if(file_exists(path('/app/model/databases.php'))) {
				display_error('FAILED : The databases file already exists, please delete it by running rm ' . path('/app/model/databases.php') . ' and run this query again to recreate the file');
				return;
			}

			$model_class_start = '
  class ___TABLE_CLASS_NAME___ extends Model {
    public function __construct($database) {
      $this->construct($database, "___TABLE_NAME___", array(' . "\n";
			$model_class_end = '
      ));
    }
  }' . "\n";

			$file_content = '';

			$tables = $db->query('SHOW TABLES');
			foreach($tables as $table) {
				$table_name = current($table);

				$class_start = str_replace('___TABLE_NAME___', $table_name, $model_class_start);
				$class_start = str_replace('___TABLE_CLASS_NAME___', str_replace(DATABASE_PREFIX, '', $table_name), $class_start);

				$file_content .= $class_start;

				$columns = $db->query('DESCRIBE ' . $table_name);
				$column_table = array();
				foreach($columns as $column) {
					$type = str_replace('(', '_', str_replace(')', '', $column['Type']));
					$spaces = explode(' ', $type);
					$type = $spaces[0];
					$underscores = explode('_', $type);
					switch($underscores[0]) {
						case 'int':
							$type = 'int';
							break;
						case 'varchar':
						default:
							break;
					}
					$column_table[] = '				"' . $column['Field'] . '" => "' . $type . '"';
				}
				$file_content .= implode(",\n", $column_table);

				$file_content .= $model_class_end;
			}

			$file_content = "<?php\n" . $file_content . "\n" . '?' . '>';
			$file = new File();
			$file->write(
				'/app/model/databases.php',
				$file_content
			);

			// Automatically create & save the structure
			$this->exportDbStructure();
		}

		public function exportDb() {
			if(!$this->isCli())
				return;

			exec('mysqldump ' . DATABASE_NAME . ' -u' . DATABASE_USER . ' -p' . DATABASE_PASS . ' > ' . path('/app/model/' . DATABASE_NAME . '.sql'), $output);
			$dump = implode("\n", $output);
			display($dump);
		}

		public function importDb($file = "") {
			if(!$this->isCli())
				return;

			if (!$file) {
				$file = '/app/model/' . DATABASE_NAME . '.sql';
			}

			exec('mysql -u' . DATABASE_USER . ' -p' . DATABASE_PASS . ' ' . DATABASE_NAME . ' < ' . path($file), $output);
			$dump = implode("\n", $output);
			display($dump);
		}

		public function packages() {
			if(!$this->isCli())
				return;

			$cli_array = $this->getGeneratrix()->getCliArray();
			$cli_2 = isset($cli_array[2]) ? $cli_array[2] : false;
			$cli_3 = isset($cli_array[3]) ? $cli_array[3] : false;
			if($cli_2) {
				$curl = new Curl();

				switch($cli_2) {
					// ./generatrix packages view
					case 'view':
						$data = $curl->get($this->server . '/packages', false);
						$packages = json_decode($data, true);
						foreach($packages as $package) {
							$user = $package['user'];
							$repo = $package['repo'];
							$desc = $package['description'];

							$packages_data = file_get_contents(path('/app/cache/packages.list'));
							$packages_list = unserialize($packages_data);

							echo (isset($packages_list[$user . ':' . $repo])) ? 'i' : 'a';
							echo "   " . str_pad($user . ':' . $repo, 40) . ' - ' . substr($desc, 0, 80) . "\n";
						}
						break;
					// ./generatrix packages anything_random
					case 'search':
						if(!$cli_3) {
							display('Please enter a search term');
							break;
						}

						$data = $curl->get($this->server . '/packages/search/' . urlencode($cli_3), false);
						$packages = json_decode($data, true);
						foreach($packages as $package) {
							$user = $package['user'];
							$repo = $package['repo'];
							$desc = $package['description'];

							$packages_data = file_get_contents(path('/app/cache/packages.list'));
							$packages_list = unserialize($packages_data);

							echo (isset($packages_list[$user . ':' . $repo])) ? 'i' : 'a';
							echo "   " . str_pad($user . ':' . $repo, 40) . ' - ' . substr($desc, 0, 80) . "\n";
						}
						break;
					default:
						display($cli_2 . ' is not a valid parameter');
						break;
				}
			} else {
				display("Please mention a paramter after packages, or type ./generatrix help");
			}
		}

		public function install() {
			if(!$this->isCli())
				return;

			$cli_array = $this->getGeneratrix()->getCliArray();
			$cli_2 = isset($cli_array[2]) ? $cli_array[2] : false;
			$cli_3 = isset($cli_array[3]) ? $cli_array[3] : false;

			$curl = new Curl();

			if(!$cli_2) {
				display(" - Please enter the name of the package to install eg. ./generatrix install vercingetorix:test");
			} else {

				$colons = explode(':', $cli_2);
				$user = $colons[0];
				$repo = isset($colons[1]) ? $colons[1] : false;

				if(!$repo || ($user == '') || ($repo == '')) {
					display(" - Please enter the name of the package to install as vercingetorix:test");
				} else {
					$json = $curl->get($this->server . '/packages/latest/' . $cli_2, false);
					$data = json_decode($json, true);
					if(isset($data['error']) && ($data['error'] == '')) {
						$url = $data['url'];
						$version = $data['version'];

						$packages_list = false;
						display(' + Getting list of installed packages');
						if(file_exists(path('/app/cache/packages.list')) && !is_dir(path('/app/cache/packages.list'))) {
							$packages_data = file_get_contents(path('/app/cache/packages.list'));
							$packages_list = unserialize($packages_data);
						}

						$user_repo = $user . ':' . $repo;

						if(
							isset($packages_list[$user_repo][$version]) &&
							(count($packages_list[$user_repo][$version]) > 0) &&
							$this->verifyFiles($packages_list[$user_repo][$version])
						) {
							display(' - This package has already been installed');
							return;
						}

						$file_count = file_exists(path('/app/packages/' . $user . '#' . $repo)) ? shell_exec('find app/packages/' . $user . '#' . $repo . '/ | wc -l') : 0;

						$previous_versions = isset($packages_list[$user_repo]) ? array_keys($packages_list[$user_repo]) : array();
						$previous_version = (count($previous_versions) > 0) ? $previous_versions[count($previous_versions) - 1] : '';

						if(
							$previous_version &&
							(
								!$this->verifyFiles($packages_list[$user_repo][$previous_version]) ||
								($file_count != count($packages_list[$user_repo][$previous_version]))
							)
						) {

							if($file_count != count($packages_list[$user_repo][$previous_version])) {
								display(' - You have created some new files in this folder');
							}

							display(' - This package has been edited. Please remove the custom modifications to proceed');
							return;
						} else {

							$download_file_name = 'app/packages/' . $user . '#' . $repo . '#' . $version . '.tar.gz';
							$wget_cmd = 'wget -nv ' . $url . ' -O ' . $download_file_name;
							$wget_output = '';
							if(file_exists(path('/' . $download_file_name)) && !is_dir(path('/' . $download_file_name))) {
								display(' - Package has already been downloaded');
							} else {
								display(' + Downloading package from url : ' . $url);
								$wget_output = shell_exec($wget_cmd);
							}

							$curr_dir = getcwd();
							chdir(path('/app/packages'));

							shell_exec('rm -rf ' . $user . '#' . $repo);
							display(' + Removing old package if it still exists');

							$tar_cmd = 'tar xzvf ' . $user . '#' . $repo . '#' . $version . '.tar.gz';
							$tar_output = shell_exec($tar_cmd);
							display(' + Unzipping downloaded package');

							chdir($curr_dir);

							$lines = explode("\n", $tar_output);

							$files = array();
							$folder_name = isset($lines[0]) ? $lines[0] : false;
							if($folder_name && ($folder_name != '')) {
								foreach($lines as $line) {
									$changed_file_name = str_replace($folder_name, $user . '#' . $repo . '/', $line);
									if($changed_file_name != '') {
										$files[$changed_file_name] = is_dir(path('/app/packages/' . $line)) ? 0 : filesize(path('/app/packages/' . $line));
									}
								}
							}
							if(!file_exists(path('/app/packages/' . $user . '#' . $repo))) {
								rename(path('/app/packages/' . $folder_name), path('/app/packages/' . $user . '#' . $repo));
								display(' + The package has been successfully installed');
							} else {
								display(' - The package has already been installed');
								return;
							}

							$packages_list[$user_repo][$version] = $files;
							file_put_contents(path('/app/cache/packages.list'), serialize($packages_list));
							display(' + Updated the list of installed packages');

						}
					} else {
						if(isset($data['error'])) {
							display(' - ' . $data['error']);
						} else {
							display(' - Did not get a response from the server. Please try again later');
						}
					}
				}
			}

		}

		public function verifyFiles($list) {
			$return = true;
			foreach($list as $item => $size) {
				if(file_exists(path('/app/packages/' . $item)) && (filesize(path('/app/packages/' . $item)) == $size)) {

				} else {
					if(($size == 0) && is_dir(path('/app/packages/' . $item))) {

					} else {
						$return = false;
						display(' - The filesize of app/packages/' . $item . ' does not match');
					}
				}
			}
			return $return;
		}

		public function remove() {
			if(!$this->isCli())
				return;

			$cli_array = $this->getGeneratrix()->getCliArray();
			$cli_2 = isset($cli_array[2]) ? $cli_array[2] : false;
			$cli_3 = isset($cli_array[3]) ? $cli_array[3] : false;

			$curl = new Curl();

			if(!$cli_2) {
				display(" - Please enter the name of the package to remove eg. ./generatrix remove vercingetorix:test");
			} else {

				$colons = explode(':', $cli_2);
				$user = $colons[0];
				$repo = isset($colons[1]) ? $colons[1] : false;

				if(!$repo || ($user == '') || ($repo == '')) {
					display(" - Please enter the name of the package to remove as vercingetorix:test");
				} else {
					$packages_list = false;
					display(' + Getting list of installed packages');
					if(file_exists(path('/app/cache/packages.list')) && !is_dir(path('/app/cache/packages.list'))) {
						$packages_data = file_get_contents(path('/app/cache/packages.list'));
						$packages_list = unserialize($packages_data);
					}

					if(isset($packages_list[$user . ':' . $repo])) {
						unset($packages_list[$user . ':' . $repo]);
						array_values($packages_list);

						shell_exec('rm -rf app/packages/' . $user . '#' . $repo);
						shell_exec('rm -rf app/packages/' . $user . '#' . $repo .'#*.tar.gz');
						file_put_contents(path('/app/cache/packages.list'), serialize($packages_list));
						display(' + The package has been successfully removed');
					} else {
						display(' - This package has not been installed');
					}
				}
			}
		}


	}

	//
	// The Generatrix View for utils
	//

	class generatrixView extends View {
		public function base() {
			display("
The most commonly used functions are:

  help                          - shows this help screen
  addPage test                  - adds a new controller testController and view testView with base functions
  prepareModel                  - creates the model file based on your database structure
  exportDb                      - exports the complete database
  importDb                      - imports the exported database
  exportDbStructure             - export the database structure
  importDbStructure             - import the latest available structure
  importDbStructure view        - gets a list of all available structures
  importDbStructure [path]      - import the specified structure file
  packages view                 - gets a list of all available packages on github.com
  packages details user:repo    - gets a list of all available packages on github.com
  packages search test          - searches for a particular package on github.com
  install user:repo             - installs the latest tagged version from github.com
  remove user:repo              - removes the latest version of the repo from your machine
			");
		}
		public function help() { $this->base(); }
		public function addPage() { }
		public function prepareModel() { }
		public function exportDbStructure() { }
		public function importDbStructure() { }
		public function exportDb() { }
		public function importDb() { }
		public function packages() { }
		public function install() { }
		public function remove() { }
	}

?>

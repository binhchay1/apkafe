<?php

if ( !class_exists('Lasso_Puc_v4p4_Autoloader', false) ):

	class Lasso_Puc_v4p4_Autoloader {
		private $prefix = '';
		private $rootDir = '';
		private $libraryDir = '';

		private $staticMap;

		public function __construct() {
			$this->rootDir = dirname(__FILE__) . '/';
			$nameParts = explode('_', __CLASS__, 4);
			$this->prefix = $nameParts[0] . '_' . $nameParts[1] . '_' . $nameParts[2] . '_';

			$this->libraryDir = realpath($this->rootDir . '../..') . '/';
			$this->staticMap = array(
				'LassoPucReadmeParser' => 'vendor/readme-parser.php',
				'LassoParsedown' => 'vendor/ParsedownLegacy.php',
			);
			if ( version_compare(PHP_VERSION, '5.3.0', '>=') ) {
				$this->staticMap['LassoParsedown'] = 'vendor/Parsedown.php';
			}

			spl_autoload_register(array($this, 'autoload'));
		}

		public function autoload($className) {
			if ( isset($this->staticMap[$className]) && file_exists($this->libraryDir . $this->staticMap[$className]) ) {
				/** @noinspection PhpIncludeInspection */
				include ($this->libraryDir . $this->staticMap[$className]);
				return;
			}

			if (strpos($className, $this->prefix) === 0) {
				$path = substr($className, strlen($this->prefix));
				$path = str_replace('_', '/', $path);
				$path = $this->rootDir . $path . '.php';

				if (file_exists($path)) {
					/** @noinspection PhpIncludeInspection */
					include $path;
				}
			}
		}
	}

endif;
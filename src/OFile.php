<?php declare(strict_types=1);

namespace Osumi\OsumiFramework\Plugins;

/**
 * Class with static functions to manage files (copy, rename, delete, zip...)
 */
class OFile {
	/**
	 * Copy a file from a source to a destination
	 *
	 * @param string $source Source path of a file
	 *
	 * @param string $destination Destination path of a file
	 *
	 * @param bool File gets copied or not
	 */
	public static function copy(string $source, string $destination): bool {
		return copy($source, $destination);
	}

	/**
	 * Rename or move a file from a source to a destination
	 *
	 * @param string $source Source path of a file
	 *
	 * @param string $destination Destination path of a file or new name
	 *
	 * @return bool File gets moved/renamed or not
	 */
	public static function rename(string $old_name, string $new_name): bool {
		return rename($old_name, $new_name);
	}

	/**
	 * Delete a file
	 *
	 * @param string $source Path of a file
	 *
	 * @return bool File gets deleted or not
	 */
	public static function delete(string $name): bool {
		return unlink($name);
	}

	/**
	 * Delete a folder and all of its content recursively
	 *
	 * @param string $dir Source path of a directory
	 *
	 * @return bool Folder gets deleted or not
	 */
	public static function rrmdir(string $dir): bool {
		$files = array_diff(scandir($dir), ['.','..']);
		foreach ($files as $file) {
			if (is_dir($dir.'/'.$file)) {
				self::rrmdir($dir.'/'.$file);
			}
			else {
				unlink($dir.'/'.$file);
			}
		}
		return rmdir($dir);
	}

	private $zip_file = null;

	/**
	 * Adds dir to the zip file to be created
	 *
	 * @param string $location Base path to be added
	 *
	 * @param string $name Name of the file to be created
	 *
	 * @return void
	 */
	private function addDir(string $location, string $name): void {
		$this->zip_file->addEmptyDir($name);
		$this->addDirDo($location, $name);
	}

	/**
	 * Adds folder and all of its files to the zip file to be created
	 *
	 * @param string $location Base path to be added
	 *
	 * @param string $name Name of the file to be created
	 *
	 * @return void
	 */
	private function addDirDo(string $location, string $name): void {
    $location .= '/';
		$name .= '/';
		$dir = opendir($location);
		while ($file = readdir($dir)) {
			if ($file == '.' || $file == '..') {
        continue;
      }
			if (filetype($location.$file) == 'dir') {
				$this->addDir($location.$file, $name.$file);
			}
			else {
				$this->zip_file->addFile($location.$file, $name.$file);
			}
		}
	}

	/**
	 * Create a new zip file
	 *
	 * @param string $route Path to be added
	 *
	 * @param string $zip_route Path of the new zip file
	 *
	 * @param string $basename Base path of the folder to be added
	 *
	 * @return void
	 */
	public function zip($route, $zip_route, $basename=null) {
		if (file_exists($zip_route)) {
			unlink($zip_route);
		}

		$this->zip_file = new \ZipArchive();
		$this->zip_file->open($zip_route, \ZipArchive::CREATE);
		$this->addDir($route, is_null($basename) ? basename($route) : $basename);
		$this->zip_file->close();
	}
}

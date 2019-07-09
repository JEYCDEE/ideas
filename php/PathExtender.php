<?php

namespace App;

class PathExtender
{

	const DS = DIRECTORY_SEPARATOR;

	/**
	 * @var array
	 */
	public $errors = [];

	/**
	 * @var bool
	 */
	public $hasErrors = false;

	/**
	 * Get absolute path.
	 *
	 * @param string $relativePath
	 * @param string $baseDir
	 *
	 * @return string
	 *
	 * @throws \Exception
	 */
	public function getAbsolutePath(string $relativePath, string $baseDir = '/'): string
	{

		/**
		 * @var string $currentDir
		 */
		$currentDir = getcwd();

		try {

			chdir($baseDir);

			$this->addBeginningIfNeeded($relativePath);

			if (!realpath($relativePath)) {

				$this->errors[] = "Incorrect relative path: '{$relativePath}'";

				return '';

			}

			return realpath($relativePath);

		} catch (\Exception $e) {

			$this->errors[] = $e->getMessage();

			throw $e;

		} finally {

			chdir($currentDir);

		}

	}

	/**
	 * Add some necessary symbols at the beginning for relative path to avoid possible errors.
	 *
	 * @param string $relativePath
	 *
	 * @return void
	 */
	public function addBeginningIfNeeded(string &$relativePath)
	{

		if (substr($relativePath, 0, 2) == '.' . self::DS) {

			return;

		}

		if (substr($relativePath, 0, 1) == self::DS) {

			$relativePath = '.' . $relativePath;

		} else
		if (substr($relativePath, 0, 1) == '.' &&
		    substr($relativePath, 1, 1) != self::DS) {

			$relativePath = '.' . self::DS . substr($relativePath, 1);

		} else
		if (substr($relativePath, 0, 1) != '.' &&
		    substr($relativePath, 1, 1) != self::DS) {

			$relativePath = '.' . self::DS . $relativePath;

		}
	}

}

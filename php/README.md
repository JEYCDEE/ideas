@ Author  : Volodymyr Mon
@ License : MIT

Hope I can help someone someday.
All my new PHP related ideas will be pushed here.

- PathExtender.php:
  - A simple class will help you to get absolute path.
  - Usage:
	- $pathExtender = new PathExtender;
	- $absolutePath = $pathExtender->getAbsolutePath($relativePath, $baseDir);
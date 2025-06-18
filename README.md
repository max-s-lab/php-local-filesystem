<h1 align="center">
  PHP Local Filesystem Library
</h1>

![php 7.4+](https://img.shields.io/badge/php-min%207.4.0-787CB5.svg)
![Code Coverage Badge](./tests/coverage/badge.svg)
![PHPStan Level 10](https://img.shields.io/badge/PHPStan-level%2010-brightgreen)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](https://github.com/max-s-lab/php-local-filesystem/blob/master/LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/max-s-lab/php-local-filesystem.svg)](https://packagist.org/packages/max-s-lab/php-local-filesystem)

## Installation

Run command in console:

```cmd
php composer.phar require max-s-lab/php-local-filesystem
```

or add next line to the `require` section of your `composer.json` file:

#### PHP 7.4

```json
"max-s-lab/php-local-filesystem": "^1.0"
```

#### PHP 8+

```json
"max-s-lab/php-local-filesystem": "^2.0"
```

## Usage

### Initializing

#### Base

You just need to set the root directory and start using:

```php
use MaxSLab\Filesystem\Local\LocalFilesystem;

$filesystem = new LocalFilesystem('/var/www/some-directory');
```

#### Advantage

You can also set default permissions for nested directories and files.

> Note: permissions MUST be set in octal mode.

Example:

```php
$filesystem = new LocalFilesystem('/var/www/some-directory', [
    'defaultPermissions' => [
        'directory' => 0755,
        'file' => 0644,
    ],
]);
```

### Writing to a file

Writing content to a file with the creation of a file and a directory for it.

Example:

```php
$filesystem->writeToFile('file.txt', 'Test');
```

This method also allows you to set permissions for directories and file:

```php
$filesystem->writeToFile('file.txt', 'Test', [
    'directoryPermissions' => 0777,
    'filePermissions' => 0666,
]);
```

You can also use this method to write a stream to a file.
To do this, simply replace `'Test'` with a stream.

See [file_put_contents](https://www.php.net/manual/en/function.file-put-contents.php) for more information about `flags`

### Deleting a file

Example:

```php
$filesystem->deleteFile('file.txt');
```

### Copying a file

Copying a file with creating a directory for it.

Example:

```php
$filesystem->copyFile('file.txt', 'directory/file.txt');
```

This method also allows you to set permissions for directories and file:

```php
$filesystem->copyFile('file.txt', 'directory/file.txt', [
    'directoryPermissions' => 0777,
    'filePermissions' => 0666,
]);
```

### Moving a file

Moving a file with creating a directory for it.

Example:

```php
$filesystem->moveFile('file.txt', 'directory/file.txt');
```

This method also allows you to set permissions for directories and file:

```php
$filesystem->moveFile('file.txt', 'directory/file.txt', [
    'directoryPermissions' => 0777,
    'filePermissions' => 0666,
]);
```

### Basic reading file

This method will return the contents of the file as a string.

Example:

```php
$result = $filesystem->readFile('file.txt');
```

See [file_get_contents](https://www.php.net/manual/en/function.file-get-contents.php) for more information.

### Streaming file reading

This method will return the contents of the file as a stream.

Example:

```php
$result = $filesystem->readFileAsStream('file.txt');
```

See [fopen](https://www.php.net/manual/en/function.fopen.php) for more information.

### Getting file params

#### Getting the file size

Example:

```php
$result = $filesystem->getFileSize('file.txt');
```

See [filesize](https://www.php.net/manual/en/function.filesize.php) for more information.

#### Detect MIME Content-type for a file

Example:

```php
$result = $filesystem->getFileMimeType('file.txt');
```

See [mime_content_type](https://www.php.net/manual/en/function.mime-content-type.php) for more information.

#### Getting the file modification time

Example:

```php
$result = $filesystem->getFileLastModifiedTime('file.txt');
```

See [filemtime](https://www.php.net/manual/en/function.filemtime.php) for more information.

### Creating a directory

This method creates a directory recursively.

Example:

```php
$filesystem->createDirectory('directory');
```

It also allows you to set permissions for the created directories:

```php
$filesystem->createDirectory('directory', 0777);
```

### Deleting a directory

Recursively deleting a directory along with the contained files and directories.

Example:

```php
$filesystem->deleteDirectory('directory');
```

### Preparing full path

Preparing full path by relative path.

Example:

```php
// File
$result = $filesystem->prepareFullPath('file.txt');

// Directory
$result = $filesystem->prepareFullPath('directory');
```

### Pathnames listing

Example:

```php
$result = filesystem->listPathnames('*');
```

See [glob](https://www.php.net/manual/en/function.glob.php) for more information.

### Setting up permissions

Example:

```php
// File
$filesystem->setPermissions('file.txt', 0644);

// Directory
$filesystem->setPermissions('directory', 0755);
```

See [chmod](https://www.php.net/manual/en/function.chmod.php) for more information.

### Get permissions

Example:

```php
// File
$result = $filesystem->getPermissions('file.txt');

// Directory
$result = $filesystem->getPermissions('directory');
```

See [fileperms](https://www.php.net/manual/en/function.fileperms.php) for more information.

### Checking the existence of a file

Example:

```php
$result = $filesystem->fileExists('file.txt');
```

See [is_file](https://www.php.net/manual/en/function.is-file.php) for more information.

### Checking the existence of a directory

Example:

```php
$result = $filesystem->directoryExists('directory');
```

See [is_dir](https://www.php.net/manual/en/function.is-dir.php) for more information.


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

You just need to set the root directory and start using.

```php
use MaxSLab\Filesystem\Local\LocalFilesystem;

$filesystem = new LocalFilesystem('/var/www/some-directory');
```

#### Advantage

You can also set default permissions for nested directories and files.

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

```php
$filesystem->writeToFile('file.txt', 'Test');
```

This method also allows you to set permissions for directories and file.
If you do not specify them, the default settings will be used.

```php
$filesystem->writeToFile('file.txt', 'Test', [
    'directoryPermissions' => 0777,
    'filePermissions' => 0666,
]);
```

You can also use this method to write a stream to a file.
To do this, simply replace `'Test'` with a stream.

### Deleting a file

```php
$filesystem->deleteFile('file.txt');
```

### Copying a file

Copying a file with creating a directory for it.

```php
$filesystem->copyFile('file.txt', 'directory/file.txt');
```

This method also allows you to set permissions for directories and file.
If you do not specify them, the default settings will be used.

```php
$filesystem->copyFile('file.txt', 'directory/file.txt', [
    'directoryPermissions' => 0777,
    'filePermissions' => 0666,
]);
```

### Moving a file

Moving a file with creating a directory for it.

```php
$filesystem->moveFile('file.txt', 'directory/file.txt');
```

This method also allows you to set permissions for directories and file.
If you do not specify them, the default settings will be used.

```php
$filesystem->moveFile('file.txt', 'directory/file.txt', [
    'directoryPermissions' => 0777,
    'filePermissions' => 0666,
]);
```

### Basic reading file

This method will return the contents of the file as a string.

```php
$result = $filesystem->readFile('file.txt');
```

### Streaming file reading

This method will return the contents of the file as a stream.

```php
$result = $filesystem->readFileAsStream('file.txt');
```

### Getting file params

#### Size

This method returns the file size in bytes.

```php
$result = $filesystem->getFileSize('file.txt');
```

#### MIME type

```php
$result = $filesystem->getFileMimeType('file.txt');
```

#### Last modified time

```php
$result = $filesystem->getFileLastModifiedTime('file.txt');
```

### Creating directory

This method creates a directory recursively.

```php
$filesystem->createDirectory('directory');
```

It also allows you to set permissions for the created directories.

```php
$filesystem->createDirectory('directory', 0777);
```

### Deleting directory

Recursively deleting a directory along with the contained files and directories.

```php
$filesystem->deleteDirectory('directory');
```

### Preparing full path

```php
$result = $filesystem->prepareFullPath('file.txt');
```

```php
$result = $filesystem->prepareFullPath('directory');
```

### Listing pathnames

```php
$result = filesystem->listPathnames('*');
```

For more information, see [glob](https://www.php.net/manual/ru/function.glob.php).

### Set permissions

```php
$filesystem->setPermissions('file.txt', 0644);
```

```php
$filesystem->setPermissions('directory', 0755);
```

### Get permissions

```php
$result = $filesystem->getPermissions('file.txt');
```

```php
$result = $filesystem->getPermissions('directory');
```

For more information about the returned values, see [fileperms](https://www.php.net/manual/ru/function.fileperms.php).

### Check existing methods

```php
$result = $filesystem->fileExists('file.txt');
```

```php
$result = $filesystem->directoryExists('directory');
```

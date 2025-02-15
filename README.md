<h1 align="center">
  MaxSLab PHP Local Filesystem Library 
</h1>

![php 7.4+](https://img.shields.io/badge/php-min%207.4.0-blue.svg)
![Code Coverage Badge](./tests/coverage/badge.svg)
![PHPStan Level 9](https://img.shields.io/badge/PHPStan-level%209-brightgreen)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](https://github.com/max-s-lab/php-local-filesystem/blob/master/LICENSE)

## Installation
Run
```
$ php composer.phar require max-s-lab/php-local-filesystem
```

or add

```
"max-s-lab/php-local-filesystem": "^1.0"
```

to the ```require``` section of your `composer.json` file.

## Usage

### Initializing
#### Base
You just need to set the root directory and start using.
```
use MaxSLab\Filesystem\Local\LocalFilesystem;

$filesystem = new LocalFilesystem('/var/www/some-directory');
```
#### Advantage
You can also set default permissions for nested directories and files.
```
$filesystem = new LocalFilesystem('/var/www/some-directory', [
    'defaultPermissions' => [
        'directory' => 0755,
        'file' => 0644,
    ],
]);
```

### Writing to a file
Writing content to a file with the creation of a file and a directory for it.
```
$filesystem->writeToFile('file.txt', 'Test');
```

This method also allows you to set permissions for directories and file. 
If you do not specify them, the default settings will be used.
```
$filesystem->writeToFile('file.txt', 'Test', [
    'directoryPermissions' => 0777,
    'filePermissions' => 0666,
]);
```

You can also use this method to write a stream to a file. 
To do this, simply replace ``'Test'`` with a stream.

### Deleting a file
```
$filesystem->deleteFile('file.txt');
```

### Copying a file
Copying a file with creating a directory for it.
```
$filesystem->copyFile('file.txt', 'directory/file.txt');
```

This method also allows you to set permissions for directories and file. 
If you do not specify them, the default settings will be used.
```
$filesystem->copyFile('file.txt', 'directory/file.txt', [
    'directoryPermissions' => 0777,
    'filePermissions' => 0666,
]);
```

### Moving a file
Moving a file with creating a directory for it.
```
$filesystem->moveFile('file.txt', 'directory/file.txt');
```

This method also allows you to set permissions for directories and file. 
If you do not specify them, the default settings will be used.
```
$filesystem->moveFile('file.txt', 'directory/file.txt', [
    'directoryPermissions' => 0777,
    'filePermissions' => 0666,
]);
```

### Basic reading file
This method will return the contents of the file as a string.
```
$result = $filesystem->readFile('file.txt');
```

### Streaming file reading
This method will return the contents of the file as a stream.
```
$result = $filesystem->readFileAsStream('file.txt');
```

### Getting file params
#### Size
This method returns the file size in bytes.
```
$result = $filesystem->getFileSize('file.txt');
```

#### MIME type
```
$result = $filesystem->getFileMimeType('file.txt');
```

#### Last modified time
```
$result = $filesystem->getFileLastModifiedTime('file.txt');
```

### Creating directory
This method creates a directory recursively.
```
$filesystem->createDirectory('directory');
```

It also allows you to set permissions for the created directories.
```
$filesystem->createDirectory('directory', 0777);
```

### Deleting directory
Recursively deleting a directory along with the contained files and directories.
```
$filesystem->deleteDirectory('directory');
```

### Preparing full path
```
$result = $filesystem->prepareFullPath('file.txt');
```

```
$result = $filesystem->prepareFullPath('directory');
```

### Listing pathnames
```
$result = filesystem->listPathnames('*');
```
For more information, see <a href="https://www.php.net/manual/ru/function.glob.php">glob</a>.

### Set permissions
```
$filesystem->setPermissions('file.txt', 0644);
```
```
$filesystem->setPermissions('directory', 0755);
```

### Get permissions
```
$result = $filesystem->getPermissions('file.txt');
```
```
$result = $filesystem->getPermissions('directory');
```
For more information about the returned values, see <a href="https://www.php.net/manual/ru/function.fileperms.php">fileperms</a>.

### Check existing methods
```
$result = $filesystem->fileExists('file.txt');
```
```
$result = $filesystem->directoryExists('directory');
```

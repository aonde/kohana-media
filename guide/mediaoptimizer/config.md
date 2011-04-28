# Configuration

The default config file is located in `MODPATH/media/config/media.php`.  You should copy this file to `APPPATH/config/media.php` and make changes there, in keeping with the [cascading filesystem](../kohana/files).

Configuration file contains an array of configuration groups. The structure of each configuration group, called an "instance", looks like this:

    string INSTANCE_NAME => array(
        'path'   => string MEDIA_STORAGE_PATH,
        'driver' => string OBJECT_DRIVER_NAME,
        'type'   => string ALLOWED_FILE_EXTENSION,
        'min'    => boolean MEDIA_FILE_MINIMIZATION,
        'merge'  => boolean MEDIA_FILE_MERGING,
        'cache'  => boolean MEDIA_FILE_CACHING,
    ),

Understanding each of these settings is important.

INSTANCE_NAME
:  Connections can be named anything you want, but we recommend to name in accordance with the type of static content. For example, js or css.

MEDIA_STORAGE_PATH
:  Path to the folder, that store all static content of that type. For example, `APPPATH.media/js/`. It's very important that at the end of this parameter was `DIRECTORY_SEPARATOR`.

OBJECT_DRIVER_NAME
:  Specific driver name for media files processing.

ALLOWED_FILE_EXTENSION
:  Extension of allowed file types.

MEDIA_FILE_MINIMIZATION
:  Enables of media files minimization.

MEDIA_FILE_MERGING
:  Enables of media files merging.

MEDIA_FILE_MERGING
:  Enables of merged and minimized media files caching.

## Example

The example file below shows 2 MySQL connections, one local and one remote.

    return array
    (
      // Javascript processing
      'js' => array
      (
        'path'   => APPPATH.'media'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR,
        'driver' => 'Media_Javascript',
        'type'   => 'js',
        'min'    => TRUE,
        'merge'  => TRUE,
        'cache'  => Kohana::$caching
      ),

      // Css processing
      'css' => array
      (
        'path'   => APPPATH.'media'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR,
        'driver' => 'Media_Css',
        'types'  => 'css',
        'min'    => TRUE,
        'merge'  => TRUE,
        'cache'  => Kohana::$caching
      ),
    );

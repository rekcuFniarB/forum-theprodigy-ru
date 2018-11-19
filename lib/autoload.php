<?php
/**
 * Classes autoloader.
 *
 * After registering this autoload function with SPL, the following line
 * would cause the function to attempt to load the \Baz\Qux class
 * from /path/to/project/lib/Baz/Qux.php:
 *
 *      new \Foo\Bar\Baz\Qux;
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
spl_autoload_register(function ($class) {

    //// project-specific namespace prefix
    //$prefix = 'Foo\\Bar\\';

    //// base directory for the namespace prefix
    $base_dir = __DIR__ . '/';
    
    //// Alt dir:
    $path_parts = explode('\\', $class);
    $base_dir_alt = __DIR__ . "/{$path_parts[0]}/src/";
    
    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $class) . '.php';
    $file_alt = $base_dir_alt . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file))
        error_log("__LOAD___: $file");
    //else
        //error_log("__DEBUG__: NOT EXIST: $file");
    
    //// if the file exists, require it
    if (file_exists($file)) {
        require_once $file;
    }
    elseif (file_exists($file_alt)) {
        require_once $file_alt;
    }
});


?>

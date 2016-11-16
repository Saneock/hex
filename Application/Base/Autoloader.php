<?php
namespace Hex\Base;

/**
 * An example of a general-purpose implementation that includes the optional
 * functionality of allowing multiple base directories for a single namespace
 * prefix.
 *
 * Given a foo-bar package of classes in the file system at the following
 * paths ...
 *
 *     /path/to/packages/foo-bar/
 *         src/
 *             Baz.php             # Foo\Bar\Baz
 *             Qux/
 *                 Quux.php        # Foo\Bar\Qux\Quux
 *         tests/
 *             BazTest.php         # Foo\Bar\BazTest
 *             Qux/
 *                 QuuxTest.php    # Foo\Bar\Qux\QuuxTest
 *
 * ... add the path to the class files for the \Foo\Bar\ namespace prefix
 * as follows:
 *
 *      <?php
 *      // instantiate the loader
 *      $loader = new \Base\Autoloader;
 *
 *      // register the autoloader
 *      $loader->register();
 *
 *      // register the base directories for the namespace prefix
 *      $loader->addNamespace('Foo\Bar', '/path/to/packages/foo-bar/src');
 *      $loader->addNamespace('Foo\Bar', '/path/to/packages/foo-bar/tests');
 *
 * The following line would cause the autoloader to attempt to load the
 * \Foo\Bar\Qux\Quux class from /path/to/packages/foo-bar/src/Qux/Quux.php:
 *
 *      <?php
 *      new \Foo\Bar\Qux\Quux;
 *
 * The following line would cause the autoloader to attempt to load the
 * \Foo\Bar\Qux\QuuxTest class from /path/to/packages/foo-bar/tests/Qux/QuuxTest.php:
 *
 *      <?php
 *      new \Foo\Bar\Qux\QuuxTest;
 */
class Autoloader
{
    /**
     * @var Autoloader
     */
    protected static $instance;

    public $_use_overrides = true;

    /**
     * An associative array where the key is a namespace prefix and the value
     * is an array of base directories for classes in that namespace.
     *
     * @var array
     */
    protected $prefixes = array();

    /**
     * File where classes index is stored
     */
    const INDEX_FILE = 'cache/class_index.php';

    /**
     * @var string Root directory
     */
    protected $root_dir;

    /**
     *  @var array array('classname' => 'path/to/override', 'classnamecore' => 'path/to/class/core')
     */
    public $index = array();

    public $_include_override_path = true;

    protected static $class_aliases = array();

	/**
     * Register loader with base directories for the namespace prefix
     *
     * @return void
     */
    protected function __construct($register = true)
    {
        if ($this->_use_overrides) {
            // Set root directory
            $this->root_dir = ROOT.'/';

            // Check index file
            $file = $this->normalizeDirectory(ROOT).self::INDEX_FILE;
            if (is_readable($file) and filemtime($file) and ENVIRONMENT !== 'development') {
                $this->index = include($file);
            } else {
                $this->generateIndex();
            }
        }

        // Register the autoloader
		$this->register();

		// Register the base directories for the namespace prefix

        // Base CMS packages
		$this->addNamespace('Hex', DIR_APP);
		$this->addNamespace('Exception', DIR_APP.'/Exceptions');
		$this->addNamespace('Interfaces', DIR_APP.'/Interfaces');
		$this->addNamespace('Abstracts', DIR_APP.'/Abstracts');
		$this->addNamespace('web', DIR_SECTIONS);
    }

    /**
     * Get instance of Autoloader (singleton)
     *
     * @return Autoloader
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Register loader with SPL autoloader stack.
     *
     * @return void
     */
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    /**
     * Adds a base directory for a namespace prefix.
     *
     * @param string $prefix The namespace prefix.
     * @param string $base_dir A base directory for class files in the
     * namespace.
     * @param bool $prepend If true, prepend the base directory to the stack
     * instead of appending it; this causes it to be searched first rather
     * than last.
     * @return void
     */
    public function addNamespace($prefix, $base_dir, $prepend = false)
    {
        // normalize namespace prefix
        $prefix = trim($prefix, '\\') . '\\';

        // normalize the base directory with a trailing separator
        $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR) . '/';

        // initialize the namespace prefix array
        if (isset($this->prefixes[$prefix]) === false) {
            $this->prefixes[$prefix] = array();
        }

        // retain the base directory for the namespace prefix
        if ($prepend) {
            array_unshift($this->prefixes[$prefix], $base_dir);
        } else {
            array_push($this->prefixes[$prefix], $base_dir);
        }
    }

    /**
     * Loads the class file for a given class name.
     *
     * @param string $class The fully-qualified class name.
     * @return mixed The mapped file name on success, or boolean false on
     * failure.
     */
    public function loadClass($classname)
    {
        if ($this->_use_overrides == false) {
            // the current namespace prefix
            $prefix = $classname;

            // work backwards through the namespace names of the fully-qualified
            // class name to find a mapped file name
            while (false !== $pos = strrpos($prefix, '\\')) {

                // retain the trailing namespace separator in the prefix
                $prefix = substr($classname, 0, $pos + 1);

                // the rest is the relative class name
                $relative_class = substr($classname, $pos + 1);

                // try to load a mapped file for the prefix and relative class
                $mapped_file = $this->loadMappedFile($prefix, $relative_class);
                if ($mapped_file) {
                    return $mapped_file;
                }

                // remove the trailing namespace separator for the next iteration
                // of strrpos()
                $prefix = rtrim($prefix, '\\');
            }

            if(!isset($relative_class))
                return false;

            $file = ROOT
                . '/'
                . $prefix
                . '/'
                . str_replace('\\', '/', $relative_class)
                . '.php';

            // if the mapped file exists, require it
            if ($this->requireFile($file)) {
                // yes, we're done
                return $file;
            }

            // didn't found mapped file
            return false;
        }
        
        
        // If use overrides

        // Retrocompatibility
        if (isset(self::$class_aliases[$classname]) && !interface_exists($classname, false) && !class_exists($classname, false)) {
            return eval('class '.$classname.' extends '.self::$class_aliases[$classname].' {}');
        }

        // Regenerate the class index if the requested file doesn't exists
        if ((isset($this->index[$classname]) && $this->index[$classname]['path'] && !is_file($this->root_dir.$this->index[$classname]['path']))
            || (isset($this->index[$classname.'Core']) && $this->index[$classname.'Core']['path'] && !is_file($this->root_dir.$this->index[$classname.'Core']['path']))) {
            $this->generateIndex();
        }

        // If $classname has not core suffix (E.g. Shop, Product)
        if (substr($classname, -4) != 'Core') {
            $class_dir = (isset($this->index[$classname]['override'])
                && $this->index[$classname]['override'] === true) ? $this->normalizeDirectory(ROOT) : $this->root_dir;

            // If requested class does not exist, load associated core class
            if (isset($this->index[$classname]) && !$this->index[$classname]['path']) {
                $file = $class_dir.$this->index[$classname.'Core']['path'];
                require_once($file);

                if ($this->index[$classname.'Core']['type'] != 'interface') {
                    eval('namespace '.$this->index[$classname]['namespace'].'; '.$this->index[$classname.'Core']['type'].' '.$this->index[$classname]['class'].' extends \\'.$classname.'Core {}');
                }
                
                return $file;
            } else {
                // request a non Core Class load the associated Core class if exists
                if (isset($this->index[$classname.'Core'])) {
                    $file = $this->root_dir.$this->index[$classname.'Core']['path'];
                    require_once($file);
                }

                if (isset($this->index[$classname])) {
                    $file = $class_dir.$this->index[$classname]['path'];
                    if ($this->requireFile($file)) {
                        return $file;
                    }
                }

                if (isset($file))
                    return $file;
                else 
                    return false;
            }
        }
        // Call directly ProductCore, ShopCore class
        elseif (isset($this->index[$classname]['path']) && $this->index[$classname]['path']) {
            $file = $this->root_dir.$this->index[$classname]['path'];
            if ($this->requireFile($file)) {
                 return $file;
            }
        }
        
        return false;
    }

    /**
     * Load the mapped file for a namespace prefix and relative class.
     *
     * @param string $prefix The namespace prefix.
     * @param string $relative_class The relative class name.
     * @return mixed Boolean false if no mapped file can be loaded, or the
     * name of the mapped file that was loaded.
     */
    protected function loadMappedFile($prefix, $relative_class)
    {
        // are there any base directories for this namespace prefix?
        if (isset($this->prefixes[$prefix]) === false) {
            return false;
        }

        // look through base directories for this namespace prefix
        foreach ($this->prefixes[$prefix] as $base_dir) {

            // replace the namespace prefix with the base directory,
            // replace namespace separators with directory separators
            // in the relative class name, append with .php
            $file = $base_dir
                  . str_replace('\\', '/', $relative_class)
                  . '.php';

            // if the mapped file exists, require it
            if ($this->requireFile($file)) {
                // yes, we're done
                return $file;
            }
        }

        // never found it
        return false;
    }

    /**
     * Generate classes index
     */
    public function generateIndex()
    {
        $classes = array_merge(
            $this->getClassesFromDir('Application/'),
            $this->getClassesFromDir('sections/')
        );

        if ($this->_include_override_path) {
            $classes = array_merge(
                $classes,
                $this->getClassesFromDir('overrides/Application/', true)
            );
        }

        ksort($classes);
        $content = '<?php return '.var_export($classes, true).'; ?>';

        // Write classes index on disc to cache it
        $filename = $this->normalizeDirectory(ROOT).self::INDEX_FILE;
        $filename_tmp = tempnam(dirname($filename), basename($filename.'.'));

        if ($filename_tmp !== false && file_put_contents($filename_tmp, $content) !== false) {
            if(file_exists($filename_tmp)){
                if (!rename($filename_tmp, $filename)) {
                    unlink($filename_tmp);
                } else {
                    chmod($filename, 0666);
                }
            }
        }
        // $filename_tmp couldn't be written. $filename should be there anyway (even if outdated), no need to die.
        else {
            Logger::logError('Cannot write temporary file '.$filename_tmp);
        }
        $this->index = $classes;
    }

    /**
     * Retrieve recursively all classes in a directory and its subdirectories
     *
     * @param string $path Relativ path from root to the directory
     * @return array
     */
    protected function getClassesFromDir($path, $host_mode = false)
    {
        $classes = array();
        $root_dir = $host_mode ? $this->normalizeDirectory(ROOT) : $this->root_dir;

        foreach (scandir($root_dir.$path) as $file) {
            if ($file[0] != '.') {
                if (is_dir($root_dir.$path.$file)) {
                    $classes = array_merge($classes, $this->getClassesFromDir($path.$file.'/', $host_mode));
                } elseif (substr($file, -4) == '.php') {
                    $content = file_get_contents($root_dir.$path.$file);

                    $namespacePattern = '[\\a-z0-9_]*[\\]';
                    $pattern = '#\W((abstract\s+)?class|interface)\s+(?P<classname>'.basename($file, '.php').'(?:Core)?)'
                                .'(?:\s+extends\s+'.$namespacePattern.'[a-z][a-z0-9_]*)?(?:\s+implements\s+'.$namespacePattern.'[a-z][\\a-z0-9_]*(?:\s*,\s*'.$namespacePattern.'[a-z][\\a-z0-9_]*)*)?\s*\{#i';

                    if (preg_match($pattern, $content, $m)) {
                        // Get namespace if set global
                        if (preg_match('/namespace ([\\a-z0-9_]+?);/i', $content, $nm)) {
                            $namespace = $nm[1];
                        } else {
                            $namespace = '';
                        }

                        $classes[$namespace.'\\'.$m['classname']] = array(
                            'class' => $m['classname'],
                            'namespace' => $namespace,
                            'path' => $path.$file,
                            'type' => trim($m[1]),
                            'override' => $host_mode
                        );

                        if (substr($m['classname'], -4) == 'Core') {
                            $classes[$namespace.'\\'.substr($m['classname'], 0, -4)] = array(
                                'class' => substr($m['classname'], 0, -4),
                                'namespace' => $namespace,
                                'path' => '',
                                'type' => $classes[$namespace.'\\'.$m['classname']]['type'],
                                'override' => $host_mode
                            );
                        }
                    }
                }
            }
        }

        return $classes;
    }

    /**
     * If a file exists, require it from the file system.
     *
     * @param string $file The file to require.
     * @return bool True if the file exists, false if not.
     */
    protected function requireFile($file)
    {
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }

    /**
     * Get class path from index file
     *
     * @param string $classname Class name
     */
    public function getClassPath($classname)
    {
        return (isset($this->index[$classname]) && isset($this->index[$classname]['path'])) ? $this->index[$classname]['path'] : null;
    }

    private function normalizeDirectory($directory)
    {
        return rtrim($directory, '/\\').DIRECTORY_SEPARATOR;
    }



    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }
}
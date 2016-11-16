<?php
namespace Abstracts;

abstract class Request extends \Hex\Base\Object
{
    private $_scriptFile;

    /**
     * Resolves the current request into a route and the associated parameters.
     * @return array the first element is the route, and the second is the associated parameters.
     */
    abstract public function resolve();

    /**
     * Returns entry script file path.
     *
     * @return string entry script file path (processed w/ realpath())
     * @throws InvalidConfigException if the entry script file path cannot be determined automatically.
     */
    public function getScriptFile()
    {
        if ($this->_scriptFile === null) {
            if (isset($_SERVER['SCRIPT_FILENAME'])) {
                $this->setScriptFile($_SERVER['SCRIPT_FILENAME']);
            } else {
                throw new \Exception\InvalidConfig('Unable to determine the entry script file path.');
            }
        }

        return $this->_scriptFile;
    }

    /**
     * Sets the entry script file path.
     * The entry script file path can normally be determined based on the `SCRIPT_FILENAME` SERVER variable.
     * However, for some server configurations, this may not be correct or feasible.
     * This setter is provided so that the entry script file path can be manually specified.
     *
     * @param string $value the entry script file path. This can be either a file path or a path alias.
     * @throws InvalidConfigException if the provided entry script file path is invalid.
     */
    public function setScriptFile($value)
    {
        $scriptFile = realpath($value);
        if ($scriptFile !== false && is_file($scriptFile)) {
            $this->_scriptFile = $scriptFile;
        } else {
            throw new InvalidConfigException('Unable to determine the entry script file path.');
        }
    }
}
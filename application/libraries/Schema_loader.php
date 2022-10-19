<?php

use Opis\JsonSchema\ISchemaLoader;
use Opis\JsonSchema\Schema;

class Schema_loader implements ISchemaLoader
{
    /** @var string[] */
    protected $map = [];

    /** @var Schema[] */
    protected $loaded = [];

    /**
     * @inheritdoc
     */
    public function loadSchema(string $uri)
    {
        // Check if already loaded
        if (isset($this->loaded[$uri])) {
            return $this->loaded[$uri];
        }
        
        // Check the mapping
        foreach ($this->map as $prefix => $dir) {
            if (strpos($uri, $prefix) === 0) {
                // We have a match
                $path = substr($uri, strlen($prefix) + 1);
                $path = $dir . '/' . ltrim($path, '/');

                if (file_exists($path)) {
                    // Create a schema object
                    $schema = Schema::fromJsonString(file_get_contents($path));
                    // Save it for reuse
                    $this->loaded[$uri] = $schema;

                    return $schema;
                }
            }
        }

        // Nothing found
        return null;
    }

    /**
     * @param string $dir
     * @param string $uri_prefix
     * @return bool
     */
    public function registerPath(string $dir, string $uri_prefix): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $uri_prefix = rtrim($uri_prefix, '/');
        $dir = rtrim($dir, '/');

        $this->map[$uri_prefix] = $dir;
        return true;
    }
}

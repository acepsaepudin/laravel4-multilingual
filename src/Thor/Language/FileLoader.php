<?php

namespace Thor\Language;

class FileLoader extends \Illuminate\Translation\FileLoader
{

    /**
     * Load a local namespaced translation group for overrides.
     *
     * @param  array  $lines
     * @param  string  $locale
     * @param  string  $group
     * @param  string  $namespace
     * @return array
     */
    protected function loadNamespaceOverrides(array $lines, $locale, $group, $namespace)
    {
        $files = array(
            // packages/{package}/{locale} folder structure
            $this->path . "/packages/{$namespace}/{$locale}/{$group}.php",
            // original Laravel folder structure:
            $this->path . "/packages/{$locale}/{$namespace}/{$group}.php"
        );

        foreach($files as $file) {
            if($this->files->exists($file)) {
                return array_replace_recursive($lines, $this->files->getRequire($file));
            }
        }
        return $lines;
    }

}

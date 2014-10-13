<?php

require_once(__DIR__ . '/Config.php');
require_once(__DIR__ . '/GeneratorBase.php');

class CodeIDEGenerator extends GeneratorBase
{
    public function execute($srcFilesDir, $destDir)
    {
        $templateDir = dirname(__DIR__) . DS . 'template' . DS;
        $templatePath = $templateDir . 'codeide_module_html.php';

        $modules = $this->modules;
        $extra_modules = array();

        foreach ($modules as $keym => $module)
        {
            $moduleName = $module['moduleName'];
            $functions = $module['tags']['functions'];
            if (!empty($functions))
            {
                $moduleNamePrefix = "{$moduleName}.";
                $moduleNamePrefixLen = strlen($moduleNamePrefix);
                foreach ($functions as $key => $function)
                {
                    $parent = $moduleName;
                    $functionName = $function['name'];
                    if (substr($functionName, 0, $moduleNamePrefixLen) == $moduleNamePrefix)
                    {
                        $functionName = substr($functionName, $moduleNamePrefixLen);
                        $function['enable'] = true;
                    }
                    else
                    {
                        $pos = strpos($functionName, '.');
                        if ($pos != false)
                        {
                            $parent = substr($functionName, 0, $pos);
                            $functionName = substr($functionName, $pos + 1);
                        }
                        else
                        {
                            $pos = strpos($functionName, ':');
                            if ($pos != false)
                            {
                                $parent = substr($functionName, 0, $pos);
                                $functionName = substr($functionName, $pos + 1);
                            }
                            else
                            {
                                $parent = 'global';
                            }
                        }

                        if (!isset($extra_modules[$parent])) 
                        {
                            $extra_modules[$parent] = array();
                            $extra_modules[$parent]['functions'] = array();
                        }
                        $function['name'] = $functionName;
                        $function['enable'] = true;
                        array_push($extra_modules[$parent]['functions'], $function);
                        $function['enable'] = false;
                    }
                    $functions[$key] = $function;
                }
                $modules[$keym]['tags']['functions'] = $functions;
            }
        }

        $indexFilename = '';
        foreach ($modules as $key => $module)
        {
            $module['outputFilename'] = $this->getModuleFilename($module['moduleName'], '.lua');
            $module['outputPath'] = $this->getModulePath($destDir, $module['moduleName'], '.lua');
            if (empty($indexFilename))
            {
                $indexFilename = $module['outputFilename'];
            }
            $modules[$key] = $module;
        }

        foreach ($modules as $key => $module)
        {
            $moduleName = $module['moduleName'];
            $functions = $module['tags']['functions']; // for template

            printf("process module %s ... ", $moduleName);
            ob_start();
            require($templatePath);
            $contents = ob_get_clean();
            print("ok\n");
            file_put_contents($module['outputPath'], $contents);
        }

        foreach ($extra_modules as $key => $module)
        {
            $moduleName = $key;
            $functions = $module['functions'];
            $module['outputFilename'] = $this->getModuleFilename($moduleName, '.lua');
            $module['outputPath'] = $this->getModulePath($destDir, $moduleName, '.lua');

            printf("process module %s ... ", $moduleName);
            ob_start();
            require($templatePath);
            $contents = ob_get_clean();
            print("ok\n");
            file_put_contents($module['outputPath'], $contents);
        }

        print("ok\n");
    }
}

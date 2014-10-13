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

        print("ok\n");
    }
}

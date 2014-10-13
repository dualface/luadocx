<?php

require_once(__DIR__ . '/Config.php');
require_once(__DIR__ . '/GeneratorBase.php');

class LocalHTMLGenerator extends GeneratorBase
{
    public function execute($srcFilesDir, $destDir)
    {
        $templateDir = dirname(__DIR__) . DS . 'template' . DS;
        $templatePath = $templateDir . 'apidoc_module_html.php';

        $modules = $this->modules;
        $indexFilename = '';
        foreach ($modules as $key => $module)
        {
            if ($module['moduleName'] == $this->config->indexModule)
            {
                $module['outputFilename'] = $this->getModuleFilename('index', '.html');
                $module['outputPath'] = $this->getModulePath($destDir, 'index', '.html');
                $indexFilename = $module['outputFilename'];
            }
            else
            {
                $module['outputFilename'] = $this->getModuleFilename($module['moduleName'], '.html');
                $module['outputPath'] = $this->getModulePath($destDir, $module['moduleName'], '.html');
                if (empty($indexFilename))
                {
                    $indexFilename = $module['outputFilename'];
                }
            }
            $modules[$key] = $module;
        }

        foreach ($modules as $key => $module)
        {
            $moduleName = $module['moduleName'];
            $module['doc'] = file_get_contents($srcFilesDir . DS . $module['filename']);
            $functions = $module['functions'];

            foreach ($functions as $offset => $fn)
            {
                $functions[$offset]['doc'] = file_get_contents($srcFilesDir . DS . $fn['filename']);
            }

            printf("process module %s ... ", $moduleName);
            ob_start();
            require($templatePath);
            $contents = ob_get_clean();
            print("ok\n");
            file_put_contents($module['outputPath'], $contents);
        }

        print("copy assets ... ");
        $this->copyFile($templateDir, $destDir, 'luadocx-highlight.min.js');
        $this->copyFile($templateDir, $destDir, 'luadocx-style.css');
        $this->copyFile($templateDir, $destDir, 'luadocx-style-monokai.css');
        print("ok\n");
    }
}

<?php

error_reporting(E_ALL);
date_default_timezone_set('Asia/Chongqing');

define('LUADOCX_VERSION', '1.4');
define('DS', DIRECTORY_SEPARATOR);

function help()
{
    print <<<EOT

LuaDocX - Generate documents from Lua source files


-   extract tags (module, class, function) from Lua source files. write to JSON file.

    luadocx extract -c config_file_path source_files_dir json_files_dir


-   generate offline HTML documents:

    luadocx generate -c config_file_path -t localhtml json_files_dir html_files_dir


-   generate Cocos Code IDE auto complete database:

    luadocx generate -c config_file_path -t codeide json_files_dir output_files_dir


EOT;
}

if (!isset($argc) || $argc < 5)
{
    help();
    return 1;
}

array_shift($argv);
$params = array(
    'command' => '',
    'configFilePath' => '',
    'generateType' => 'localhtml',
    'srcFilesDir' => '',
    'destDir' => '',
);
while ($arg = array_shift($argv))
{
    if ($arg == '-c')
    {
        $params['configFilePath'] = array_shift($argv);
        continue;
    }

    if ($arg == '-t')
    {
        $params['generateType'] = strtolower(array_shift($argv));
        continue;
    }

    if ($arg[0] == '-')
    {
        printf("\nERROR: invalid option %s\n", $arg);
        help();
        return 1;
    }

    if (empty($params['command']))
    {
        $params['command'] = $arg;
        continue;
    }
    if (empty($params['srcFilesDir']))
    {
        $params['srcFilesDir'] = $arg;
        continue;
    }
    if (empty($params['destDir']))
    {
        $params['destDir'] = $arg;
    }
}


// check params
$commands = array('extract', 'generate');
if (!in_array($params['command'], $commands))
{
    printf("\nERROR: invalid command %s\n", $params['command']);
    help();
    return 1;
}

if (!is_file($params['configFilePath']))
{
    printf("\nERROR: invalid config file path %s\n", $params['configFilePath']);
    help();
    return 1;
}
$params['configFilePath'] = realpath($params['configFilePath']);

if (!is_dir($params['srcFilesDir']))
{
    printf("\nERROR: invalid srcFilesDir %s\n", $params['srcFilesDir']);
    help();
    return 1;
}
$params['srcFilesDir'] = realpath($params['srcFilesDir']);

if (!is_dir($params['destDir']))
{
    @mkdir($params['destDir']);
    if (!is_dir($params['destDir']))
    {
        printf("\nERROR: invalid destDir %s\n", $params['destDir']);
        help();
        return 1;
    }
}
$params['destDir'] = realpath($params['destDir']);


// execute
require_once(__DIR__ . '/inc/Config.php');

$config = new Config($params['configFilePath']);
if (!$config->isValid())
{
    printf("\nERROR: invalid config file %s\n", $params['configFilePath']);
    help();
    return 1;
}

if ($params['command'] == 'extract')
{
    require_once(__DIR__ . '/inc/DirScanner.php');
    $scanner = new DirScanner($config, $params);
    $modules = $scanner->execute();
    file_put_contents($params['destDir'] . DS . 'modules.json', json_encode($modules));

    require_once(__DIR__ . '/inc/MarkdownGenerator.php');
    $generator = new MarkdownGenerator($config, $modules);
    $generator->execute($params['destDir']);

    $structure = $generator->extractStructure();
    file_put_contents($params['destDir'] . DS . 'structure.json', json_encode($structure));
}
else if ($params['command'] == 'generate')
{
    switch ($params['generateType'])
    {
        case 'localhtml':
            $modules = json_decode(file_get_contents($params['srcFilesDir'] . DS . 'structure.json'), true);
            require_once(__DIR__ . '/inc/LocalHTMLGenerator.php');
            $generator = new LocalHTMLGenerator($config, $modules);
            return $generator->execute($params['srcFilesDir'], $params['destDir']);

        case 'codeide':
            $modules = json_decode(file_get_contents($params['srcFilesDir'] . DS . 'modules.json'), true);
            require_once(__DIR__ . '/inc/CodeIDEGenerator.php');
            $generator = new CodeIDEGenerator($config, $modules);
            return $generator->execute($params['srcFilesDir'], $params['destDir']);
    }

    printf("\nERROR: invalid generate type %s\n", $params['generateType']);
    help();
    return 1;
}

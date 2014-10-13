--------------------------------
-- @module <?php echo $moduleName; ?>


<?php
if (!empty($functions)):
    $moduleNamePrefix = "{$moduleName}.";
    $moduleNamePrefixLen = strlen($moduleNamePrefix);
    foreach ($functions as $function):
        if (!$function['enable']) 
        {
            continue;
        }
        $parent = $moduleName;
        $functionName = $function['name'];
        if (substr($functionName, 0, $moduleNamePrefixLen) == $moduleNamePrefix)
        {
            $functionName = substr($functionName, $moduleNamePrefixLen);
        }
        else
        {
            $pos = strpos($functionName, '.');
            if ($pos != false)
            {
                $parent = substr($functionName, 0, $pos);
                $functionName = substr($functionName, $pos + 1);
            }
        }

//        $functionDoc = str_replace("\n", "<br />\n-- ", $function['doc']);
        $functionDoc = $function['description'];

        echo <<<EOT
--------------------------------
-- {$functionDoc}
-- @function [parent=#{$parent}] {$functionName}

EOT;

        foreach ($function['tags'] as $tag):
            echo <<<EOT
-- @{$tag['name']} {$tag['value']}

EOT;
        endforeach;

        echo "\n";

    endforeach;
endif;

?>
return nil

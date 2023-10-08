<?php

$path = file_get_contents('path.json');

$folders = json_decode($path)->folders;
$exactPaths = json_decode($path)->exact_paths;

foreach ($folders as $folder) {
    $files = glob( $folder . '*.php');

    if (empty($files)) {
        echo "Ошибка пути {$folder} ";

        continue;
    }

    foreach ($files as $file) {
        reorderConstants($file);
    }
}

foreach ($exactPaths as $exactPath) {
    $file = glob($exactPath);

    if (empty($file)) {
        echo "Ошибка пути {$exactPath} ";

        continue;
    }

    $file = $file[0];


    reorderConstants($file);
}


/**
 * @param string $file
 * @return void
 */
function reorderConstants(string $file): void
{
    $content = file_get_contents($file);

    preg_match('/class (\w+)/', $content, $classNameMatches);

    if (empty($classNameMatches)) {
        return;
    }
    $className = $classNameMatches[1];

    preg_match_all('/public const (\w+) = ([0-9]+);/', $content, $matches, PREG_SET_ORDER);

    if (empty($matches)) {
        return;
    }

    $constants = [];

    foreach ($matches as $match) {
        $constants[$match[1]] = $match[2];
    }

    asort($constants);


    $content = preg_replace(
        '/class ' . preg_quote($className) . '[\s\S]+}/',
        generateNewContent(constants: $constants, className: $className),
        $content
    );

    file_put_contents($file, $content);
}

/**
 * @param array $constants
 * @param string $className
 * @return string
 */
function generateNewContent(array $constants, string $className): string
{
    $content = "class $className\n{\n";

    foreach ($constants as $constant => $value) {
        $content .= "    public const $constant = $value;\n";
    }

    $content .= "}\n";

    return $content;
}


echo PHP_EOL . 'Скрипт выполнен.' . PHP_EOL;
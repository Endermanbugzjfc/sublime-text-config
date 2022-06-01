<?php

declare(strict_types=1);

const AUTHOR = "Endermanbugzjfc";
const DEFAULT_LICENSE = "MIT";

function matchLicense(
    string $firstLine
): string {
    return match ($firstLine) {
        default => DEFAULT_LICENSE
    };
}

class StopWalkDirException extends Exception
{
}

function walkDir(
    string $path,
    callable $callback
) {
    try {
        if (
            $path[-1] !== "/"
            and
            $path[-1] !== "\\\\"
        ) {
            $path .= "/";
        }
        $dirs = scandir(
            $path
        );
        $dirs = array_diff(
            $dirs,
            [
                ".",
                ".."
            ]
        );
        foreach ($dirs as $dir) {
            $subPath = $path . $dir;
            if (is_dir(
                $subPath
            )) {
                walkDir(
                    $subPath,
                    $callback
                );
            } else {
                $callback($subPath);
            }
        }
    } catch (StopWalkDirException) {
    }
}

$path = realpath(
    "."
);
$author = AUTHOR;
$authorLower = strtolower(
    $author
);
$name = basename(
    $path
);
$nameLower = strtolower(
    $name
);

$plugin = false;
walkDir(
    $path,
    function (string $path) use (
        &
        $plugin
    ) {
        if (basename(
            $path
        ) === "plugin.yml") {
            $plugin = true;
            throw new StopWalkDirException();
        }
    }
);

$type = $plugin
    ? "project"
    : "library";
$requirePocketmine = $plugin
    ? <<<EOT
    "pocketmine/pocketmine-mp": "^4"
    EOT
    : "";

$license = DEFAULT_LICENSE;
$licenseFile = "./LICENSE";
if (file_exists(
    $licenseFile
)) {
    $licenseContent = file_get_contents(
        $licenseFile
    );
    $licenseExploded = explode(
        "\n",
        $licenseContent
    );
    $licenseFirstLine = $licenseExploded[0];
    $license = matchLicense(
        $licenseFirstLine
    );
}

$raw = <<<EOT
{
  "name": "$authorLower/$nameLower",
  "type": "$type",
  "license": "$license",
  "require": {
    $requirePocketmine
  },
  "require-dev": {
  },
  "autoload": {
    "psr-4": {
      "$author\\\\$name\\\\": "src/$name"
    }
  },
  "autoload-dev": {
    "psr-4": {
      "$author\\\\$name\\\\": [
        "tests/",
        "tools/"
      ]
    }
  }
}
EOT;

file_put_contents(
    "./composer.json",
    $raw
);

<?php

class GovnokodRuLanguageDecoder {
    public static $map = array(
        'PHP' => 'php',
        'JavaScript' => 'js',
        'SQL' => 'sql',
        'Perl' => 'perl',
        'Python' => 'py',
        'Си' => 'c',
        'C++' => 'cpp',
        'C#' => 'cs',
        'Java' => 'java',
        'Pascal' => 'pascal',
        'ActionScript' => 'actionscript',
        'Assembler' => 'asm',
        'VisualBasic' => 'vb',
        'Ruby' => 'ruby',
        '1C' => '1c',
        'bash' => 'bash',
        'Objective C' => 'objc',
        'Swift' =>  'swift',
        'Lua' => 'lua',
        'Haskell' => 'haskell',
    );

    /**
     * Parse language title into slug
     *
     * @param string $title
     *
     * @return mixed
     */
    public function decode($title) {
        if (isset(self::$map[$title])) {
            return self::$map[$title];
        }
        return GK_DEFAULT_LANGUAGE_SLUG;
    }
}
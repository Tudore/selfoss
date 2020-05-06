<?php

namespace Tests\Helpers;

use helpers\ImageUtils;
use PHPUnit\Framework\TestCase;

final class IconExtractorTest extends TestCase {
    /**
     * Apple touch icons are supported.
     */
    public function testAppleTouchIcon() {
        $page = <<<EOD
<html>
<head>
<link rel="apple-touch-icon" sizes="114x114" href="https://www.example.com/images/apple-touch-114x114.png">
</head>
<body>
</body>
</html>
EOD;

        $this->assertEquals(
            ImageUtils::parseShortcutIcons($page),
            [
                'https://www.example.com/images/apple-touch-114x114.png',
            ]
        );
    }

    /**
     * Apple touch precomposed icons are supported.
     */
    public function testAppleTouchPrecomposedIcon() {
        $page = <<<EOD
<html>
<head>
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="https://www.example.com/images/apple-touch-precomposed-114x114.png">
</head>
<body>
</body>
</html>
EOD;

        $this->assertEquals(
            ImageUtils::parseShortcutIcons($page),
            [
                'https://www.example.com/images/apple-touch-precomposed-114x114.png',
            ]
        );
    }

    /**
     * Apple touch icons without sizes are supported.
     */
    public function testAppleTouchWithoutSizesIcon() {
        $page = <<<EOD
<html>
<head>
<link rel="apple-touch-icon" href="https://www.example.com/images/apple-touch-114x114.png">
</head>
<body>
</body>
</html>
EOD;

        $this->assertEquals(
            ImageUtils::parseShortcutIcons($page),
            [
                'https://www.example.com/images/apple-touch-114x114.png',
            ]
        );
    }

    /**
     * Shortcut icons are supported.
     */
    public function testShortcutIcon() {
        $page = <<<EOD
<html>
<head>
<link rel="shortcut icon" href="https://www.example.com/favicon.ico">
</head>
<body>
</body>
</html>
EOD;

        $this->assertEquals(
            ImageUtils::parseShortcutIcons($page),
            [
                'https://www.example.com/favicon.ico',
            ]
        );
    }

    /**
     * Icons are supported.
     */
    public function testIcon() {
        $page = <<<EOD
<html>
<head>
<link rel="icon" href="https://www.example.com/favicon.ico">
</head>
<body>
</body>
</html>
EOD;

        $this->assertEquals(
            ImageUtils::parseShortcutIcons($page),
            [
                'https://www.example.com/favicon.ico',
            ]
        );
    }

    /**
     * Multiple icons are recognized.
     */
    public function testMultipleIcons() {
        $page = <<<EOD
<html>
<head>
<link rel="icon" href="/favicon.png" type="image/png">
<link rel="icon" href="/favicon.ico" type="image/vnd.microsoft.icon">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
</head>
<body>
</body>
</html>
EOD;

        $this->assertEquals(
            ImageUtils::parseShortcutIcons($page),
            [
                '/favicon.png',
                '/favicon.ico',
                '/favicon.svg',
            ]
        );
    }

    /**
     * Apple icons are prioritized over shortcut icons.
     */
    public function testAppleAndIcon() {
        $page = <<<EOD
<html>
<head>
<link rel="icon" href="/favicon.ico">
<link rel="apple-touch-icon" sizes="114x114" href="https://www.example.com/images/apple-touch-114x114.png">
</head>
<body>
</body>
</html>
EOD;

        $this->assertEquals(
            ImageUtils::parseShortcutIcons($page),
            [
                'https://www.example.com/images/apple-touch-114x114.png',
                '/favicon.ico',
            ]
        );
    }

    /**
     * Larger icons are prioritized over smaller ones.
     */
    public function testAppleAndPrecomposed() {
        $page = <<<EOD
<html>
<head>
<link rel="apple-touch-icon" sizes="144x144" href="https://www.example.com/images/apple-touch-114x114.png">
<link rel="apple-touch-icon-precomposed" sizes="87x87" href="https://www.example.com/images/apple-touch-precomposed-87x87.png">
</head>
<body>
</body>
</html>
EOD;

        $this->assertEquals(
            ImageUtils::parseShortcutIcons($page),
            [
                'https://www.example.com/images/apple-touch-114x114.png',
                'https://www.example.com/images/apple-touch-precomposed-87x87.png',
            ]
        );
    }

    /**
     * Apple precomposed icons are prioritized over normal touch icons.
     */
    public function testAppleAndPrecomposedWithoutSizes() {
        $page = <<<EOD
<html>
<head>
<link rel="apple-touch-icon" href="https://www.example.com/images/apple-touch-114x114.png">
<link rel="apple-touch-icon-precomposed" href="https://www.example.com/images/apple-touch-precomposed-114x114.png">
</head>
<body>
</body>
</html>
EOD;

        $this->assertEquals(
            ImageUtils::parseShortcutIcons($page),
            [
                'https://www.example.com/images/apple-touch-precomposed-114x114.png',
                'https://www.example.com/images/apple-touch-114x114.png',
            ]
        );
    }

    /**
     * Uglified websites with unquoted atttributes are handled correctly.
     */
    public function testUnquotedAttributes() {
        $page = <<<EOD
<html><head><link rel="shortcut icon" href=//www.example.com/favicons/favicon.ico><link rel=apple-touch-icon sizes=152x152 href=//www.example.com/favicons/apple-touch-icon-152x152.png><link rel=icon type=image/png href=//www.example.com/favicons/favicon-196x196.png sizes=196x196></head><body></body></html>
EOD;

        $this->assertEquals(
            ImageUtils::parseShortcutIcons($page),
            [
                '//www.example.com/favicons/favicon-196x196.png',
                '//www.example.com/favicons/apple-touch-icon-152x152.png',
                '//www.example.com/favicons/favicon.ico',
            ]
        );
    }

    /**
     * Null is returned when no icon found.
     */
    public function testMissingIcon() {
        $page = <<<EOD
<html>
<head>
</head>
<body>
</body>
</html>
EOD;

        $this->assertEquals(
            ImageUtils::parseShortcutIcons($page),
            []
        );
    }

    /**
     * Commented out icons are ignored.
     */
    public function testCommentIcon() {
        $page = <<<EOD
<html>
<head>
<!--<link rel="icon" href="https://www.example.com/favicon.ico">-->
</head>
<body>
</body>
</html>
EOD;

        $this->assertEquals(
            ImageUtils::parseShortcutIcons($page),
            []
        );
    }

    /**
     * Icons inside script elements are ignored.
     */
    public function testScriptIcon() {
        $page = <<<EOD
<html>
<head>
<script>
console.log('<link rel="icon" href="https://www.example.com/favicon.ico">');
</script>
</head>
<body>
</body>
</html>
EOD;

        $this->assertEquals(
            ImageUtils::parseShortcutIcons($page),
            []
        );
    }
}

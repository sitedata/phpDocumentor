<?php

declare(strict_types=1);

namespace phpDocumentor\Configuration;

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

use League\Uri\Uri;
use phpDocumentor\Path;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \phpDocumentor\Configuration\PathNormalizingMiddleware
 * @covers ::__invoke
 * @covers ::<private>
 */
final class PathNormalizingMiddlewareTest extends TestCase
{
    /** @var ConfigurationFactory */
    private $configurationFactory;

    protected function setUp(): void
    {
        $definition = new Definition\Version3('default');
        $this->configurationFactory = new ConfigurationFactory([], new SymfonyConfigFactory(['3' => $definition]));
    }

    public function testNoConfigUriLeavesConfigUnchanged(): void
    {
        $configuration = $this->givenAConfiguration();
        $middleware = new PathNormalizingMiddleware();
        $outputConfig = $middleware($configuration, null);

        self::assertEquals($configuration, $outputConfig);
    }

    /**
     * @dataProvider pathProvider
     */
    public function testNormalizedIgnoreToGlob(string $input, string $output): void
    {
        $configuration = $this->givenAConfiguration();
        $configuration['phpdocumentor']['versions']['1.0.0']->api[0]->setIgnore(['paths' => [$input]]);

        $middleware = new PathNormalizingMiddleware();
        $outputConfig = $middleware($configuration, Uri::createFromString('./config.xml'));

        self::assertEquals(
            [$output],
            $outputConfig['phpdocumentor']['versions']['1.0.0']->getApi()[0]['ignore']['paths']
        );
    }

    /**
     * @dataProvider cachePathProvider
     */
    public function testNormalizeCachePath(string $input, string $output, string $configPath): void
    {
        $configuration = $this->givenAConfiguration();
        $configuration['phpdocumentor']['paths']['cache'] = new Path($input);

        $middleware = new PathNormalizingMiddleware();
        $outputConfig = $middleware(
            $configuration,
            Uri::createFromString($configPath)
        );

        self::assertSame($output, (string) $outputConfig['phpdocumentor']['paths']['cache']);
    }

    public function testDsnResolvedByConfigPath(): void
    {
        $configuration = $this->givenAConfiguration();

        $middleware = new PathNormalizingMiddleware();

        $outputConfig = $middleware(
            $configuration,
            Uri::createFromString('/data/phpDocumentor/config.xml')
        );

        self::assertEquals(
            '/data/phpDocumentor/',
            (string) $outputConfig['phpdocumentor']['versions']['1.0.0']->api[0]['source']['dsn']
        );
    }

    public function cachePathProvider(): array
    {
        return [
            'Absolute paths are not normalized' => [
                '/opt/myProject',
                '/opt/myProject',
                '/data/phpdocumentor/config.xml',
            ],
            'Absolute windows paths are not normalized' => [
                'D:\opt\myProject',
                'D:\opt\myProject',
                '/data/phpdocumentor/config.xml',
            ],
            'Relative unix paths are changed to an absolute path with the config folder as prefix' => [
                '.phpdoc/cache',
                '/data/phpdocumentor/.phpdoc/cache',
                '/data/phpdocumentor/config.xml',
            ],
            'Relative paths on Windows are changed to an absolute path with the config folder as prefix' => [
                '.phpdoc\cache',
                'd:/data/phpdocumentor/.phpdoc/cache',
                'D:/data/phpdocumentor/config.xml',
            ],
        ];
    }

    public function pathProvider(): array
    {
        return [
            [
                'src',
                '/src/**/*',
            ],
            [
                '.',
                '/**/*',
            ],
            [
                './src',
                '/src/**/*',
            ],
            [
                '/src/*',
                '/src/*',
            ],
            [
                'src/dir/test.php',
                '/src/dir/test.php',
            ],
        ];
    }

    private function givenAConfiguration(): Configuration
    {
        return $this->configurationFactory->createDefault();
    }
}

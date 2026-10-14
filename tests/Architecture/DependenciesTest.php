<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class DependenciesTest extends TestCase
{
    public function test_domain_and_application_depend_only_inwards(): void
    {
        $root = dirname(__DIR__, 2).'/src/Library';
        foreach (['Domain', 'Application'] as $layer) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root.'/'.$layer));
            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') continue;
                $source = file_get_contents($file->getPathname());
                foreach (['Illuminate', 'Eloquent', 'Firebase', 'Library\\Adapters', 'Library\\Bootstrap'] as $forbidden) {
                    $this->assertStringNotContainsString($forbidden, $source, $file->getPathname());
                }
                if ($layer === 'Domain') {
                    $this->assertStringNotContainsString('Library\\Application', $source);
                }
            }
        }
    }

    public function test_adapters_are_constructed_only_in_composition(): void
    {
        $root = dirname(__DIR__, 2).'/src/Library';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        $adapterClasses = ['SystemClock', 'BcryptPasswordHasher', 'JwtTokens', 'EloquentUserRepository'];
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php' || $file->getFilename() === 'PortBindings.php') continue;
            $source = file_get_contents($file->getPathname());
            foreach ($adapterClasses as $adapter) {
                $this->assertDoesNotMatchRegularExpression('/new\\s+(?:[\\\\\\w]+\\\\)?'.preg_quote($adapter, '/').'\\s*\\(/', $source);
            }
        }
    }
}

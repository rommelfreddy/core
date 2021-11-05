<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Test\Script\Execution;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Script\Execution\ScriptExecutor;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\Framework\Test\App\AppSystemTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;

class ScriptExecutorTest extends TestCase
{
    use IntegrationTestBehaviour;
    use AppSystemTestBehaviour;

    private ScriptExecutor $executor;

    public function setUp(): void
    {
        Feature::skipTestIfInActive('FEATURE_NEXT_17441', $this);

        $this->executor = $this->getContainer()->get(ScriptExecutor::class);
    }

    /**
     * @dataProvider executeProvider
     */
    public function testExecute(array $hooks, array $expected): void
    {
        $this->loadAppsFromDir(__DIR__ . '/_fixtures');

        $object = new ArrayStruct();

        foreach ($hooks as $hook) {
            $this->executor->execute($hook, ['object' => $object]);
        }

        static::assertNotEmpty($expected);

        foreach ($expected as $key => $value) {
            static::assertTrue($object->has($key));
            static::assertEquals($value, $object->get($key));
        }
    }

    public function executeProvider()
    {
        yield 'Test simple function call' => [
            ['simple-function-case'],
            ['foo' => 'bar'],
        ];
        yield 'Test multiple scripts called' => [
            ['simple-function-case', 'multi-script-case'],
            ['foo' => 'bar', 'bar' => 'foo', 'baz' => 'foo'],
        ];
        yield 'Test include with function call' => [
            ['include-case'],
            ['called' => 1],
        ];
    }
}

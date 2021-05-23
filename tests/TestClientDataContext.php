<?php
use PHPUnit\Framework\TestCase;
use MostWebFramework\Client\Args;
use MostWebFramework\Client\DataContext;
use MostWebFramework\Client\DataModel;
use MostWebFramework\Client\DataService;

class TestClientDataContext extends TestCase
{
    public function testNotNull(): void
    {
        $this->expectException(Exception::class);
        Args::notNull(null, 'argument');
        Args::notNull(true, 'argument');
    }

    public function testNotEmpty(): void
    {
        $this->expectException(Exception::class);
        Args::notEmpty('', 'argument');
        Args::notNull('string', 'argument');
    }

    public function testNotString(): void
    {
        $this->expectException(Exception::class);
        Args::notString(true, 'argument');
        Args::notNull('string', 'argument');
    }

    /**
     * @testdox should create instance
     */
    public function testCreateInstance(): void
    {
        $context = new DataContext("http://localhost:4000/api/");
        $this->assertNotNull($context);
        $this->assertInstanceOf(DataService::class, $context->getService());
        $this->assertEquals($context->getService()->getBase(), "http://localhost:4000/api/");
    }

    /**
     * @testdox should get model
     */
    public function testGetModel(): void
    {
        $context = new DataContext("http://localhost:4000/api/");
        $model = $context->model('Users');
        $this->assertNotNull($model);
        $this->assertInstanceOf(DataModel::class, $model);
    }
}
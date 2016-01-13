<?php

namespace SampleApp\Service;

class SampleServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testSayHello()
    {
        $sample_service = new SampleService();

        $this->assertEquals('Hello, welcome to Dietcube.', $sample_service->sayHello());
    }
}

<?php

namespace Faker\Test\Provider\it_IT;

use Faker\Generator;
use Faker\Provider\it_IT\Person;
use PHPUnit\Framework\TestCase;

class PersonTest extends TestCase
{
    /**
     * @var Generator
     */
    private $faker;

    protected function setUp(): void
    {
        $faker = new Generator();
        $faker->addProvider(new Person($faker));
        $this->faker = $faker;
    }

    public function testIfTaxIdCanReturnData()
    {
        $taxId = $this->faker->taxId();
        $this->assertRegExp('/^[a-zA-Z]{6}[0-9]{2}[a-zA-Z][0-9]{2}[a-zA-Z][0-9]{3}[a-zA-Z]$/', $taxId);
    }
}

<?php

namespace Tests\Feature\Admin;

use Tests\Feature\Concerns\ActsAsAdmin;
use Tests\TestCase;

abstract class AdminApiTestCase extends TestCase
{
    use ActsAsAdmin;
}

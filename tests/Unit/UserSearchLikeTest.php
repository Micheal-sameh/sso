<?php

namespace Tests\Unit;

use App\Repositories\Eloquent\UserRepository;
use ReflectionMethod;
use Tests\TestCase;

class UserSearchLikeTest extends TestCase
{
    public function test_like_wildcards_in_search_are_escaped(): void
    {
        $method = new ReflectionMethod(UserRepository::class, 'likeTerm');
        $repo = new UserRepository;

        // input: 50%_a\b  ->  every %, _ and \ gets a backslash in front
        $this->assertSame('%50\\%\\_a\\\\b%', $method->invoke($repo, '50%_a\\b'));
        $this->assertSame('%plain%', $method->invoke($repo, 'plain'));
    }
}

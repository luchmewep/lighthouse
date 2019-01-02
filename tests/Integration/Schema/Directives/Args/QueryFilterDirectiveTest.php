<?php

namespace Tests\Integration\Schema\Directives\Args;

use Tests\DBTestCase;
use Illuminate\Support\Arr;
use Tests\Utils\Models\User;
use Illuminate\Database\Eloquent\Collection;

class QueryFilterDirectiveTest extends DBTestCase
{
    /** @var Collection|User[] */
    protected $users;

    /**
     * Set up test environment.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->users = factory(User::class, 5)->create();
    }

    /**
     * @test
     */
    public function itCanAttachEqFilterToQuery()
    {
        $this->schema = '
        type User {
            id: ID!
            name: String
            email: String
        }
        
        type Query {
            users(id: ID @eq): [User!]! @paginate(model: "Tests\\\Utils\\\Models\\\User")
        }
        ';
        $query = '
        {
            users(count: 5 id: '.$this->users->first()->getKey().') {
                data {
                    id
                }
            }
        }
        ';
        $result = $this->query($query);

        $this->assertCount(1, Arr::get($result, 'data.users.data'));
    }

    /**
     * @test
     */
    public function itCanAttachNeqFilterToQuery()
    {
        $this->schema = '
        type User {
            id: ID!
            name: String
            email: String
        }
        
        type Query {
            users(id: ID @neq): [User!]! @paginate(model: "Tests\\\Utils\\\Models\\\User")
        }
        ';
        $query = '
        {
            users(count: 5 id: '.$this->users->first()->getKey().') {
                data {
                    id
                }
            }
        }
        ';
        $result = $this->query($query);

        $this->assertCount(4, Arr::get($result->data, 'users.data'));
    }

    /**
     * @test
     */
    public function itCanAttachInFilterToQuery()
    {
        $this->schema = '
        type User {
            id: ID!
            name: String
            email: String
        }
        
        type Query {
            users(include: [Int] @in(key: "id")): [User!]!
                @paginate(model: "Tests\\\Utils\\\Models\\\User")
        }
        ';

        $user1 = $this->users->first()->getKey();
        $user2 = $this->users->last()->getKey();
        $query = '
        {
            users(count: 5 include: ['.$user1.', '.$user2.']) {
                data {
                    id
                }
            }
        }
        ';
        $result = $this->query($query);

        $this->assertCount(2, Arr::get($result, 'data.users.data'));
    }

    /**
     * @test
     */
    public function itCanAttachNotInFilterToQuery()
    {
        $this->schema = '
        type User {
            id: ID!
            name: String
            email: String
        }
        
        type Query {
            users(exclude: [Int] @notIn(key: "id")): [User!]!
                @paginate(model: "Tests\\\Utils\\\Models\\\User")
        }
        ';

        $user1 = $this->users->first()->getKey();
        $user2 = $this->users->last()->getKey();
        $query = '
        {
            users(count: 5 exclude: ['.$user1.', '.$user2.']) {
                data {
                    id
                }
            }
        }
        ';
        $result = $this->query($query);

        $this->assertCount(3, Arr::get($result->data, 'users.data'));
    }

    /**
     * @test
     */
    public function itCanAttachWhereFilterToQuery()
    {
        $this->schema = '
        type User {
            id: ID!
            name: String
            email: String
        }
        
        type Query {
            users(id: Int @where(operator: ">")): [User!]!
                @paginate(model: "Tests\\\Utils\\\Models\\\User")
        }
        ';

        $user1 = $this->users->first()->getKey();
        $query = '
        {
            users(count: 5 id: '.$user1.') {
                data {
                    id
                }
            }
        }
        ';

        $result = $this->query($query);
        $this->assertCount(4, Arr::get($result->data, 'users.data'));
    }

    /**
     * @test
     */
    public function itCanAttachTwoWhereFilterWithTheSameKeyToQuery()
    {
        $this->schema = '
        type User {
            id: ID!
            name: String
            email: String
        }
        
        type Query {
            users(
                start: Int @where(key: "id", operator: ">")
                end: Int @where(key: "id", operator: "<")
            ): [User!]! @paginate
        }
        ';

        $user1 = $this->users->first()->getKey();
        $user2 = $this->users->last()->getKey();
        $query = '
        {
            users(count: 5 start: '.$user1.' end: '.$user2.') {
                data {
                    id
                }
            }
        }
        ';

        $result = $this->query($query);
        $this->assertCount(3, Arr::get($result, 'data.users.data'));
    }

    /**
     * @test
     */
    public function itCanAttachWhereBetweenFilterToQuery()
    {
        $this->schema = '
        type User {
            id: ID!
            name: String
            email: String
        }
        
        type Query {
            users(
                start: String! @whereBetween(key: "created_at")
                end: String! @whereBetween(key: "created_at")
            ): [User!]! @paginate(model: "Tests\\\Utils\\\Models\\\User")
        }
        ';

        $user = $this->users[0];
        $user->created_at = now()->subDay();
        $user->save();

        $user = $this->users[1];
        $user->created_at = now()->subDay();
        $user->save();

        $start = now()->subDay()->startOfDay()->format('Y-m-d H:i:s');
        $end = now()->subDay()->endOfDay()->format('Y-m-d H:i:s');

        $query = '
        {
            users(count: 5 start: "'.$start.'" end: "'.$end.'") {
                data {
                    id
                }
            }
        }
        ';
        $result = $this->query($query);

        $this->assertCount(2, Arr::get($result->data, 'users.data'));
    }

    /**
     * @test
     */
    public function itCanAttachWhereNotBetweenFilterToQuery()
    {
        $this->schema = '
        type User {
            id: ID!
            name: String
            email: String
        }
        
        type Query {
            users(
                start: String! @whereNotBetween(key: "created_at")
                end: String! @whereNotBetween(key: "created_at")
            ): [User!]! @paginate(model: "Tests\\\Utils\\\Models\\\User")
        }
        ';

        $user = $this->users[0];
        $user->created_at = now()->subDay();
        $user->save();

        $user = $this->users[1];
        $user->created_at = now()->subDay();
        $user->save();

        $start = now()->subDay()->startOfDay()->format('Y-m-d H:i:s');
        $end = now()->subDay()->endOfDay()->format('Y-m-d H:i:s');

        $query = '
        {
            users(count: 5 start: "'.$start.'" end: "'.$end.'") {
                data {
                    id
                }
            }
        }
        ';
        $result = $this->query($query);

        $this->assertCount(3, Arr::get($result->data, 'users.data'));
    }

    /**
     * @test
     */
    public function itCanAttachWhereClauseFilterToQuery()
    {
        $this->schema = '
        type User {
            id: ID!
            name: String
            email: String
        }
        
        type Query {
            users(created_at: String! @where(clause: "whereYear")): [User!]!
                @paginate(model: "Tests\\\Utils\\\Models\\\User")
        }
        ';

        $user = $this->users[0];
        $user->created_at = now()->subYear();
        $user->save();

        $user = $this->users[1];
        $user->created_at = now()->subYear();
        $user->save();

        $year = now()->subYear()->format('Y');

        $query = '
        {
            users(count: 5 created_at: "'.$year.'") {
                data {
                    id
                }
            }
        }
        ';

        $result = $this->query($query);
        $this->assertCount(2, Arr::get($result->data, 'users.data'));
    }

    /**
     * @test
     */
    public function itOnlyProcessesFilledArguments()
    {
        $this->schema = '
        type User {
            id: ID!
            name: String
            email: String
        }
        
        type Query {
            users(id: ID @eq, name: String @where(operator: "like")): [User!]!
                @paginate(model: "Tests\\\Utils\\\Models\\\User")
        }
        ';
        $query = '
        {
            users(count: 5 name: "'.$this->users->first()->name.'") {
                data {
                    id
                }
            }
        }
        ';
        $result = $this->query($query);

        $this->assertCount(1, Arr::get($result->data, 'users.data'));
    }
}

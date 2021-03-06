<?php
declare(strict_types=1);

namespace Tests\Unit\app\Repositories;

use App\Entities\Contracts\FolderInterface;
use App\Repositories\FolderRepository;
use App\Repositories\Contracts\FolderRepositoryInterface;
use App\Repositories\Exceptions\FolderNotFoundException;
use App\Repositories\Filters\Contracts\FilterInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mockery;
use Tests\Unit\TestCase;

// use Illuminate\Foundation\Testing\WithFaker;
// use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class FolderRepositoryTest
 *
 * @package Tests\Unit\app\Repositories
 */
final class FolderRepositoryTest extends TestCase
{
    /**
     * test instance of.
     *
     * @return void
     */
    public function testInstanceOf()
    {
        $repository = app(FolderRepository::class);

        $this->assertInstanceOf(FolderRepositoryInterface::class, $repository);
    }

    /**
     * test construct method and props.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testConstruct()
    {
        /** @var Mockery\Mock|Builder $query */
        $query = Mockery::mock(Builder::class);

        /** @var Mockery\Mock|FolderInterface $eloquent */
        $eloquent = Mockery::mock(FolderInterface::class);
        $eloquent->shouldReceive('newQuery')->once()->with()->andReturn($query);

        $repository = new FolderRepository($eloquent);

        $eloquentRef = $this->getHiddenProperty($repository, 'eloquent');
        $queryRef = $this->getHiddenProperty($repository, 'query');

        $this->assertSame($eloquent, $eloquentRef->getValue($repository));
        $this->assertSame($query, $queryRef->getValue($repository));
    }

    /**
     * test resetQuery method.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testResetQuery()
    {
        // expected
        $expectedQuery = 'new query';

        /** @var Mockery\Mock|FolderInterface $eloquent */
        $eloquent = Mockery::mock(FolderInterface::class);
        // construct 及び resetQuery で各々 newQuery が呼ばれるので twice
        $eloquent->shouldReceive('newQuery')->twice()->with()->andReturn($expectedQuery);

        $repository = new FolderRepository($eloquent);

        $this->assertSame($repository, $repository->resetQuery());

        $queryRef = $this->getHiddenProperty($repository, 'query');
        $this->assertSame($expectedQuery, $queryRef->getValue($repository));
    }

    /**
     * test orderBy method.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testOrderBy()
    {
        /** @var Mockery\Mock|Builder $builder */
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('orderBy')->once()->with('updated_at');

        /** @var Mockery\Mock|FolderInterface $eloquent */
        $eloquent = Mockery::mock(FolderInterface::class);
        $eloquent->shouldReceive('newQuery')->once()->with()->andReturn($builder);

        /** @var Mockery\Mock|FolderRepository $repository */
        $repository = Mockery::mock(FolderRepository::class, [$eloquent])->makePartial();

        $this->callHiddenMethod($repository, 'orderBy');
    }

    /**
     * test totalCount method.
     *
     * @return void
     */
    public function testTotalCount()
    {
        // conditions
        $expected = 2;

        /** @var Mockery\Mock|Builder $builder */
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('count')->once()->with(['id'])->andReturn($expected);

        /** @var Mockery\Mock|FolderInterface $eloquent */
        $eloquent = Mockery::mock(FolderInterface::class);
        $eloquent->shouldReceive('newQuery')->once()->with()->andReturn($builder);

        $repository = new FolderRepository($eloquent);

        $this->assertSame($expected, $repository->totalCount());
    }

    /**
     * test create method.
     *
     * @return void
     */
    public function testCreate()
    {
        $inputs = [
            'user_id' => 'dummy user_id',
            'rack_id' => 'dummy rack_id',
            'name' => 'dummy name',
        ];
        $eloquentInputs = [
            'user_id' => 'dummy user_id',
            'rack_id' => 'dummy rack_id',
            'name' => 'dummy name',
        ];

        /** @var Mockery\Mock|FolderInterface $createdFolder */
        $createdFolder = Mockery::mock(FolderInterface::class);
        $createdFolder->shouldReceive('save')->once()->with()->andReturn(true);

        /** @var Mockery\Mock|FolderInterface $eloquent */
        $eloquent = Mockery::mock(FolderInterface::class);
        $eloquent->shouldReceive('newQuery')->once()->with();
        $eloquent->shouldReceive('newInstance')->once()->with($eloquentInputs)->andReturn($createdFolder);

        $repository = new FolderRepository($eloquent);

        $this->assertSame($createdFolder, $repository->create($inputs));
    }

    /**
     * test find method.
     *
     * @throws \App\Repositories\Exceptions\FolderNotFoundException
     *
     * @return void
     */
    public function testFind()
    {
        // conditions
        $folderId = 999;

        /** @var Mockery\Mock|FolderInterface */
        $foundFolder = Mockery::mock(FolderInterface::class);

        /** @var Mockery\Mock|Builder $builder */
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('find')->once()->with($folderId)->andReturn($foundFolder);

        /** @var Mockery\Mock|FolderInterface $folder */
        $folder = Mockery::mock(FolderInterface::class);
        $folder->shouldReceive('newQuery')->once()->with()->andReturn($builder);

        /** @var Mockery\Mock|FolderRepository $repository */
        $repository = Mockery::mock(FolderRepository::class, [$folder])->makePartial()->shouldAllowMockingProtectedMethods();

        $this->assertSame($foundFolder, $repository->find($folderId));
    }

    /**
     * test find method. not found case.
     *
     * @throws FolderNotFoundException
     *
     * @return void
     */
    public function testFindNotFoundCase()
    {
        // conditions
        $folderId = 2;
        $this->expectException(FolderNotFoundException::class);

        $foundFolder = null;

        /** @var Mockery\Mock|Builder $builder */
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('find')->once()->with($folderId)->andReturn($foundFolder);

        /** @var Mockery\Mock|FolderInterface $folder */
        $folder = Mockery::mock(FolderInterface::class);
        $folder->shouldReceive('newQuery')->once()->with()->andReturn($builder);

        /** @var Mockery\Mock|FolderRepository $repository */
        $repository = Mockery::mock(FolderRepository::class, [$folder])->makePartial()->shouldAllowMockingProtectedMethods();

        $this->assertSame($foundFolder, $repository->find($folderId));
    }

    /**
     * test update method.
     *
     * @throws FolderNotFoundException
     *
     * @return void
     */
    public function testUpdate()
    {
        // conditions
        $folderId = 999;
        $inputs = [
            'rack_id' => 'dummy rack_id',
            'name' => 'dummy name',
        ];
        $eloquentInputs = [
            'rack_id' => 'dummy rack_id',
            'name' => 'dummy name',
        ];

        /** @var Mockery\Mock|FolderInterface $updatedFolder */
        $updatedFolder = Mockery::mock(FolderInterface::class);
        $updatedFolder->shouldReceive('update')->once()->with($eloquentInputs)->andReturn($updatedFolder);

        /** @var Mockery\Mock|Builder $builder */
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('find')->once()->with($folderId)->andReturn($updatedFolder);

        /** @var Mockery\Mock|FolderInterface $folder */
        $folder = Mockery::mock(FolderInterface::class);
        $folder->shouldReceive('newQuery')->once()->with()->andReturn($builder);

        $repository = new FolderRepository($folder);

        $this->assertSame($updatedFolder, $repository->update($folderId, $inputs));
    }

    /**
     * test update method with not found case
     *
     * @throws FolderNotFoundException
     *
     * @return void
     */
    public function testUpdateNotFoundCase()
    {
        // conditions
        $folderId = 999;
        $this->expectException(FolderNotFoundException::class);
        $inputs = [
            'user_id' => 'dummy user_id',
            'rack_id' => 'dummy rack_id',
            'name' => 'dummy name',
        ];

        /** @var Mockery\Mock|FolderInterface $updatedFolder */
        $updatedFolder = Mockery::mock(FolderInterface::class);
        $updatedFolder->shouldReceive('update')->never();

        /** @var Mockery\Mock|Builder $builder */
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('find')->once()->with($folderId)->andReturn(null);

        /** @var Mockery\Mock|FolderInterface $folder */
        $folder = Mockery::mock(FolderInterface::class);
        $folder->shouldReceive('newQuery')->once()->with()->andReturn($builder);

        $repository = new FolderRepository($folder);

        $this->assertSame($updatedFolder, $repository->update($folderId, $inputs));
    }

    /**
     * test delete method.
     *
     * @throws FolderNotFoundException
     * @throws \Exception
     * @return void
     */
    public function testDelete()
    {
        // conditions
        $folderId = 999;
        $deleteResult = true;

        /** @var Mockery\Mock|FolderInterface $foundFolder */
        $foundFolder = Mockery::mock(FolderInterface::class);
        $foundFolder->shouldReceive('delete')->once()->with()->andReturn($deleteResult);

        /** @var Mockery\Mock|Builder $builder */
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('find')->once()->with($folderId)->andReturn($foundFolder);

        /** @var Mockery\Mock|FolderInterface $folder */
        $folder = Mockery::mock(FolderInterface::class);
        $folder->shouldReceive('newQuery')->once()->with()->andReturn($builder);

        $repository = new FolderRepository($folder);

        $this->assertTrue($repository->delete($folderId));
    }

    /**
     * test delete method, not found case.
     *
     * @throws FolderNotFoundException
     * @throws \Exception
     * @return void
     */
    public function testDeleteNotFoundCase()
    {
        // conditions
        $folderId = 2;
        $this->expectException(FolderNotFoundException::class);

        $foundFolder = null;

        /** @var Mockery\Mock|Builder $builder */
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('find')->once()->with($folderId)->andReturn($foundFolder);

        /** @var Mockery\Mock|FolderInterface $folder */
        $folder = Mockery::mock(FolderInterface::class);
        $folder->shouldReceive('newQuery')->once()->with()->andReturn($builder);

        $repository = new FolderRepository($folder);

        $repository->delete($folderId);
    }

    /**
     * test all method
     *
     * @return void
     */
    public function testAll()
    {
        /** @var Mockery\Mock|Collection $collection */
        $collection = Mockery::mock(Collection::class);

        /** @var Mockery\Mock|Builder $builder */
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('get')->once()->with()->andReturn($collection);

        /** @var Mockery\Mock|FolderInterface $eloquent */
        $eloquent = Mockery::mock(FolderInterface::class);
        $eloquent->shouldReceive('newQuery')->once()->with()->andReturn($builder);

        /** @var Mockery\Mock|FolderRepository $repository */
        $repository = Mockery::mock(FolderRepository::class, [$eloquent])->makePartial()->shouldAllowMockingProtectedMethods();
        $repository->shouldReceive('orderBy')->once()->with()->andReturn($repository);

        $this->assertSame($collection, $repository->all());
    }

//    /**
//     * test filter method.
//     *
//     * @return void
//     */
//    public function testFilter()
//    {
//        /** @var Mockery\Mock|\Illuminate\Database\Eloquent\Builder $query */
//        $query = Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
//
//        /** @var Mockery\Mock|FolderInterface $eloquent */
//        $eloquent = Mockery::mock(FolderInterface::class);
//        $eloquent->shouldReceive('newQuery')->once()->with()->andReturn($query);
//
//        /** @var Mockery\Mock|FilterInterface $filter */
//        $filter = Mockery::mock(FilterInterface::class);
//        $filter->shouldReceive('apply')->once()->with($query);
//
//        $repository = new FolderRepository($eloquent);
//
//        $this->assertSame($repository, $repository->filtering($filter));
//    }

    /**
     * test count method.
     *
     * @return void
     */
    public function testCount()
    {
        // conditions
        $expected = 2;

        /** @var Mockery\Mock|Builder $builder */
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('count')->once()->with(['id'])->andReturn($expected);

        /** @var Mockery\Mock|FolderInterface $eloquent */
        $eloquent = Mockery::mock(FolderInterface::class);
        $eloquent->shouldReceive('newQuery')->once()->with()->andReturn($builder);

        $repository = new FolderRepository($eloquent);

        $this->assertSame($expected, $repository->count());
    }
}

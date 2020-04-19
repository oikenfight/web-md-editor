<?php
declare(strict_types=1);

namespace Tests\Unit\app\Http\UseCases\Folder;

use App\Entities\Contracts\FolderInterface;
use App\Entities\Contracts\UserInterface;
use App\Http\UseCases\Contracts\Folder\StoreUseCaseInterface;
use App\Http\UseCases\Folder\StoreUseCase;
use App\Repositories\Contracts\FolderRepositoryInterface;
use Mockery;
use Tests\Unit\TestCase;


/**
 * Class StoreUseCaseTest
 *
 * @package Tests\Unit\app\Http\UseCases\Folder
 */
final class StoreUseCaseTest extends TestCase
{
    /**
     * test instance of
     *
     * @return void
     */
    public function testInstanceOf()
    {
        /** @var Mockery\Mock|FolderRepositoryInterface $repository */
        $repository = Mockery::mock(FolderRepositoryInterface::class);

        $useCase = new StoreUseCase($repository);

        $this->assertInstanceOf(StoreUseCaseInterface::class, $useCase);
    }

    /**
     * test __construct
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testConstruct()
    {
        /** @var Mockery\Mock|FolderRepositoryInterface $repository */
        $repository = Mockery::mock(FolderRepositoryInterface::class);

        $useCase = new StoreUseCase($repository);

        $repositoryRef = $this->getHiddenProperty($useCase, 'repository');

        $this->assertSame($repository, $repositoryRef->getValue($useCase));
    }

    /**
     * test __invoke method
     *
     * @return void
     */
    public function testInvoke()
    {
        /** @var Mockery\Mock|FolderInterface $user */
        $Folder = Mockery::mock(FolderInterface::class);
        $expected = $Folder;

        $userId = 100;
        $paramInput = [
            'name' => 'dummy folder name'
        ];
        $input = [
            'user_id' => $userId,
            'name' => 'dummy folder name'
        ];

        /** @var Mockery\Mock|UserInterface $user */
        $user = Mockery::mock(UserInterface::class);
        $user->id = $userId;

        /** @var Mockery\Mock|FolderRepositoryInterface $repository */
        $repository = Mockery::mock(FolderRepositoryInterface::class);
        $repository->shouldReceive('create')->once()->with($input)->andReturn($Folder);

        $useCase = new StoreUseCase($repository);

        $this->assertSame($expected, $useCase($user, $paramInput));
    }

    /**
     * test __invoke method with none input
     *
     * @return void
     */
    public function testInvokeWithFolderDataIsNone()
    {
        /** @var Mockery\Mock|FolderInterface $user */
        $Folder = Mockery::mock(FolderInterface::class);
        $expected = $Folder;

        $userId = 100;
        $folderData = [
            'user_id' => $userId,
            'name' => null,
        ];

        /** @var Mockery\Mock|UserInterface $user */
        $user = Mockery::mock(UserInterface::class);
        $user->id = $userId;

        /** @var Mockery\Mock|FolderRepositoryInterface $repository */
        $repository = Mockery::mock(FolderRepositoryInterface::class);
        $repository->shouldReceive('create')->once()->with($folderData)->andReturn($Folder);

        $useCase = new StoreUseCase($repository);

        $this->assertSame($expected, $useCase($user));
    }
}

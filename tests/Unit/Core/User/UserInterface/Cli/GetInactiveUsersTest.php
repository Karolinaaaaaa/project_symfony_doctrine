<?php

namespace App\Tests\Unit\Core\User\UserInterface\Cli;

use App\Core\User\Domain\Repository\UserRepositoryInterface;
use App\Core\User\Domain\User;
use App\Core\User\UserInterface\Cli\GetInactiveUsers;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class GetInactiveUsersTest extends TestCase
{
    private UserRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject $userRepository;

    private GetInactiveUsers $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->command = new GetInactiveUsers($this->userRepository);
    }

    public function test_execute_with_inactive_users(): void
    {
        $user1 = $this->createMock(User::class);
        $user1->method('getEmail')->willReturn('inactive1@example.com');

        $user2 = $this->createMock(User::class);
        $user2->method('getEmail')->willReturn('inactive2@example.com');

        $this->userRepository->expects(self::once())
            ->method('getInactiveUsers')
            ->willReturn([$user1, $user2]);

        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $this->command->run($input, $output);

        $result = $output->fetch();
        $this->assertStringContainsString('inactive1@example.com', $result);
        $this->assertStringContainsString('inactive2@example.com', $result);
    }

    public function test_execute_no_inactive_users(): void
    {
        $this->userRepository->expects(self::once())
            ->method('getInactiveUsers')
            ->willReturn([]);

        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $this->command->run($input, $output);

        $this->assertStringContainsString('Brak nieaktywnych użytkowników.', $output->fetch());
    }
}

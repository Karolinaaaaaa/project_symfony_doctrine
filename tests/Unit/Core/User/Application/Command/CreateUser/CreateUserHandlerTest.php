<?php

namespace App\Tests\Unit\Core\User\Application\Command\CreateUser;

use App\Core\User\Application\Command\CreateUser\CreateUserCommand;
use App\Core\User\Application\Command\CreateUser\CreateUserHandler;
use App\Core\User\Domain\Repository\UserRepositoryInterface;
use App\Core\User\Domain\User;
use App\Common\Mailer\MailerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateUserHandlerTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private MailerInterface|MockObject $mailer;
    private CreateUserHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->mailer = $this->createMock(MailerInterface::class);

        $this->handler = new CreateUserHandler(
            $this->userRepository,
            $this->mailer
        );
    }

    public function test_handle_success(): void
    {
        $command = new CreateUserCommand('test@example.com');

        $this->userRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(function (User $user) use ($command) {
                return $user->getEmail() === $command->email && !$user->isActive();
            }));

        $this->userRepository->expects(self::once())
            ->method('flush');

        $this->mailer->expects(self::once())
            ->method('send')
            ->with(
                'test@example.com',
                'Zarejestrowano konto w systemie',
                'Twoje konto zostało zarejestrowane w systemie. Aktywacja konta trwa do 24h.'
            );

        $this->handler->__invoke($command);
    }

    public function test_handle_user_persistence_failure(): void
    {
        $command = new CreateUserCommand('test@example.com');

        $this->userRepository->expects(self::once())
            ->method('save')
            ->willThrowException(new \RuntimeException('Database error'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Database error');

        $this->handler->__invoke($command);
    }

    public function test_handle_mailer_failure(): void
    {
        $command = new CreateUserCommand('test@example.com');

        $this->userRepository->expects(self::once())
            ->method('save');
        $this->userRepository->expects(self::once())
            ->method('flush');

        $this->mailer->expects(self::once())
            ->method('send')
            ->willThrowException(new \RuntimeException('Mailer error'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Mailer error');

        $this->handler->__invoke($command);
    }
}

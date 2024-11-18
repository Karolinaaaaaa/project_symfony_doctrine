<?php

namespace App\Core\User\Application\Command\CreateUser;

use App\Core\User\Domain\Repository\UserRepositoryInterface;
use App\Core\User\Domain\User;
use App\Common\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateUserHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly MailerInterface $mailer
    ) {}

    public function __invoke(CreateUserCommand $command): void
    {

        $user = new User($command->email);

        $this->userRepository->save($user);
        $this->userRepository->flush();

        $this->mailer->send(
            $user->getEmail(),
            'Zarejestrowano konto w systemie',
            'Twoje konto zostało zarejestrowane w systemie. Aktywacja konta trwa do 24h.'
        );
    }
}
